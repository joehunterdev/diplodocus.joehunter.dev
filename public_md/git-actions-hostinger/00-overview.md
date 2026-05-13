# GitHub Actions → Hostinger Deploy Guide

This mini-guide explains a practical GitHub Actions deployment setup for Hostinger shared hosting.
The recommended approach is a hybrid one:

- **FTP** uploads changed files from the GitHub runner
- **SSH** runs post-deploy commands on the server

Hostinger shared hosting often blocks direct SSH from GitHub-hosted runners, so the hybrid setup is the reliable path.

## The deploy flow

```mermaid
flowchart LR
    Dev[Developer] -->|git push| Branch[release/production]
    Branch --> GHA[GitHub Actions Runner]
    GHA -->|FTP upload changed files| Hostinger[(Hostinger Server)]
    GHA -->|SSH post-deploy hook| Hostinger
    Hostinger -->|composer install| App[Live app]
```

## Pages in this guide

- [Hostinger setup](01-hostinger-setup.md)
- [SSH keys and secrets](02-ssh-keys-and-secrets.md)
- [Workflow file](03-workflow.md)
- [Troubleshooting](04-troubleshooting.md)
- [Attachments](attachments/README.md)

## Next

- [Hostinger setup](01-hostinger-setup.md)
