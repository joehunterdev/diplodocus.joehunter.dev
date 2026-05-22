# User Accounts & Access Control

Proper account management and privilege separation form the foundation of system security. The principle of least privilege means running with minimum necessary permissions.

## Account types

Windows provides two primary account types:

### Administrator accounts
- Full system access
- Can install software, modify settings, access all files
- **Risk:** If compromised, attacker gains full control
- **Best practice:** Don't use daily

### Standard user accounts
- Limited permissions
- Can run applications, change personal settings
- Cannot modify system settings or install software
- **Recommended:** Use for daily work

## Configure your accounts

### Create a standard user account

1. **Settings** → **Accounts** → **Family & other users**
2. Click **Add someone else to this PC**
3. Use a **Microsoft account** or **Local account** (local more private)
4. Select account type: **Standard user**

### Switch from admin to standard daily account

1. Create standard account (above)
2. Log in daily using standard account
3. Reserve admin account for maintenance only
4. Use **Run as administrator** (right-click) when needed

### Enable password requirements

1. **Settings** → **Accounts** → **Sign-in options**
2. Ensure **Password** is selected (not PIN-only)
3. Set **Require sign-in:** After **5-10 minutes**

### Use strong passwords

- **Minimum:** 12+ characters
- **Include:** uppercase, lowercase, numbers, symbols
- **Avoid:** dictionary words, personal info
- **Consider:** Passphrase instead (e.g., "BlueSky2024!Mountain")
- **Tool:** Password manager (Bitwarden, 1Password, KeePass)

## User Account Control (UAC)

UAC prompts when apps try to modify system settings or when you elevate to admin.

### Verify UAC is enabled

1. **Settings** → **Accounts** → **Change user account control settings**
2. Slider should be at **Notify me only when apps try to make changes** (second from top)
3. DO NOT set to lowest level
4. Click **OK** (you'll see UAC prompt — expected)

### UAC best practices

- **Don't dismiss prompts casually** — read what's requesting access
- **Only approve what you initiated** — if you didn't start it, deny
- **Be cautious of legitimate-looking prompts** — malware can spoof them

## Guest account

**Disable the guest account** unless specifically needed:

1. **Settings** → **Accounts** → **Family & other users**
2. Select **Guest**
3. Click **Remove**

## Remove built-in accounts

Windows 10 includes hidden accounts for system functions. Hide unnecessary ones:

- **Control Panel** → **User Accounts** → **Manage another account**
- Identify built-in accounts (SYSTEM, LOCAL SERVICE)
- These should not appear in login screen

## Credential Guard (Pro/Enterprise only)

Adds enhanced protection for credentials stored in memory:

1. **gpedit.msc** (Group Policy Editor)
2. Navigate: **Computer Configuration** → **Administrative Templates** → **System** → **Device Guard**
3. Set **Turn on Virtualization Based Security:** **Enabled**
4. Requires reboot

## Multi-factor authentication

For Microsoft accounts:

1. Go to **account.microsoft.com**
2. **Security** → **Advanced security options**
3. Enable **Two-step verification**
4. Use **Microsoft Authenticator** app as factor

**Next:** [Network & Firewall Security](05-network-firewall.md)
