# Troubleshooting

Common issues you may see during setup or deployment.

## `530 Login incorrect`

- Check `FTP_USER` in hPanel → FTP Accounts
- Make sure `FTP_PASS` is the FTP password, not your hPanel login

## `ssh: no key found`

- Re-paste `SSH_PRIVATE_KEY` with newlines intact
- Do not copy the key from a terminal window

## `dial tcp x.x.x.x:65002: i/o timeout`

- Hostinger may block the runner IP
- Use `appleboy/ssh-action` for SSH, not raw `run:` steps

## `Permission denied (publickey)`

- Confirm the public key in hPanel matches `~/.ssh/your_app_deploy.pub`

## PHP command not found

- Use the full PHP path on Hostinger shared hosting
- Check available binaries with `ls /usr/local/php*/bin/php`

## Notes

- Keep `SSH_PORT` as a secret instead of hardcoding it
- Exclude `vendor/` from FTP; install dependencies on the server
- Keep `.env` on the server and out of the deployment
- Leave `.ftp-deploy-sync-state.json` alone so the sync stays incremental

## Next

- [GitHub Actions → Hostinger Deploy Guide](00-overview.md)
