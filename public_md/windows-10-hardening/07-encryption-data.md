# Encryption & Data Protection

Encryption ensures that even if your device is stolen or compromised, sensitive data remains unreadable without the encryption key.

## BitLocker Drive Encryption

BitLocker encrypts your entire drive — the most important protection for laptop/mobile devices.

### Check BitLocker support

BitLocker is available on **Windows 10 Pro, Enterprise, Education** (not Home).

To check your edition:
- **Settings** → **System** → **About**
- Look for Windows edition

### Enable BitLocker

1. **Control Panel** → **BitLocker Drive Encryption**
2. Click **Turn on BitLocker** (next to C: drive)
3. Choose **Use a password** for unlock (recommended)
4. Enter strong password (12+ characters with symbols)
5. Confirm password
6. Choose to **Print recovery key** or **Save to account**
   - **Recovery key is critical** if password forgotten
   - **Store securely, separately** from device
7. Click **Start encryption**

> **Important:** Save your recovery key! Without it, a forgotten password means permanent data loss.

**Encryption time:** Several hours for full drive — you can use the system meanwhile.

### Verify BitLocker status

```
Control Panel → BitLocker Drive Encryption
```

Should show: "BitLocker on" with checkmark.

### BitLocker alternatives (Home edition)

**Device Encryption:**
- Built into Home edition on compatible hardware
- Slightly less control than BitLocker
- Settings → **System** → **About** → **Device encryption**

**VeraCrypt (third-party):**
- Open-source, free
- Works on all editions
- More complex to use than BitLocker

## File-level encryption (EFS)

For individual files/folders without full disk encryption:

1. Right-click file/folder
2. Select **Properties** → **Advanced**
3. Check **Encrypt contents to secure data**
4. Click **OK** twice

**Note:** EFS provides less protection than BitLocker; use full disk encryption when possible.

## Passwords & credentials

### Windows Credential Manager

Stores passwords for websites, networks, apps:

1. **Control Panel** → **Credential Manager**
2. Review **Web Credentials** and **Windows Credentials**
3. Delete any unused or suspicious entries
4. Consider disabling password storage for sensitive sites

### Use a password manager

Modern password managers (cloud + local):

**Recommended:**
- **Bitwarden** — open-source, free/premium
- **KeePass** — local-only, open-source, free
- **1Password** — commercial, sync-friendly
- **Dashlane** — commercial, strong UX

**Benefits:**
- Generate strong, unique passwords for each site
- Store encrypted, accessible only with master password
- Protect against credential reuse/phishing

## Two-Factor Authentication (2FA)

2FA requires second verification method when logging in.

### For local Windows account

Windows 10 supports:
- **Windows Hello** (face recognition, fingerprint)
- **Security key** (USB hardware key)
- **Authenticator app** (backup for Microsoft account)

### Enable Windows Hello

1. **Settings** → **Accounts** → **Sign-in options**
2. Under **Windows Hello:**
   - Click **Face** or **Fingerprint**
   - Follow setup wizard
   - Add multiple faces/fingerprints for robustness

### For cloud services (Microsoft account)

1. Visit **account.microsoft.com**
2. **Security** → **Two-step verification**
3. Choose second factor:
   - **Authenticator app** (Microsoft Authenticator)
   - **Security code via SMS**
   - **Security key** (FIDO2 device)

## Backup & recovery

> **Critical:** Regular backups are **essential disaster recovery**. Follow the 3-2-1 rule below.

### File History (incremental backup)

1. **Settings** → **System** → **About** → **Advanced system settings** → **System Protection** tab
2. Click **Configure**
3. Select **Turn on system protection**
4. Set max usage (20-30% of drive space recommended)
5. Click **OK** → **Create** to make first restore point

### System Image backup

Full disk snapshot for rapid recovery:

1. **Control Panel** → **Backup and Restore (Windows 7)**
2. Click **Create system image**
3. Choose destination (external drive recommended)
4. Select drives to include
5. Click **Start backup** (can take 30min-1hr)

### Cloud backup

Microsoft OneDrive automatic sync:

1. **Settings** → **Accounts** → **Sync your settings**
2. Turn on **Sync your settings**
3. Choose what to sync (files, settings)

**Also consider:** Third-party services (Backblaze, Carbonite) for continuous cloud backup.

### Backup best practice — The 3-2-1 Rule

| Rule | Meaning | Example |
|------|---------|---------|
| **3 copies** | Original + 2 backups | Main drive + external + cloud |
| **2 media types** | Different storage technologies | SSD + cloud service |
| **1 off-site** | Separate physical location | Cloud provider in different region |

## Disk wiping for disposal

When selling/donating Windows 10 device:

### Reset this PC

1. **Settings** → **System** → **About** → **Reset this PC**
2. Click **Reset PC**
3. Choose **Remove everything**
4. Select **Cloud download** or **Local reinstall**
5. Confirm to remove files and reinstall Windows

> **For maximum security:** Use **DBAN** (Darik's Boot and Nuke) or manufacturer tool for multiple-pass secure erasure. Built-in reset leaves some recovery possible.

**Next:** [System Monitoring & Maintenance](08-monitoring.md)
