# Remote Commands

`remote:cmds` is a custom Artisan command I carry between projects. It runs a **whitelisted** set of maintenance tasks on staging/production over SSH, and pulls logs down — without ever opening a shell or letting arbitrary commands through. It's the day-to-day companion to the GitHub Actions deploy on [page 08](08-deployment.md).

It lives at `app/Console/Commands/RemoteCmds.php` and reads its connection details **directly from `.env`** (`SSH_USER`, `SSH_HOST`, `SSH_PORT`, `SSH_KEY_PATH`, `SSH_PATH_STAGING`, `SSH_PATH_PRODUCTION`) — deliberately **not** from a config file; see [Environment & config](06-environment-and-config.md).

**Download the full command** (nulled — everything env-driven, no credentials inside):

[📦 07a-RemoteCmds.php](attachments/07a-RemoteCmds.php) — drop into `app/Console/Commands/`, fill the `SSH_*` keys in `.env`, done.

## Usage

```bash
php artisan remote:cmds                        # interactive: pick env + commands
php artisan remote:cmds staging                # pre-select staging, then choose
php artisan remote:cmds production migrate      # run one command
php artisan remote:cmds production fresh logs   # chain several
```

Signature:

```php
protected $signature = 'remote:cmds
    {environment? : staging|production}
    {commands?* : cache, optimize, fresh, migrate, seed, composer, dump, symlink, logs, download-logs, clear-logs}';
```

## The whitelist

Commands are a hardcoded map — the user picks a **key**, never types the command. `PHP_PATH` / `COMPOSER_PATH` are swapped per environment (e.g. Hostinger's `/opt/alt/php85/usr/bin/php` and `/usr/local/bin/composer2`).

```php
private const ALLOWED_COMMANDS = [
    'cache'    => 'PHP_PATH artisan cache:clear && PHP_PATH artisan config:clear && PHP_PATH artisan view:clear && PHP_PATH artisan route:clear',
    'optimize' => 'PHP_PATH artisan config:cache && PHP_PATH artisan route:cache && PHP_PATH artisan view:cache && PHP_PATH artisan event:cache',
    'fresh'    => 'PHP_PATH artisan migrate:fresh --seed --force --no-interaction -v',
    'migrate'  => 'PHP_PATH artisan migrate --force --no-interaction -v',
    'seed'     => 'PHP_PATH artisan db:seed --force --no-interaction -v',
    'composer' => 'COMPOSER_PATH install --no-dev --no-interaction --optimize-autoloader',
    'dump'     => 'COMPOSER_PATH dump-autoload',
    'symlink'  => 'PHP_PATH artisan storage:link',
];
```

| Key | What it does |
|---|---|
| `cache` | Clear all caches (config, route, view, app) |
| `optimize` | Rebuild all caches for production |
| `migrate` | Run pending migrations (`--force`) |
| `fresh` | **Drop everything**, re-migrate, re-seed |
| `seed` | Run seeders only |
| `composer` | `install --no-dev --optimize-autoloader` |
| `dump` | `dump-autoload` |
| `symlink` | `storage:link` |

## Log tooling

Beyond the whitelist, three subcommands manage remote logs over SSH:

| Command | Action |
|---|---|
| `logs` | Tail `laravel.log` and colourise errors/warnings inline |
| `download-logs` | SCP logs down to `storage/logs/remote/{env}/` |
| `clear-logs` | Truncate remote logs (double-confirmed) |

## FTP sync-state helpers

Because deploys use `FTP-Deploy-Action`'s differential sync (page 08), the command can also manage that state file:

- `check-sync` — report whether the remote `.ftp-deploy-sync-state.json` exists
- `reset-sync` — back it up, then delete it to force a full re-upload next push
- `restore-sync` — re-upload the latest backup

## Security model

The command is deliberately paranoid — it's the difference between a handy tool and a foot-gun:

1. **Local only.** Refuses to run unless `APP_ENV=local`.
2. **Config gate.** Aborts if SSH config or the target `.env.{env}` is missing.
3. **Production confirmation.** An explicit `confirm()` prompt before anything touches production.
4. **Path validation.** The remote path must be absolute, contain no `..`, and match a safe charset.
5. **Audit log.** Every execution is appended to `storage/logs/remote-commands.log`.

```php
if (config('app.env') !== 'local') {
    $this->error('SSH commands must be run from local (APP_ENV=local).');
    return self::FAILURE;
}

if ($env === 'production' && ! $this->confirm('⚠️  Work with PRODUCTION. Continue?')) {
    return self::FAILURE;
}
```

> **Warning** — `fresh` runs `migrate:fresh --seed` and **destroys the remote database**. It's on the whitelist for convenience on staging; think twice before ever aiming it at production.

## Frequently-used flows

```bash
# After a normal deploy (migrations + cache rebuild)
php artisan remote:cmds production migrate optimize

# Rebuild staging from scratch
php artisan remote:cmds staging fresh

# Something's broken in prod — clear caches and read the log
php artisan remote:cmds production cache logs

# Pull prod logs down for local inspection
php artisan remote:cmds production download-logs
```

> **Tip** — Copy the whole `RemoteCmds.php` class into a new project, then adjust only the per-environment `PHP_PATH`/`COMPOSER_PATH` in `prepareCommand()` to match the host. Everything else is portable.

## Next

- [Deployment](08-deployment.md)
