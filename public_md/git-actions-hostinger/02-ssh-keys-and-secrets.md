# SSH Keys and Secrets

Generate a dedicated deploy key and add it to Hostinger and GitHub.

## Generate a deploy key

Create a dedicated SSH key for deployments. Don’t reuse your personal key.

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/your_app_deploy
```

This creates:

- `~/.ssh/your_app_deploy` — private key for GitHub Secrets
- `~/.ssh/your_app_deploy.pub` — public key for hPanel

## Add the public key to hPanel

Copy the public key from your machine:

```powershell
Get-Content "$env:USERPROFILE\.ssh\your_app_deploy.pub" | Set-Clipboard
```

Then paste it into **SSH Access → Add SSH Key** in hPanel.

Test the connection locally:

```bash
ssh -i ~/.ssh/your_app_deploy -p 65002 uXXXXXXXXX@your-server-ip
```

## Add GitHub secrets

Go to **GitHub → Repository → Settings → Environments → your environment → Secrets** and add:

| Secret | Value |
|---|---|
| `SSH_HOST` | Server IP |
| `SSH_USER` | Hostinger username |
| `SSH_PORT` | `65002` |
| `SSH_PRIVATE_KEY` | Contents of `~/.ssh/your_app_deploy` |
| `SSH_PASSPHRASE` | Key passphrase |
| `FTP_SERVER` | Server IP or domain |
| `FTP_USER` | FTP username |
| `FTP_PASS` | FTP password |
| `FTP_PORT` | `21` |
| `FTP_SERVER_DIR` | `/home/uXXXXXXXXX/domains/yourapp.com/public_html` |

> **Important** — `SSH_PRIVATE_KEY` must keep its newlines intact.
> Paste it from a text editor, not from a terminal.

## Next

- [Workflow file](03-workflow.md)
