# MULTI-POS / Stacey Halu Halu — GitHub Actions deployment prompt

You are a senior PHP developer, DevOps engineer, GitHub Actions specialist, and shared-hosting deployment expert.

Prepare my existing **MULTI-POS / Stacey Halu Halu** application for safe automatic deployment from GitHub to **InfinityFree**, preserving its current functionality and production data.

## Objective

```text
Local XAMPP development (C:\xampp\htdocs\pos)
→ GitHub: https://github.com/Realnexx/MULTI-POS
→ Push to main
→ GitHub Actions validation
→ Incremental FTP deployment
→ Confirmed InfinityFree document root
→ Live POS system and public storefront
```

InfinityFree is the intended destination for this task; its account, live domain, FTP path, PHP version, and database details still need verification. Do not claim these have already been configured.

After initial setup, normal updates should require only reviewing changes, committing, and pushing to `main`.

## Known project facts — verify before making changes

This prompt was adapted from an inspection of the local working tree. Recheck these facts because files may have changed.

- Git is already initialized, the current branch is `main`, and `origin` points to `https://github.com/Realnexx/MULTI-POS.git`.
- There are many existing modified and untracked files. Preserve all unrelated work. Do not reset, clean, stash, overwrite, or commit it automatically.
- The application is custom PHP using MySQLi, sessions, controllers, models, PHP views, and AJAX endpoints. Do not assume Laravel, WordPress, or a Node application.
- `index.php` is the main entry point. Routing uses `?route=...` and loads `config/db.php`.
- Application folders include `ajax/`, `assets/`, `config/`, `controllers/`, `middleware/`, `models/`, and `views/`.
- Main controllers include `AuthController`, `AdminController`, `PosController`, `ExpenseController`, `InventoryController`, and `WebsiteController`.
- Features include a public storefront and cart, authentication, roles and permissions, branch management, cashier POS, sales history, receipts, inventory, categories, expenses, reports, settings, CMS content, and activity logs.
- Runtime assets include `assets/admin/`, `assets/css/`, `assets/js/`, `assets/img/`, `assets/uploads/`, and `assets/video/`. Bundled browser libraries in `assets/admin/libs/` may be required in production.
- `config/db.php` currently combines connection configuration, automatic database/schema/data changes, and shared helper functions.
- The `url()` helper in `config/db.php` currently uses `/pos/` as its base path.
- `index.php` currently enables displayed PHP errors and exposes an `?init=1` route that includes `config/init_db.php`.
- `config/init_db.php` contains initialization and seed logic, including predictable example accounts. Do not publish or execute this as a public production setup endpoint.
- Upload destinations include `assets/img/products/` and `assets/uploads/avatars/`. Inspect all upload handlers for additional destinations, including branch images, store logos, and CMS media.
- The root contains numerous database repair, migration, test, patch, refactoring, schema inspection, and preview files.
- `auroratabs/` contains a separate-looking application/template tree, SQL files, and package manifests. Its presence does not establish that the POS needs it. Trace references before deciding deployment scope.
- No root `.gitignore`, `.github/` workflow directory, or `.htaccess` was found during the initial inspection. Check again before creating files.
- No root Composer or Node package manifest was found. Package manifests inside bundled assets or `auroratabs/` do not by themselves require a root build step.

Do not rename the application or restructure it to fit a generic example.

## 1. Inspect and define deployment scope

Read applicable `AGENTS.md` instructions. Inspect Git status, tracked files, relevant history, routing, includes, asset references, database setup, upload handlers, and existing server rules without printing secrets.

Identify the exact files required for production, runtime-created files, local tools, and unrelated projects. Inspect `prompt.md`, `z-program.php`, `z-program-prompt.md`, `scratch/`, `stitch_*.html`, and preview files rather than assuming they belong on the live server.

Prefer assembling a dedicated deployment staging directory containing only reviewed runtime files. Deploy that directory rather than uploading the entire repository with a small generic exclusion list. Keep the staging directory out of Git.

Do not run application PHP files during inspection: loading the current database bootstrap can mutate a database. Use static inspection and PHP syntax checks first.

## 2. Prepare Git safely

Create or improve `.gitignore` based on this application's actual needs. Cover:

- Environment files, while allowing sanitized examples.
- The chosen local/production configuration file containing credentials.
- Editor metadata, OS files, logs, caches, temporary files, and generated deployment output.
- Backup files such as `views/layouts/admin.php.bak`.
- Actual dependency directories only where a separate installation/build strategy makes exclusion appropriate.
- Runtime uploads after distinguishing them from required versioned starter images.

Do not broadly ignore `assets/`, bundled libraries, all images, all PHP files, or all configuration files. Keep `config/db.php` deployable after extracting secrets because the application requires it.

Explain that `.gitignore` does not untrack existing files or remove secrets from history. Review any proposed removal from Git's index individually and preserve local copies.

## 3. Separate secrets from application bootstrap

Inspect current files and Git history for database credentials, FTP credentials, API keys, SMTP passwords, tokens, and private configuration. Report paths and credential categories without displaying values. Distinguish local development defaults and sample credentials from verified production secrets.

Use a small server-specific PHP configuration file, such as `config/config.local.php`, with a committed sanitized example if appropriate. Adapt the naming and loading convention to the existing code.

- Preserve the existing `$conn` MySQLi interface and shared helpers used throughout controllers, models, views, and AJAX endpoints.
- Preserve compatibility with `DB_HOST`, `DB_USER`, `DB_PASS`, and `DB_NAME`, or update their consumers consistently.
- Keep production credentials out of Git, deployment artifacts, workflow YAML, and logs.
- Ignore the private file in Git and exclude/protect it during FTP synchronization.
- Fail safely when required production configuration is missing; do not silently use development credentials.
- Keep configuration errors generic in production and log diagnostic details privately.
- Do not add dotenv or Composer solely for configuration if unnecessary.

Document how the production configuration will be installed before the first code deployment. Use the hosting account's actual MySQL hostname and assigned database name; do not assume localhost or permission to create databases.

If real secrets were committed, identify what needs rotation. Do not rewrite shared Git history automatically.

## 4. Remove database side effects from normal requests

This is a required deployment prerequisite for this project.

`config/db.php` currently attempts database creation, schema changes, and data updates during normal application bootstrap. Merely omitting SQL imports from GitHub Actions does not make deployment database-safe.

- Separate connection/helper loading from database setup and migrations.
- Move necessary existing schema changes into a separately reviewed migration procedure, preserving their intended behavior.
- Do not execute `CREATE DATABASE`, `CREATE TABLE`, `ALTER TABLE`, seed inserts, default PIN assignment, or repair operations on normal page/AJAX requests in production.
- Do not drop, truncate, reset, recreate, or overwrite production records.
- Do not run `config/init_db.php`, root migration scripts, or SQL dumps in the deployment workflow.
- Disable the public `?init=1` route in production and prevent direct HTTP access to initialization/migration utilities.
- Do not remove helper functions that the runtime still needs.
- Explain any initial schema preparation required, with a backup and a controlled manual process. Do not import development data into an existing live database.

Inspect root utilities including `add_soft_delete.php`, `db_repair*.php`, `fix_db.php`, `ensure_logs.php`, `migrate*.php`, `migration_*.php`, `populate_new_fields.php`, `sync_cat_id.php`, and `update*_db.php`. Keep development tools locally if useful, but omit them from public deployment unless a reviewed runtime dependency requires them.

## 5. Fix deployment path and environment behavior

Keep local development at `/pos/` working. Make the application's base URL/path configurable so production can use `/` or its confirmed subdirectory.

Verify redirects, forms, AJAX calls, image helpers, receipts, printed reports, navigation, and authentication at both base paths. Search beyond `url()` for hardcoded `/pos/` references.

Distinguish the FTP filesystem destination from the browser URL base path. A deployment to `/htdocs/` does not mean links should start with `/htdocs/`.

Disable displayed startup/runtime errors in production through an environment-aware setting. Retain useful local debugging and private production logging.

## 6. Preserve uploaded files and server-only data

Audit every upload destination and classify static assets versus user-managed content. Protect existing product images, avatars, branch images, logos, CMS media, and private configuration from overwrite and deletion.

- Exclude server-owned upload content from ordinary deployments.
- Keep required static fallback images and browser assets deployable.
- Provide a separate, deliberate initial upload process for needed local media.
- Ensure required directories exist with appropriate writable permissions; do not recommend world-writable permissions by default.
- Verify the FTP action's actual exclusion and deletion semantics. `dangerous-clean-slate: false` alone does not guarantee preservation of previously managed files removed from the deployment source.
- Explain any transition from an earlier deployment state file before changing upload ownership or exclusions.

## 7. Create GitHub Actions deployment

Create `.github/workflows/deploy.yml` with:

- A descriptive name: `Deploy MULTI-POS to InfinityFree`.
- Push trigger for `main` and manual `workflow_dispatch`.
- Minimal repository permissions, normally `contents: read`.
- Deployment concurrency that prevents overlapping FTP synchronizations; do not interrupt an in-progress upload unnecessarily.
- Repository checkout, appropriate PHP syntax validation, runtime staging, and FTP deployment in that order.
- A currently verified maintained FTP action, such as `SamKirkland/FTP-Deploy-Action`. Verify official documentation and supported inputs/versions before choosing a version; do not copy an old sample blindly.
- Credentials exclusively from repository secrets `FTP_SERVER`, `FTP_USERNAME`, and `FTP_PASSWORD`.
- Protocol and port verified against the account's supported connection settings. Prefer encrypted transport when supported; do not assume FTPS availability.
- `local-dir` pointing to the reviewed staging directory, with the required trailing slash.
- `server-dir` using the actual confirmed FTP document root, with the required trailing slash. `/htdocs/` is the expected starting point, but an addon domain can have a different directory.
- `dangerous-clean-slate: false` and the action's incremental state tracking preserved.

Exclude Git metadata, workflows, editor settings, private configuration, environment files, local instructions/prompts, scratch files, backups, logs, schema dumps, SQL files, initialization tools, debug/test scripts, patch/refactoring utilities, unrelated application trees, and runtime uploads as established by the inspection.

Do not exclude bundled production JavaScript/CSS merely because it lives in a `libs` directory. Do not install or build `auroratabs/` unless the POS actually requires it.

Document that Git ignores and FTP exclusions are separate controls. Do not expose deployment state information through the public website where server rules can protect it. Do not manually delete or edit state files casually.

## 8. Production access protection

Create or adapt supported Apache rules where necessary. Preserve routing and assets. Check:

- Directory listing and access to `.git`, environment files, private configuration, logs, backups, SQL/schema dumps, and deployment metadata.
- Direct access to setup, repair, test, and migration PHP scripts.
- PHP/script execution in writable upload directories, including handler variants supported by the host.
- Upload extension, MIME/content, filename, and authorization checks in existing handlers.
- Predictable seeded accounts and PINs; require replacement/rotation before live use without printing their values.

Verify hosting support before adding directives that might cause HTTP 500 errors. Keep broader application security findings separate from necessary deployment changes.

## 9. Hosting and GitHub setup instructions

Explain the exact manual steps still needed:

1. Confirm the InfinityFree domain, document root, supported PHP/MySQLi environment, and FTP settings.
2. Back up the existing site, uploads, database, and private configuration before the initial deployment.
3. Create/select the hosting database and prepare its schema separately if needed.
4. Install the private production configuration securely, including the correct browser base path.
5. Create repository secrets under GitHub Repository → Settings → Secrets and variables → Actions → New repository secret:
   - `FTP_SERVER`: the account's FTP hostname, not an assumed website domain.
   - `FTP_USERNAME`: FTP login name.
   - `FTP_PASSWORD`: FTP password, not a MySQL credential.
6. Confirm production prerequisites before enabling the first upload.

Git is already initialized and `origin` exists. Do not reinitialize Git, add a duplicate remote, or rename branches unnecessarily. Do not push, publish, create secrets remotely, or perform a live deployment unless I explicitly authorize those actions.

For the first push, explain how to review `git status` and `git diff`, selectively stage approved files, inspect the staged diff for secrets and unrelated work, commit, and push to the existing `main` branch. Avoid recommending an unreviewed `git add .` while the working tree contains unrelated changes.

For future updates, document review → stage → commit → `git push origin main`. For another computer, document cloning the existing repository, creating its ignored local configuration, preparing a local development database separately, and pulling before starting work. Never synchronize production credentials or production data through Git.

## 10. Validate and report honestly

Perform appropriate static checks without invoking the database bootstrap. Validate PHP syntax, workflow syntax and action inputs, trigger behavior, staging contents, exclusions, configuration loading, and absence of secrets in the deployable artifact.

Once a safe configured environment is available, test storefront navigation, login/logout, role restrictions, branch access, POS/cart, receipt output, inventory, reports, settings, uploads, and AJAX endpoints. Use a test database for writes. Do not create test sales or modify stock on production without explicit authorization.

Check that routine requests do not change schema or seed records. Verify private files and setup endpoints cannot be fetched publicly after deployment when access is available.

Provide a concise final report covering:

1. Files created and modified, distinguishing your edits from pre-existing work.
2. Runtime deployment scope and excluded files.
3. Git exclusions and server-only configuration/media protection.
4. Workflow triggers, validation, concurrency, action version, and confirmed FTP destination.
5. Required GitHub secrets and remaining manual hosting steps.
6. Database bootstrap changes and separate schema preparation instructions.
7. Base-path handling for local XAMPP and production.
8. Sensitive-file findings and any verified credential rotation requirements, without values.
9. Checks completed, checks blocked, and unresolved production prerequisites.
10. Reviewed first-push, future-update, and multiple-computer commands.
11. How to inspect the GitHub Actions run and verify the live application.
12. Rollback: identify the bad commit with `git log --oneline`, use `git revert <commit-hash>`, review and push the revert. Explain that code rollback does not reverse database changes or restore uploaded media; those need separate backups/recovery.

Do not claim successful deployment without an observed successful Actions run. Distinguish a successful file transfer from verified live application behavior.

The final goal is: **I edit MULTI-POS locally → push approved changes to GitHub → GitHub Actions updates the InfinityFree application while preserving production configuration, transactions, inventory, and uploaded media.**
