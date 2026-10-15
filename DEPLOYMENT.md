# MULTI-POS deployment and recovery

The local implementation is ready for review. Nothing has been committed, pushed, migrated, or deployed. The existing remote is `https://github.com/Realnexx/MULTI-POS.git` on `main`.

The user-provided production domain is `tindaflow.wuaze.com`. Its FTP hostname, document-root path, supported protocol/port, and PHP version remain unverified. The domain itself must not be assumed to be the FTP hostname. For an application served directly at this domain's root, configure `base_path` as `/` after confirming the hosting layout.

## What changed

- `.github/workflows/deploy.yml` validates on pushes to `main` and manual runs, saves a runtime artifact, and uploads only when `DEPLOY_ENABLED` is `true`. Deployment uses the `production` GitHub environment and serializes uploads without cancelling an active transfer.
- `tools/prepare-deploy.py` stages only the PHP runtime and browser assets into ignored `.deploy/`, checking PHP syntax without executing application code. No Node or Composer build is needed for the current runtime.
- `config/app.php` loads ignored `config/config.local.php`. `config/config.example.php` is the sanitized template. A local configuration was created from the previous XAMPP settings, retaining `/pos/`; it is not in the artifact or Git.
- `config/db.php` now connects to an existing database and retains `$conn`, the database constants, and existing shared helpers. It no longer creates databases, changes schema, or seeds data. `url()` uses the configured base path.
- `tools/migrate-legacy.php` preserves the previous bootstrap schema changes as an explicit manual CLI operation. It corrects the previous missing column name in the dynamic ADD COLUMN query and omits automatic default PIN assignment.
- `index.php` refuses the initialization query parameter. `config/init_db.php` is now CLI-only, explicitly requires demo seeding, refuses production, and no longer drops the branches/ingredients tables. It is excluded from deployment.
- Root and assets `.htaccess` rules restrict private files, local PHP tools, directory listing, and executable upload types. Upload validation checks raster image content and matching extension, restricts filenames, and limits images to 5 MB. Existing upload destinations remain unchanged. Writable directory creation uses 0755, with product directories created when needed.
- Existing edits in `index.php`, `config/db.php`, `controllers/AdminController.php`, `controllers/InventoryController.php`, and `ajax/branch_action.php` were preserved around these targeted changes. Other existing application edits were not reverted.

## Required setup before enabling uploads

1. Confirm the live domain, FTP hostname, FTP login, supported protocol/port, and exact FTP document root in the InfinityFree account. `/htdocs/` is the expected example, not a confirmed path. An addon domain may differ. Do not use `/public_html/` by assumption.
2. Back up existing code, uploaded media, private configuration, and the full database. Inspect the server for old publicly accessible utility scripts; this deployment does not automatically clean pre-existing files. Remove obsolete public tools deliberately after backup.
3. Verify the host's PHP version and `mysqli`, `mbstring`, sessions, and image support. CI currently checks PHP 8.2; align its version with the confirmed production version before enabling deployment.
4. Create/select the hosting database using the control panel. Use its assigned hostname, database name, username and password. The application no longer creates a database for you.
5. Copy `config/config.example.php` to **server-only** `config/config.local.php`, set `environment` to `production`, supply the real database settings, and set `base_path` to `/` or the actual URL subdirectory. Install it before serving application traffic. `/htdocs/` is an FTP path, never the browser base path. Keep the private file out of Git and do not put its values in workflow variables.
6. Prepare the schema separately as described below. Replace all predictable demo passwords and PINs through authorized management before live use. Do not assign one shared PIN to existing users.
7. Create server directories `assets/uploads/avatars/`, `assets/img/products/`, `assets/img/users/`, and `assets/img/branches/`. Ensure the hosting PHP process can write there using appropriate ownership and permissions, starting with 0755. Do not default to 0777.
8. Upload any initial product images, avatars, branch/user images and store logos separately after reviewing them. Ordinary deployment intentionally excludes them; database records may refer to these paths. `assets/img/default.png` and other static assets are included.
9. Verify Apache accepts the supplied rewrite, Options and authorization directives. Local Apache supports rewrite and overrides; hosting support remains unverified. Test on a staging domain first and investigate HTTP 500 errors before live rollout.

## GitHub settings

Under Repository → Settings → Secrets and variables → Actions → **Secrets**, add repository secrets:

- `FTP_SERVER`: actual FTP hostname, not an assumed website domain.
- `FTP_USERNAME`: FTP account login.
- `FTP_PASSWORD`: FTP password, distinct from MySQL credentials.

Under the same page's **Variables** tab add:

- `FTP_SERVER_DIR`: confirmed document root, for example `/htdocs/`, ending in `/`.
- `FTP_PROTOCOL`: confirmed `ftp`, `ftps`, or `ftps-legacy`. Prefer FTPS when supported; do not disable certificate verification to work around configuration errors. Plain FTP is unencrypted.
- `FTP_PORT`: confirmed numeric port, commonly `21` but account verification is required.
- `DEPLOY_ENABLED`: leave unset or `false` until all production prerequisites are complete. Set `true` to enable future uploads on pushes/manual runs.

Create/review the `production` environment under repository Settings → Environments. Optional required reviewers can gate its deployment job. Workflow credentials come exclusively from Secrets.

The workflow uses FTP Deploy Action v4.4.0, verified against its [official action inputs](https://github.com/SamKirkland/FTP-Deploy-Action/blob/v4.4.0/action.yml). Checkout, PHP setup and artifact actions use their official supported inputs. Validate and deploy are separate jobs; a validation failure prevents transfer. Missing hosting settings also prevent transfer.

## Schema preparation and migration

**Do not run migrations as a routine deployment or against a database without a verified backup.** No schema/data operation has been run as part of this implementation.

For an existing database, review `tools/migrate-legacy.php` against a restored test copy first. It carries the former bootstrap's table/column changes and Manager-role insertion. DDL is not generally transactional and enum modifications can affect incompatible data; review differences before execution. It does not cover every historical root utility or guarantee a complete schema for a brand-new installation.

If reviewed and appropriate, run manually in a trusted CLI environment against the intended database:

```powershell
C:\xampp\php\php.exe tools/migrate-legacy.php --apply
```

This command is documentation, not an instruction for GitHub Actions. InfinityFree may not provide a shell. In that case, derive and review the required SQL against a restored database locally and apply only the approved schema statements through the host's database administration tool. Never publish a web migration endpoint or upload private credentials/tools to accomplish this.

For a brand-new installation, prepare a reviewed schema from a known working test database, excluding records, credentials, sales, sample users and stock. Compare missing fields against the models and historical utilities. `config/init_db.php` is a local demo initializer, **not** a production provisioning script. Existing root repair/update scripts are retained locally for reference, excluded from the artifact, and must not be bulk-executed.

After schema preparation, ordinary requests only connect; they do not migrate or seed. Sales and other legitimate application operations still write business data normally.

## Artifact and server-file preservation

Runtime scope: `index.php`, root protection rules, `config/{db,app,uploads}.php`, application PHP under `ajax/`, `controllers/`, `middleware/`, `models/`, `views/`, and supported CSS/JS/image/font/video assets, including bundled admin browser libraries. Asset sources, manifests, documentation, SQL and unrelated templates are omitted.

Excluded from transfer: `.git`, `.github`, editor metadata, environment/private config files, local docs/prompts, `tools/`, `scratch/`, `auroratabs/`, root repair/migration/debug/test/patch/refactoring/preview scripts, schema dumps, logs, backups, initialization code, runtime upload directories, and `assets/img/logo_*` store logos. The code does not reference `auroratabs` as a runtime dependency based on the inspected paths.

Git exclusions are separate from FTP exclusions. `.gitignore` also excludes local runtime uploads, backups, dependencies and staging output. Already tracked files remain tracked: review with `git ls-files assets/uploads assets/img` and selectively untrack server-owned media if desired, keeping local files. Never run a broad index-removal command without reviewing the list.

FTP Deploy Action tracks managed files and can delete previously managed files removed from the artifact even with `dangerous-clean-slate: false`. Explicit private-configuration and media exclusion patterns protect those paths from normal synchronization. Its [documented exclusions cover publish/delete operations](https://github.com/SamKirkland/FTP-Deploy-Action#settings). Keep those exclusions when changing staging scope.

Before adopting this workflow on a server with an earlier deployment, back up the existing `.ftp-deploy-sync-state.json`, inspect its managed paths privately, and confirm excluded paths match all server-owned content. Do not delete/reset the state file casually or change its name to force a full upload. Never enable dangerous clean slate. Root rules deny public access to dotfiles including state metadata; verify this on the host.

## Security findings and limits

The inspected working-tree configuration contained XAMPP development database settings, not verified production credentials. Two reachable Git commits were inspected for credential-like strings. Known demo credentials exist in initialization code/history; no real production credential was verified from the inspected files. This is a bounded static scan, not proof that every file or external remote history is free of secrets. If any development values were reused on a live account, rotate them; remove/disable demo users and predictable PINs on the live database. Ignoring a file does not remove it from history.

The separate, untracked `auroratabs/app/auth/helpers/jwt_utils.php` contains a hardcoded JWT signing key. If that application has been used publicly with that key, rotate it and invalidate existing tokens before publishing that application. Its registration test scripts and public contact/newsletter form examples also contain credential-like literals requiring review before committing that separate tree. None of `auroratabs/` is part of this deployment artifact. No secret values were printed. Existing tracked media includes branch/user images, store logos and an avatar; adding ignore patterns does not remove those tracked copies.

Deployment-related exposure fixes cover displayed production errors, public initialization, private file access, and executable image uploads. Existing `ajax/branch_action.php` lacks explicit authentication, permission and CSRF enforcement in the inspected handler; treat this as a production blocker requiring application-level review before enabling deployment. `AdminController` also contains a PIN update assembled into SQL; review and parameterize it separately before public rollout. These pre-existing authorization/query concerns have not been represented as fixed by the deployment work.

Upload validation now rejects non-images, mismatched extensions, files above 5 MB, nested upload arrays, and names containing spaces or extra punctuation/dots. Rename images to simple names such as `product_photo.png`. SVG uploads are not accepted; static SVG assets remain supported. Handler-generated paths remain compatible with current database references.

## Local checks and live verification

Run from the project root:

```powershell
$env:PHP_BINARY = 'C:\xampp\php\php.exe'
python -m unittest discover -s tools/tests -v
python tools/prepare-deploy.py C:\xampp\php\php.exe
```

These checks do not execute `config/db.php`, connect to MySQL, run migrations, or create sales. They verify artifact boundaries, absence of schema/seed statements from bootstrap, CLI-only initialization, missing/invalid config failure handling, and local/production configuration base paths and display settings. The builder syntax-checks all staged PHP. Local private settings retain `/pos/`.

Implementation verification completed: all four automated tests passed; 433 runtime files were staged with PHP syntax validation using local PHP 7.4.33; the workflow parsed successfully with PyYAML and its triggers, permissions, dependency, enable gate, credential references and exclusions were checked. PHP 8.2 validation will run in GitHub Actions; it was not executed locally. Additional manual PHP lint checks passed for the excluded initializer/migration tools. Apache configuration syntax passed, and six local HTTP checks returned 403 for private config, Git metadata, initialization, migration tools, an executable upload path and an asset directory index. Full-tree `git diff --check` reports whitespace in pre-existing edited files; unrelated formatting was left intact. The temporary YAML validation dependency was removed after use.

Before enabling uploads, use a restored test database and staging host to verify storefront links, login/logout, roles/branch restrictions, POS/cart, receipts/printing, inventory, expenses, reports, settings, media uploads, and AJAX requests at both `/pos/` and `/`. Functional database testing and host-specific access rules are still pending. Do not create test sales or change stock on production without authorization.

After the first authorized push, open Repository → Actions → Deploy MULTI-POS to InfinityFree. Confirm validation and deployment jobs succeeded. A skipped deploy means the enable flag is absent/false. Inspect the runtime artifact privately if needed; it contains no server credentials. Test the live application separately after a successful transfer.

Public requests to `config/config.local.php`, `.git/config`, `.ftp-deploy-sync-state.json`, local setup scripts, and uploaded executable files must return 403/404; directory indexes must be unavailable. Confirm production errors reveal no paths or credentials. Verify existing media/configuration remain unchanged. A green transfer job alone does not establish application correctness.

## First push and normal development

No repository initialization or remote change is needed. There are many unrelated pre-existing edits, so review carefully:

```powershell
git status --short
git diff
git add .gitignore .htaccess .github/workflows/deploy.yml assets/.htaccess config/app.php config/config.example.php config/uploads.php tools/prepare-deploy.py tools/migrate-legacy.php tools/tests/test_deployment.py DEPLOYMENT.md
git add -p -- config/db.php config/init_db.php index.php controllers/AdminController.php controllers/InventoryController.php ajax/branch_action.php
git diff --cached --check
git diff --cached
git status --short
# Only after confirming every staged change and required runtime file:
git commit -m "Prepare MULTI-POS deployment to InfinityFree"
git push origin main
```

The commands are for you to review and execute. Partial staging of already edited files needs care: stage coherent versions that include required existing application changes. Check `git ls-files --others --exclude-standard`; required untracked runtime files such as bundled admin assets, new components, AJAX endpoints and `models/StockNotification.php` must be reviewed and added explicitly if referenced by the committed application. Do not commit uploads or private configuration. Test the exact staged/committed snapshot before pushing; the local artifact currently reflects the full working tree, not a selective commit.

For future updates: pull before starting, make changes, review, stage specific paths, inspect the staged diff, commit, and `git push origin main`. If local changes prevent a pull, resolve them deliberately rather than discarding them.

On another computer:

```powershell
git clone https://github.com/Realnexx/MULTI-POS.git
cd MULTI-POS
```

Clone into that computer's XAMPP document root as appropriate. Create its own ignored `config/config.local.php` from the example, select `local`, set the actual local URL path (for a folder named `MULTI-POS`, use `/MULTI-POS/`), configure a separate local database, and prepare its schema safely. Transfer permitted local sample media separately if needed. Never share live credentials or production data through Git.

## Rollback

```powershell
git log --oneline
git revert <bad-commit-hash>
# Review the revert and checks, then:
git push origin main
```

The revert triggers a new validation/deployment when enabled. Use a deliberate revert for merge commits with the correct mainline rather than guessing. Code rollback does not restore database schema, transactions or uploaded media. Recover those only through a separately reviewed backup/recovery procedure. FTP synchronization is not atomic; schedule changes with appropriate operational care and verify completion before treating the site as healthy.
