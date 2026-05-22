# Application & Software Security

Applications are attack vectors — malicious or vulnerable software can compromise your system. Careful management reduces risk.

## Installation practices

### Only install from official sources

- **Microsoft Store** — curated, sandboxed
- **Official websites** — Download directly from vendor
- **Reputable package managers** — Chocolatey, Ninite (Windows)
- **Avoid:** Torrent sites, random forums, untrusted sources

### Verify downloads

Many vendors publish checksums (hash values) for verification:

1. Download file and checksum from official site
2. Open **PowerShell (Admin)**
3. Navigate to download folder
4. Run:
   ```powershell
   Get-FileHash filename.exe -Algorithm SHA256
   ```
5. Compare output to official hash — must match exactly

### Smart Screen protection

**Windows Defender SmartScreen** warns about unrecognized downloads:

- **Don't bypass warnings** for files from unknown sources
- This is usually legitimate protection working
- Only proceed if you absolutely trust the source

## Application permissions

### Review app permissions

1. **Settings** → **Privacy & security** → **App permissions**
2. Review installed app access to:
   - Camera, Microphone
   - Location, Contacts
   - Files, Photo library
3. Disable unused permissions

### Microphone/Camera security

Disable unless needed:

1. **Settings** → **Privacy & security** → **Microphone**
   - Toggle off if not regularly used
2. **Settings** → **Privacy & security** → **Camera**
   - Toggle off if not regularly used

### File access restrictions

Control which apps access your files:

1. **Settings** → **Privacy & security** → **File access**
2. Review apps listed
3. Disable for untrusted applications

## Browser security

Web browsing is primary attack vector. Browser choice matters:

### Recommended browsers
- **Microsoft Edge** — tight Windows 10 integration, good defaults
- **Firefox** — privacy-focused, open-source
- **Chrome** — frequent updates, good sandbox

### Disable dangerous features

**Extensions/Add-ons:**
- Only install from official stores
- Disable extensions you don't use
- Review permissions each requests

**Flash:**
- Windows 10 has Flash blocked by default — keep it that way
- Avoid sites requiring Flash

**JavaScript:**
- Don't disable globally (breaks most sites)
- Use extension (NoScript) for fine control if needed

**Plugins:**
- Disable Java (rarely needed, frequently exploited)
- Disable other plugins unless specifically required

### Browser auto-update

Ensure browsers auto-update:

**Edge:**
- **Settings** → **About Microsoft Edge**
- Auto-updates automatically

**Firefox:**
- **Menu (≡)** → **Options** → **General** → **Firefox Updates**
- Select **Automatic**

**Chrome:**
- **Menu (⋮)** → **Settings** → **About Chrome**
- Auto-updates automatically

## Driver updates

Drivers interface directly with hardware — keep current:

### Automatic driver updates

1. **Settings** → **Update & Security** → **Windows Update**
2. **Advanced options** → **Optional updates**
3. Check for driver updates regularly

### Device-specific drivers

For hardware-specific issues:

- **GPU (NVIDIA/AMD):** Download from official driver sites
- **Motherboard:** OEM website for chipset/network drivers
- **Printer:** Manufacturer support page

**Caution:** Only download drivers from official sources.

## Disable unnecessary services

Some Windows services aren't needed and can be disabled:

### Via Services GUI

1. Press **Win+R**, type `services.msc`
2. Find service in list
3. Right-click → **Properties**
4. Startup type: **Disabled** (if not needed)
5. **Stop** if currently running
6. Click **OK**

### Services to consider disabling (if not using):

- **Bluetooth Support Service** — if no Bluetooth
- **Print Spooler** — if no printing
- **Remote Desktop** — unless you use remote access
- **SSDP Discovery** — media sharing (UPnP)

**Be careful:** Disabling wrong services can break Windows. Document changes.

## Autostart programs

Reduce startup time and attack surface:

1. Press **Ctrl+Shift+Esc** → **Task Manager**
2. Click **Startup** tab
3. Right-click unwanted programs → **Disable**
4. Remove startup shortcuts:
   - Press **Win+R**
   - Type: `shell:startup`
   - Delete unnecessary shortcuts

## Malware protection

### Windows Defender (built-in)

- Always enabled by default
- Provides real-time scanning
- Sufficient for most users

### Additional scanning

Periodic deep scans catch missed threats:

1. **Windows Security** → **Virus & threat protection** → **Scan options**
2. Select **Full scan** (takes longer, more thorough)
3. Click **Scan now**

### Third-party antivirus

Only use ONE real-time antivirus:

- **Recommended:** Windows Defender (built-in, efficient)
- **Alternative:** Bitdefender, Kaspersky, ESET
- **Avoid:** Multiple real-time scanners (conflict/slowdown)

## Software removal

Uninstall unused software:

1. **Settings** → **Apps** → **Apps & features**
2. Find software
3. Click it, then **Uninstall**
4. Follow uninstaller prompts

**Tip:** Use **Revo Uninstaller** to remove leftover registry entries.

**Next:** [Encryption & Data Protection](07-encryption-data.md)
