# Environment & Config

A nulled `.env.example` I copy into every project, plus `config/seeding.php` — the one config file the base uses. **Every value here is blank or a placeholder** — real credentials live only in the untracked `.env` on each machine/server.

> **Danger** — `.env` is never committed and never deployed (it's in `.gitignore` and the FTP `exclude` list). Only ever commit `.env.example` with empty values. Personal data (real emails, NIFs, addresses, keys) belongs in `.env` alone.

## Nulled `.env.example`

```ini
# Application
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://my-app.localhost

# Frontend / Vite
VITE_SERVER_URL=http://127.0.0.1:5173
VITE_APP_NAME="${APP_NAME}"

# Logs
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Database (PostgreSQL — switch to mysql/3306 if required)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# Cache / Session / Queue (shared-hosting friendly defaults)
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log

# Redis (optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Dev tooling
DEBUGBAR_ENABLED=false

# --- Seeding (base accounts, read by config/seeding.php) ---
SUPER_ADMIN_NAME=
SUPER_ADMIN_EMAIL=
SUPER_ADMIN_PASSWORD=

ADMIN_NAME=
ADMIN_EMAIL=
ADMIN_PASSWORD=

DUMMY_PASSWORD=

# --- SSH (used by remote:cmds — read via env() directly, NEVER a config file) ---
SSH_USER=
SSH_PORT=
SSH_HOST=
SSH_KEY_PATH=
SSH_PATH_STAGING=
SSH_PATH_PRODUCTION=

# --- SFTP Configuration — Do not upload ---
SFTP_HOST=
SFTP_PORT=21
SFTP_USERNAME=
SFTP_PASSWORD=
SFTP_PROTOCOL=ftp
SFTP_REMOTE_PATH=/
```

> **Note** — Keep this file lean: it's the *core* stack only. App-specific groups (payment keys, third-party APIs, business identity) get appended per project — they are not part of the base. Generate `APP_KEY` after copying with `php artisan key:generate`.

## `config/seeding.php`

Decouples seeders from hardcoded credentials. Seeders read `config('seeding.*')`; the values come from `.env`, so the same seeder is safe in local, staging, and production.

```php
<?php

return [
    'super_admin' => [
        'name'     => env('SUPER_ADMIN_NAME'),
        'email'    => env('SUPER_ADMIN_EMAIL'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
    ],

    'admin' => [
        'name'     => env('ADMIN_NAME'),
        'email'    => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'dummy_password' => env('DUMMY_PASSWORD'),
];
```

> **Tip** — In local, generate test accounts as Gmail `+alias` addresses off the super-admin email (`admin+staff@…`, `admin+demo@…`). One real inbox receives everything; production uses dedicated addresses. Seeders should skip gracefully (log a warning, return) when an email is unset — see the `UserSeeder` on [Database](02-database-postgresql.md).

## No `config/deployment.php` — SSH stays in `.env` only

Deployment connection details are **private info and never go into a config file**, even one that only calls `env()`. A config file gets committed, cached (`config:cache`), and deployed; `.env` does none of those. The `remote:cmds` tool ([page 07](07-remote-commands.md)) reads `env('SSH_USER')`, `env('SSH_HOST')`, `env('SSH_PATH_STAGING')` etc. **directly** — there is nothing to publish.

> **Danger** — If a project ever grows a `config/deployment.php`, it goes in `.gitignore` and the FTP exclude list immediately. Belt and braces: private connection data should have exactly one home — the untracked `.env`.

## Per-environment env files

For SSH/deploy targeting, keep an untracked `.env.staging` and `.env.production` alongside `.env` locally. `remote:cmds` checks the target's `.env.{env}` exists and resolves the remote path per environment from your local `.env`'s `SSH_PATH_*` keys. They follow the same nulled template — filled in only on your machine, never committed (`.gitignore` covers `*.env`, `.env.staging`, `.env.production`).

## Next

- [Remote commands](07-remote-commands.md)
