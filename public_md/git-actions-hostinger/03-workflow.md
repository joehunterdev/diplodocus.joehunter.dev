# Workflow File

Create `.github/workflows/deploy-production.yml` for the deployment job.

## Workflow

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - release/production

jobs:
  deploy:
    runs-on: ubuntu-latest
    environment: production.yourapp.com
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
          server-dir: ${{ secrets.FTP_SERVER_DIR }}/
          state-name: .ftp-deploy-sync-state.json
          exclude: |
            .git/**
            .github/**
            .docs/**
            .vscode/**
            node_modules/**
            vendor/**
            tests/**
            storage/logs/**
            storage/framework/cache/**
            storage/framework/sessions/**
            storage/framework/testing/**
            storage/framework/views/**
            .env*
            *.log
            phpunit.xml
            package*.json

      - name: Composer install (no-dev, optimized)
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          port: ${{ secrets.SSH_PORT }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          passphrase: ${{ secrets.SSH_PASSPHRASE }}
          script: |
            cd ${{ secrets.FTP_SERVER_DIR }}
            composer install --no-dev --no-interaction --optimize-autoloader
```

## Push and verify

```bash
git push origin release/production
```

## Next

- [Troubleshooting](04-troubleshooting.md)
