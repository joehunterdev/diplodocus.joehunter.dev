# Workflow File

Create `.github/workflows/deploy-production.yml` for the deployment job. Generic copies of all workflow files are in the `attachments/` folder.

## deploy-production.yml

Triggers on push to `main` and deploys changed files via FTP, then runs `composer install` on the server via SSH.

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest
    environment: production.sitename
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
        with:
          fetch-depth: 0

      - name: Deploy changed files via FTP
        uses: SamKirkland/FTP-Deploy-Action@v4.3.6
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USER }}
          password: ${{ secrets.FTP_PASS }}
          port: ${{ secrets.FTP_PORT }}
          protocol: ftp
          local-dir: ./
          server-dir: /public_html/
          state-name: .ftp-deploy-sync-state.json
          exclude: |
            .git/**
            .github/**
            node_modules/**
            vendor/**
            .env*
```

## Excluding files

The `exclude` list tells FTP Deploy which files to skip. At minimum exclude:

| Pattern | Reason |
|---|---|
| `.git/**`, `.github/**` | Version control — not needed on server |
| `node_modules/**`, `vendor/**` | Install on server via SSH instead |
| `.env*` | Keep `.env` on server, never deploy it |

Common additions for Laravel:

```yaml
exclude: |
  tests/**
  storage/logs/**
  storage/framework/cache/**
  storage/framework/sessions/**
  storage/framework/testing/**
  storage/framework/views/**
  *.log
  phpunit.xml
  package*.json
  *.md
  **/*.md
```

> **Tip** — The more you exclude, the faster incremental deploys run. Exclude anything that's installed, generated, or environment-specific on the server.

## FTP sync state

`FTP-Deploy-Action` keeps a file called `.ftp-deploy-sync-state.json` on the server to track which files have been uploaded. On the next deploy it diffs against this state and only uploads what changed — making subsequent deploys much faster.

- The `state-name` option controls the filename on the server
- It's stored in `server-dir` (i.e. `/public_html/`)
- **Don't delete it** — removing it forces a full re-upload on the next run
- Add it to `.gitignore` so it's never committed locally
- If you need to force a full re-deploy (e.g. after a server wipe), delete the state file from the server via FTP/SSH, then push again

```bash
# Force full re-deploy by removing the state file via SSH
rm /home/uXXXXXXXXX/domains/yourapp.com/public_html/.ftp-deploy-sync-state.json
```

      - name: Composer install (no-dev, optimized)
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          port: ${{ secrets.SSH_PORT }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd ${{ secrets.FTP_SERVER_DIR }}
            /opt/alt/php85/usr/bin/php /usr/local/bin/composer2 install --no-dev --no-interaction --optimize-autoloader
```

> **Note** — The PHP path `/opt/alt/php85/usr/bin/php` and composer binary `/usr/local/bin/composer2` are correct for **PHP 8.5 on Hostinger shared hosting** (tested with Laravel 13). If you're on a different PHP version, adjust accordingly. Use the `test-ssh.yml` workflow to confirm the correct paths on your plan.

## Test workflows

Run these manually from **GitHub → Actions → Run workflow** before pushing to `main`:

- `test-ssh.yml` — verifies SSH connection, prints PHP/composer paths, checks server directory
- `test-ftp.yml` — dry-run FTP deploy (no files uploaded, just validates credentials and lists changes)
- `test-node.yml` — installs npm deps and runs a Vite build to verify the frontend compiles

## Push and verify

```bash
git push origin main
```

## Next

- [Troubleshooting](04-troubleshooting.md)
