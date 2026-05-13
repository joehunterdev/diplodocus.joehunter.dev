# Hostinger Setup

Start by enabling SSH access in hPanel.

## Enable SSH

1. Log into **hPanel**
2. Go to **Hosting → Manage → SSH Access**
3. Turn SSH **on**
4. Note these values:
   - **Host** — your server IP
   - **Username** — usually `uXXXXXXXXX`
   - **Port** — `65002`

> **Tip** — Hostinger shared hosting uses `65002`, not `22`.

## What you need before continuing

- Hostinger account with SSH access enabled
- GitHub repository with Actions enabled
- A GitHub Environment for production
- Local machine with `ssh-keygen`

## Next

- [SSH keys and secrets](02-ssh-keys-and-secrets.md)
