# Automatic GitHub → cPanel deployment

Push to `main` and GitHub Actions deploys the website changes over SFTP.
No cPanel Terminal, SSH shell, manual ZIP upload or browser command runner
is required. The workflow also supports **Actions → Deploy SkyMarket to
cPanel → Run workflow**. Files remain under the existing sibling folders
`skymarket-app/` and `public_html/`.

## Connection settings

Repository Settings → Secrets and variables → Actions → Secrets:

| Secret | Value |
| --- | --- |
| `CPANEL_SFTP_HOST` | SFTP hostname or IP |
| `CPANEL_SFTP_PORT` | Hosting SFTP port |
| `CPANEL_SFTP_USER` | SFTP account username |
| `CPANEL_SFTP_PASSWORD` | SFTP password |
| `CPANEL_SSH_KNOWN_HOSTS` | Verified OpenSSH known_hosts entry for that host and port |

Repository Actions variables:

| Variable | Value |
| --- | --- |
| `CPANEL_HOME` | SFTP account home, currently `/home/rqitrand` |
| `CPANEL_INITIAL_REF` | Commit already on cPanel before the first run, initially `8a5001a` |
| `CPANEL_DEPLOY_ENABLED` | `true` enables automatic deployment; any other value disables it |

Credentials are GitHub encrypted secrets and never enter committed source
files. If you rotate the hosting password, update `CPANEL_SFTP_PASSWORD`.
Host keys are checked; a changed key fails deployment until verified and
updated. Restrict repository write access because main deploys to production.

## What happens on a push

1. Run deployment unit tests, the TikTok browser tests and PHP syntax checks.
2. Read the last successful commit from the private file
   `skymarket-app/.github-deploy-state.json`; use the initial ref only on
   the first run. Git's diff includes changes from failed or skipped pushes.
3. Back up every changed/deleted file under
   `skymarket-app/.deploy-backups/<timestamp>-<commit>/`, with a recovery
   manifest. Acquire a deployment lock to prevent simultaneous writes.
4. Enable Laravel maintenance while changed source files are uploaded and
   removed source files are deleted. Upload compiled theme/core/plugin
   assets to both their source and published public directories.
5. Remove stale config/route caches, end maintenance and request
   `https://skymarket.co.ke/`. Save the deployed commit only after HTTP 200.
   Restore the previous files if upload or health checks fail.

The existing `.env`, application key, database, vendor dependencies,
customer uploads and the hosting-specific `bootstrap/app.php` and
`public_html/index.php` are preserved. Deployment does not import SQL,
seed databases, activate plugins or execute migrations.

## Working locally

Edit, test, commit and push to `main`. Watch the Actions run for the result.
Commit compiled files in `platform/*/*/public/` when changing frontend
asset sources; build those assets locally before pushing. PHP and Blade
changes deploy directly. Docs/test-only changes need no website upload.

Dependency manifests, migrations, entrypoints or `.htaccess` changes stop
deployment before any website files are changed. These need a separate
prepared deployment because SFTP cannot install Composer packages, build
assets or run database migrations on the host. Do not bypass that check.
After a prepared manual deployment, update the private state commit to the
commit actually installed before resuming automatic updates.

The health check tests the homepage, not checkout or payment processing.
There is a brief maintenance period for code updates. Backups are private
and retained for recovery; review disk use and remove older backups through
File Manager after confirming newer releases. The rollback covers deployed
files, not database activity.

## Recovery

If a run is forcibly cancelled, open cPanel File Manager and inspect the
latest `.deploy-backups/.../manifest.json`. Restore each listed previously
existing file from its `backup` path to its `path`, and remove newly added
files listed with `existed: false`. All paths are relative to the account
home. After restoration, remove `skymarket-app/storage/framework/down` and
the empty directory `skymarket-app/.github-deploy.lock`, then retry.
Do not remove a lock while a deployment is still running.

References:
- https://docs.github.com/en/actions/get-started/continuous-deployment
- https://docs.github.com/en/actions/how-tos/write-workflows/choose-what-workflows-do/use-secrets
