import errno
import io
import stat
import unittest

from scripts.deploy_cpanel import Deployment, build_plan, destinations


class RemoteFile(io.BytesIO):
    def __init__(self, storage, path, writable):
        super().__init__(b'' if writable else storage[path])
        self.storage, self.path, self.writable_file = storage, path, writable

    def close(self):
        if self.writable_file:
            self.storage[self.path] = self.getvalue()
        super().close()


class FakeSFTP:
    def __init__(self):
        self.files = {}
        self.dirs = {'/home/site', '/home/site/skymarket-app', '/home/site/public_html'}
        self.links = set()

    def open(self, path, mode):
        if 'w' not in mode and path not in self.files:
            raise FileNotFoundError(errno.ENOENT, 'not found')
        return RemoteFile(self.files, path, 'w' in mode)

    def lstat(self, path):
        if path in self.links:
            mode = stat.S_IFLNK
        elif path in self.dirs:
            mode = stat.S_IFDIR
        else:
            raise FileNotFoundError(errno.ENOENT, 'not found')
        return type('Info', (), {'st_mode': mode})()

    def mkdir(self, path, mode=0o755):
        self.dirs.add(path)

    def chmod(self, path, mode):
        pass

    def posix_rename(self, source, destination):
        self.files[destination] = self.files.pop(source)

    def remove(self, path):
        if path not in self.files:
            raise FileNotFoundError(errno.ENOENT, 'not found')
        del self.files[path]


class DeploymentTests(unittest.TestCase):
    def test_production_configuration_uploads_and_entrypoints_are_excluded(self):
        for path in ['.env', '.env.production', 'storage/app/public/photo.jpg', 'vendor/autoload.php',
                     'bootstrap/app.php', 'bootstrap/cache/config.php', 'public/index.php',
                     'public/storage/photo.jpg', 'public/info.php', 'tools/private.py', '.git/config']:
            with self.subTest(path=path):
                self.assertEqual([], destinations(path))

    def test_compiled_assets_reach_the_live_published_directories(self):
        self.assertEqual(['skymarket-app/platform/themes/farmart/public/js/main.js',
                          'public_html/themes/farmart/js/main.js'], destinations('platform/themes/farmart/public/js/main.js'))
        self.assertEqual(['skymarket-app/platform/plugins/ecommerce/public/css/style.css',
                          'public_html/vendor/core/plugins/ecommerce/css/style.css'],
                         destinations('platform/plugins/ecommerce/public/css/style.css'))
        self.assertEqual(['public_html/js/skymarket-tiktok.js'], destinations('public/js/skymarket-tiktok.js'))

    def test_traversal_and_absolute_repository_paths_are_rejected(self):
        for name in ['../.env', '/home/site/.env', 'public/../../.env', 'public\\..\\.env']:
            with self.assertRaises(ValueError):
                destinations(name)

    def test_dependencies_migrations_and_entrypoint_changes_require_preparation(self):
        for name in ['composer.lock', 'platform/plugins/test/composer.json', 'package.json',
                     'database/migrations/2026_new_table.php', 'public/index.php', 'bootstrap/app.php', 'public/.htaccess']:
            with self.subTest(name=name), self.assertRaises(RuntimeError):
                build_plan([('M', name)])

    def test_deletions_only_target_managed_source_files(self):
        self.assertEqual({'skymarket-app/app/Old.php': None}, build_plan([
            ('D', 'app/Old.php'), ('D', 'public/storage/photo.jpg'), ('D', '.env'), ('M', 'docs/readme.md')]))

    def test_backup_and_rollback_restore_old_files_and_remove_new_files(self):
        sftp = FakeSFTP()
        sftp.files['/home/site/skymarket-app/app/Controller.php'] = b'old'
        sftp.files['/home/site/skymarket-app/.env'] = b'private-value'
        deploy = Deployment(sftp, '/home/site')
        deploy.backup_root = 'skymarket-app/.deploy-backups/test'
        deploy.backup('skymarket-app/app/Controller.php')
        deploy.backup('public_html/js/new.js')
        deploy.write('skymarket-app/app/Controller.php', b'new')
        deploy.write('public_html/js/new.js', b'new JS')
        deploy.rollback()
        self.assertEqual(b'old', deploy.read('skymarket-app/app/Controller.php'))
        self.assertIsNone(deploy.read('public_html/js/new.js'))
        self.assertEqual(b'private-value', deploy.read('skymarket-app/.env'))

    def test_remote_paths_cannot_escape_the_two_application_directories(self):
        deploy = Deployment(FakeSFTP(), '/home/site')
        for name in ['../../etc/passwd', '/etc/passwd', 'other/config', 'public_html/../.env']:
            with self.assertRaises(ValueError):
                deploy.remote(name)

    def test_symlinked_directories_are_not_followed_during_uploads(self):
        sftp = FakeSFTP()
        sftp.links.add('/home/site/public_html/js')
        deploy = Deployment(sftp, '/home/site')
        with self.assertRaises(RuntimeError):
            deploy.write('public_html/js/test.js', b'code')


if __name__ == '__main__':
    unittest.main()
