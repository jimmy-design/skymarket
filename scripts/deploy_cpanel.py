"""Incremental SFTP deployment for SkyMarket's existing cPanel installation.

Run by GitHub Actions. Never uploads .env, vendor, uploads or Windows paths.
Backs up changes, briefly enables Laravel maintenance, and rolls back failures.
"""
from __future__ import annotations

import datetime as dt
import errno
import io
import json
import os
from pathlib import Path, PurePosixPath
import re
import stat
import subprocess
import time
import urllib.request


def git(*args: str) -> bytes:
    return subprocess.check_output(['git', *args])


def destinations(name: str) -> list[str]:
    path = PurePosixPath(name)
    if path.is_absolute() or '..' in path.parts or '\\' in name:
        raise ValueError('Invalid repository path')
    if any(part.startswith('.') for part in path.parts):
        return []
    parts = path.parts
    if not parts:
        return []
    if parts[0] in {'app', 'config', 'database', 'lang', 'resources', 'routes', 'platform'}:
        targets = ['skymarket-app/' + name]
        if parts[0] == 'database':
            return []  # Migrations are checked separately; seed data stays on GitHub.
        if len(parts) >= 5 and parts[0] == 'platform' and parts[3] == 'public':
            group, module = parts[1:3]
            asset = '/'.join(parts[4:])
            if group == 'themes':
                targets.append('public_html/themes/' + module + '/' + asset)
            elif group in {'core', 'packages', 'plugins'}:
                targets.append('public_html/vendor/core/' + group + '/' + module + '/' + asset)
        return targets
    if parts[0] == 'public' and len(parts) > 1:
        if parts[1] in {'index.php', 'storage', 'vendor', 'themes', 'hot', 'info.php', 'web.config'} or parts[1].startswith('storage.backup-'):
            return []
        return ['public_html/' + '/'.join(parts[1:])]
    if name == 'artisan':
        return ['skymarket-app/artisan']
    return []


def changes_between(base: str, head: str) -> list[tuple[str, str]]:
    raw = git('diff', '--name-status', '--no-renames', '-z', base, head).decode('utf-8').split('\0')
    return [(raw[i], raw[i + 1]) for i in range(0, len(raw) - 1, 2)]


def build_plan(changes: list[tuple[str, str]]) -> dict[str, str | None]:
    plan = {}
    for status, name in changes:
        parts = PurePosixPath(name).parts
        if PurePosixPath(name).name in {'composer.json', 'composer.lock', 'package.json', 'package-lock.json', 'yarn.lock', 'bun.lockb'}:
            raise RuntimeError('Dependency change requires a prepared dependency/build deployment: ' + name)
        if 'migrations' in parts or name in {'bootstrap/app.php', 'public/index.php', 'public/.htaccess', '.htaccess'}:
            raise RuntimeError('This infrastructure/database change requires a reviewed deployment: ' + name)
        for target in destinations(name):
            plan[target] = None if status == 'D' else name
    return plan


def missing(error: OSError) -> bool:
    return error.errno == errno.ENOENT


class Deployment:
    def __init__(self, sftp, home: str):
        home_path = PurePosixPath(home)
        if not home_path.is_absolute() or '..' in home_path.parts:
            raise ValueError('CPANEL_HOME must be an absolute SFTP home directory')
        self.sftp = sftp
        self.home = home.rstrip('/')
        self.backup_root = ''
        self.journal: list[dict] = []

    def remote(self, relative: str) -> str:
        parts = PurePosixPath(relative).parts
        if not parts or parts[0] not in {'skymarket-app', 'public_html'} or '..' in parts or '\\' in relative:
            raise ValueError('Remote path is outside the deployment folders')
        return self.home + '/' + relative

    def read(self, relative: str) -> bytes | None:
        try:
            with self.sftp.open(self.remote(relative), 'rb') as handle:
                return handle.read()
        except OSError as error:
            if missing(error):
                return None
            raise

    def mkdirs(self, relative: str) -> None:
        current = self.home
        self.remote(relative)  # Validate before creating anything.
        for part in PurePosixPath(relative).parts:
            current += '/' + part
            try:
                info = self.sftp.lstat(current)
                if not stat.S_ISDIR(info.st_mode):
                    raise RuntimeError('Expected a real directory: ' + current)
            except OSError as error:
                if not missing(error):
                    raise
                self.sftp.mkdir(current, mode=0o755)

    def write(self, relative: str, content: bytes) -> None:
        self.mkdirs(str(PurePosixPath(relative).parent))
        target = self.remote(relative)
        temporary = target + '.github-upload'
        with self.sftp.open(temporary, 'wb') as handle:
            handle.write(content)
        self.sftp.chmod(temporary, 0o644)
        try:
            self.sftp.posix_rename(temporary, target)
        except OSError:
            self.remove(relative)
            self.sftp.rename(temporary, target)

    def remove(self, relative: str) -> None:
        try:
            self.sftp.remove(self.remote(relative))
        except OSError as error:
            if not missing(error):
                raise

    def backup(self, relative: str) -> None:
        previous = self.read(relative)
        backup = self.backup_root + '/files/' + relative
        if previous is not None:
            self.write(backup, previous)
        self.journal.append({'path': relative, 'existed': previous is not None, 'backup': backup})

    def rollback(self) -> None:
        for item in reversed(self.journal):
            if item['existed']:
                old = self.read(item['backup'])
                if old is None:
                    raise RuntimeError('Missing deployment backup: ' + item['path'])
                self.write(item['path'], old)
            else:
                self.remove(item['path'])


def check_health(url: str) -> None:
    if not url.startswith('https://'):
        raise ValueError('Health check requires HTTPS')
    for attempt in range(3):
        try:
            request = urllib.request.Request(url, headers={'User-Agent': 'SkyMarket-Deployment-Healthcheck', 'Cache-Control': 'no-cache'})
            with urllib.request.urlopen(request, timeout=25) as response:
                if response.status != 200:
                    raise RuntimeError('Website health check did not return HTTP 200')
                return
        except Exception:
            if attempt == 2:
                raise RuntimeError('Live website health check failed; rolling back') from None
            time.sleep(3)


def main() -> None:
    import paramiko

    required = ['CPANEL_SFTP_HOST', 'CPANEL_SFTP_USER', 'CPANEL_SFTP_PASSWORD', 'CPANEL_SSH_KNOWN_HOSTS', 'CPANEL_HOME']
    for name in required:
        if not os.environ.get(name):
            raise RuntimeError('Missing deployment configuration: ' + name)
    client = paramiko.SSHClient()
    for line in os.environ['CPANEL_SSH_KNOWN_HOSTS'].splitlines():
        if line.strip() and not line.startswith('#'):
            entry = paramiko.hostkeys.HostKeyEntry.from_line(line)
            for hostname in entry.hostnames:
                client.get_host_keys().add(hostname, entry.key.get_name(), entry.key)
    client.set_missing_host_key_policy(paramiko.RejectPolicy())
    client.connect(os.environ['CPANEL_SFTP_HOST'], port=int(os.environ.get('CPANEL_SFTP_PORT', '22')),
                   username=os.environ['CPANEL_SFTP_USER'], password=os.environ['CPANEL_SFTP_PASSWORD'],
                   allow_agent=False, look_for_keys=False, timeout=20, auth_timeout=20, banner_timeout=20)
    client.get_transport().set_keepalive(20)
    try:
        with client.open_sftp() as sftp:
            deploy = Deployment(sftp, os.environ['CPANEL_HOME'])
            for required_file in ['public_html/index.php', 'skymarket-app/vendor/autoload.php', 'skymarket-app/bootstrap/app.php']:
                sftp.stat(deploy.remote(required_file))
            state_path = 'skymarket-app/.github-deploy-state.json'
            previous_state = deploy.read(state_path)
            base = json.loads(previous_state)['commit'] if previous_state else os.environ.get('CPANEL_INITIAL_REF', '')
            if not re.fullmatch(r'[0-9a-f]{7,40}', base):
                raise RuntimeError('Set CPANEL_INITIAL_REF to the commit currently deployed on cPanel')
            head = git('rev-parse', 'HEAD').decode().strip()
            git('cat-file', '-e', base + '^{commit}')
            plan = build_plan(changes_between(base, head))
            print('Deploying commit', head, 'from', base, ':', len(plan), 'file changes', flush=True)
            if not plan:
                print('No website files changed. Existing deployment state retained.')
                return

            down = 'skymarket-app/storage/framework/down'
            if deploy.read(down) is not None:
                raise RuntimeError('The site is already in maintenance mode; deployment stopped')
            lock = 'skymarket-app/.github-deploy.lock'
            try:
                sftp.mkdir(deploy.remote(lock), mode=0o700)
            except OSError:
                raise RuntimeError('Another deployment holds the lock. Inspect .github-deploy.lock before retrying.') from None
            maintenance = False
            rollback_failed = False
            try:
                stamp = dt.datetime.now(dt.timezone.utc).strftime('%Y%m%dT%H%M%SZ')
                deploy.backup_root = 'skymarket-app/.deploy-backups/' + stamp + '-' + head[:10]
                for target in plan:
                    deploy.backup(target)
                for cache in ['config.php', 'routes-v7.php', 'routes.php']:
                    target = 'skymarket-app/bootstrap/cache/' + cache
                    if deploy.read(target) is not None:
                        plan[target] = None
                        deploy.backup(target)
                deploy.backup(state_path)
                deploy.write(deploy.backup_root + '/manifest.json', json.dumps({'base': base, 'commit': head, 'files': deploy.journal}, indent=2).encode())
                deploy.write(down, json.dumps({'status': 503, 'retry': 30, 'refresh': 5, 'redirect': None, 'template': None}).encode())
                maintenance = True
                for target, source in plan.items():
                    if source is None:
                        deploy.remove(target)
                    else:
                        deploy.write(target, Path(source).read_bytes())
                    print('Updated:', target, flush=True)
                deploy.remove(down)
                maintenance = False
                check_health(os.environ.get('DEPLOY_HEALTH_URL', 'https://skymarket.co.ke/'))
                deploy.write(state_path, json.dumps({'commit': head, 'deployed_at': stamp, 'backup': deploy.backup_root}, indent=2).encode())
                print('Deployment successful. Website returned HTTP 200. Backup:', deploy.backup_root)
            except Exception:
                if maintenance or deploy.journal:
                    print('Deployment failed; restoring previous files.', flush=True)
                    try:
                        deploy.write(down, b'{"status":503,"retry":30,"refresh":5,"redirect":null,"template":null}')
                        maintenance = True
                        deploy.rollback()
                    except Exception:
                        rollback_failed = True
                        raise RuntimeError('Rollback needs attention. Site remains in maintenance; use the private backup manifest.') from None
                raise
            finally:
                if maintenance and not rollback_failed:
                    deploy.remove(down)
                if not rollback_failed:
                    sftp.rmdir(deploy.remote(lock))
    finally:
        client.close()


if __name__ == '__main__':
    main()
