# SSH Keys and Secrets

Generate a dedicated deploy key and add it to Hostinger and GitHub.

## Generate a deploy key

Create a dedicated SSH key for deployments. Don’t reuse your personal key.

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/your_app_deploy
```
On Windows (PowerShell), use an empty string passphrase like this:

```powershell
ssh-keygen -t ed25519 -C "your-app-deploy" -f "$env:USERPROFILE\.ssh\your_app_deploy" -N '""'
```
This creates:

- `~/.ssh/your_app_deploy` — private key for GitHub Secrets
- `~/.ssh/your_app_deploy.pub` — public key for hPanel

## Add the public key to hPanel

Copy the public key from your machine:

```powershell
Get-Content "$env:USERPROFILE\.ssh\your_app_deploy.pub" | Set-Clipboard
```

Then paste it into **SSH Access → Add SSH Key** in hPanel. Give the key a descriptive name matching your app (e.g. `your-app-deploy`).

To copy the public key to clipboard on Windows:

```powershell
Get-Content "$env:USERPROFILE\.ssh\your_app_deploy.pub" | Set-Clipboard
```

Test the connection locally:

```powershell
ssh -i "$env:USERPROFILE\.ssh\your_app_deploy" -p 65002 uXXXXXXXXX@your-server-ip
```

## Add GitHub secrets

Go to **GitHub → Repository → Settings → Environments → your environment → Secrets** and add:

| Secret | Value |
|---|---|
| `SSH_HOST` | Server IP |
| `SSH_USER` | Hostinger username |
| `SSH_PORT` | `65002` |
| `SSH_PRIVATE_KEY` | Contents of `~/.ssh/your_app_deploy` |
| `SSH_PASSPHRASE` | Key passphrase (omit if none set) |
| `FTP_SERVER` | Server IP or domain |
| `FTP_USER` | Full FTP username — find in **hPanel → FTP Accounts** (format: `u123456789.accountname`) |
| `FTP_PASS` | FTP account password — set in **hPanel → FTP Accounts** (not your hPanel login password) |
| `FTP_PORT` | `21` |
| `FTP_SERVER_DIR` | `/home/uXXXXXXXXX/domains/yourapp.com/public_html` |

> **Important** — `SSH_PRIVATE_KEY` must keep its newlines intact.
> Paste it from a text editor, not from a terminal.

To copy the **private key** to clipboard on Windows:

```powershell
Get-Content "$env:USERPROFILE\.ssh\your_app_deploy" | Set-Clipboard
```

> **Tip** — `FTP_USER` is the full username shown in **hPanel → FTP Accounts**, e.g. `u123456789.yourapp`. Using just the hPanel username will cause a `530 Login incorrect` error.

> **Note** — If you generated the key without a passphrase, omit `SSH_PASSPHRASE` from your workflow entirely.

## Next

- [Workflow file](03-workflow.md)
