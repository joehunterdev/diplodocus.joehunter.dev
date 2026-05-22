# Advanced Security Topics

For users wanting deeper security hardening or managing enterprise deployments, these advanced techniques provide additional protection.

## Group Policy hardening

**Requires:** Windows 10 Pro, Enterprise, Education (not Home)

Group Policy Editor allows granular security configuration:

### Access Group Policy Editor

1. Press **Win+R**
2. Type: `gpodit.msc`
3. Click **OK**

### Key policies to harden

Navigate within **gpedit.msc:**

#### Disable unnecessary services

1. **Computer Configuration** → **Windows Settings** → **Security Settings** → **System Services**
2. Double-click service name
3. Set **Startup mode:** **Disabled**
4. Click **OK**

#### Configure password policy

1. **Computer Configuration** → **Windows Settings** → **Security Settings** → **Account Policies** → **Password Policy**
2. Adjust:
   - **Minimum password length:** 12
   - **Maximum password age:** 90 days
   - **Enforce password history:** 10 passwords
   - **Complexity requirements:** Enabled

#### UAC hardening

1. **Computer Configuration** → **Windows Settings** → **Security Settings** → **Local Policies** → **Security Options**
2. Adjust **User Account Control** settings
3. Recommend maximum security settings

## Credential Guard (Pro/Enterprise)

Isolates sensitive credentials in secure container:

### Enable Credential Guard

1. Press **Win+R**, type `gpedit.msc`
2. Navigate: **Computer Configuration** → **Administrative Templates** → **System** → **Device Guard**
3. Set **Turn on Virtualization Based Security:** **Enabled with UEFI lock**
4. Reboot required

**Benefit:** Prevents credential theft even if malware gains admin access.

## Device Guard / Code Integrity

Restricts what code can run on system:

### Enable configurable code integrity

1. **gpedit.msc**
2. **Computer Configuration** → **Administrative Templates** → **System** → **Code Integrity**
3. Set **Enforced:** Mode → **Enabled**
4. Reboot

**Effect:** Only signed drivers and code run; prevents many malware attacks.

## DBAN (Darik's Boot and Nuke)

For secure drive wiping:

1. Download DBAN from **dban.org**
2. Create bootable USB
3. Boot from USB on computer to wipe
4. Select drives, wipe method (DoD 3-pass recommended)
5. Complete wipe prevents data recovery

**Use case:** Before selling/donating computer or removing sensitive data.

## Full Disk Encryption + Pre-boot authentication

Combine BitLocker with UEFI password:

### UEFI/BIOS password

1. Restart computer, press **DEL**, **F2**, or **ESC** (varies by manufacturer)
2. Look for **Security** or **System Security** section
3. Set **Administrator Password** (for BIOS access)
4. Set **User Password** (required at boot)
5. Save & exit

**Effect:** Even with physical drive access, attacker can't boot without password.

### Combine with BitLocker

- UEFI password prevents BIOS modification
- BitLocker password protects drive encryption key
- Two layers prevent attack vectors

## Network isolation

### Windows Sandbox

Isolated virtual environment for testing untrusted programs:

1. **Settings** → **Apps** → **Apps & features** → **Optional features**
2. Click **Add an optional feature**
3. Search & install **Windows Sandbox**
4. Restart system
5. Run **Windows Sandbox** from Start menu
6. Run suspicious program inside sandbox (isolated from real system)

### Hyper-V isolation

More advanced than Sandbox; for testing entire environments:

1. **Settings** → **Apps** → **Apps & features** → **Turn Windows features on or off**
2. Check **Hyper-V**
3. Restart system
4. Launch **Hyper-V Manager**
5. Create virtual machines for testing

## Command line hardening

For advanced users comfortable with PowerShell:

### Disable dangerous PowerShell features

```powershell
# Run as Administrator
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

This allows local scripts but blocks untrusted downloaded scripts.

### Audit sensitive operations

Enable logging of sensitive PowerShell commands:

```powershell
# Administrator PowerShell
New-Item -Path "HKLM:\SOFTWARE\Policies\Microsoft\Windows\PowerShell" -Force
New-ItemProperty -Path "HKLM:\SOFTWARE\Policies\Microsoft\Windows\PowerShell" `
  -Name "ExecutionPolicy" -Value "RemoteSigned" -PropertyType String
```

## Security baseline

Microsoft publishes recommended security configurations:

### Apply baseline

1. Download **Windows 10 Security Baseline** from Microsoft
2. Extract to `C:\Windows\System32\GroupPolicy`
3. Run:
   ```
   gpupdate /force
   ```
4. Restart system

**Effect:** All Microsoft-recommended security settings applied at once.

## Threat modeling

For those managing sensitive data, assess threats:

**Questions to ask:**
- What data do I need to protect?
- Who might target this data?
- What attacks are most likely?
- Which controls provide best protection for risk?

**Result:** Prioritize security spending on highest-risk areas.

## Incident response

If you suspect compromise:

1. **Isolate** — disconnect from network immediately
2. **Document** — take screenshots of suspicious activity
3. **Preserve** — don't shut down; preserve evidence
4. **Notify** — contact IT if business system
5. **Investigate** — review Event Viewer logs, file modifications
6. **Recover** — reinstall OS from clean backup

## Professional assessment

For critical systems, consider professional security audit:

- **Penetration testing** — professional attempts to break in
- **Vulnerability scanning** — tools find known issues
- **Security audit** — review against standards (NIST, CIS)

**Cost:** Ranges from $500-5000+ depending on scope.

**Next:** [Resources & Further Reading](10-resources.md)
