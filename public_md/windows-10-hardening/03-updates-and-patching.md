# System Updates & Patch Management

The foundation of any security posture is keeping your operating system and software current. Patches address known vulnerabilities that attackers actively exploit.

## Enable Windows Update

Windows Update is your primary mechanism for receiving security patches.

### Settings

1. Open **Settings** → **Update & Security** → **Windows Update**
2. Click **Check for updates**
3. Ensure **Automatic (recommended)** is selected

### Configure update behavior

**For Home editions:**
- Updates install automatically; plan for restarts
- Restarts typically occur during off-hours
- Use **Active hours** to prevent interruptions during work

**For Pro/Enterprise:**
- More granular control via Group Policy
- Consider **Windows Update for Business** for managed deployments
- Stagger updates across devices to maintain uptime

## Verify Windows is current

```
Settings → System → About
```

Look for:
- Latest **Windows 10 version number** (currently 22H2)
- **OS Build** with latest patches
- **Experience Index** showing system health

## Additional security updates

Beyond OS patches, keep these current:

- **Microsoft Defender** (Windows Defender definitions)
- **BIOS/Firmware** (manufacturer tools or settings)
- **Chipset drivers** (Intel/AMD updates)
- **Third-party software** (browsers, utilities)

## Update verification

To check patch history:

```
Settings → Update & Security → Update history
```

You should see regular monthly patches (typically second Tuesday).

## Troubleshooting update issues

If Windows Update stalls or fails:

1. Run **Windows Update Troubleshooter**
   - Settings → Update & Security → Troubleshoot
   - Select **Windows Update** → Run troubleshooter

2. Clear temporary update files (advanced)
   ```
   Command Prompt (Admin):
   DISM /Online /Cleanup-Image /StartComponentCleanup /ResetBase
   ```

3. Check **Windows Update Log**
   - `%WinDir%\Logs\WindowsUpdate` folder

## Backup before major updates

Even with the best practices, major OS updates can occasionally cause issues. Maintain backups:

- Use **File History** (continuous file backup)
- Create system image before major updates
- Test in VM if possible before production systems

**Next:** [User Accounts & Access Control](04-user-accounts.md)
