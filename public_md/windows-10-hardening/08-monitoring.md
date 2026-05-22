# System Monitoring & Maintenance

Ongoing monitoring and maintenance keep your system healthy, catch threats early, and ensure security controls remain effective.

## Windows Security Dashboard

Centralized security status view:

1. **Settings** → **Update & Security** → **Windows Security**
2. Review five areas:
   - **Virus & threat protection**
   - **Device performance & health**
   - **Firewall & network protection**
   - **App & browser control**
   - **Device security**

All should show **No issues** with green checkmarks.

## Virus & threat protection

### Run regular scans

1. **Windows Security** → **Virus & threat protection**
2. Click **Scan options**
3. Choose scan type:
   - **Quick scan** — 5-10 minutes, most common threats
   - **Full scan** — 1-2 hours, comprehensive
   - **Custom scan** — specific folder
4. Click **Scan now**

### Schedule weekly full scans

Automate regular scanning:

1. **Windows Defender Advanced Threat Protection** (not available in Home)
   - Or use **Task Scheduler** to automate manual scans

For Home edition, manually run full scan weekly:

```powershell
Task Scheduler → Create Basic Task
Name: Weekly Defender Scan
Trigger: Weekly (day/time of choice)
Action: Program: C:\Program Files\Windows Defender\MsMpEng.exe
Arguments: -Scan -ScanType 2
```

> **Tip:** Schedule scans for off-hours (late night or weekend) to avoid performance impact.

### Quarantine suspicious files

Defender quarantines detected threats:

1. **Windows Security** → **Virus & threat protection** → **Protection history**
2. Review quarantined items
3. If legitimate, select and **Restore**
4. If malicious, **Remove**

## Event Viewer (system logging)

Event logs record system events, errors, security incidents:

1. Press **Win+R**, type `eventvwr`
2. Or: **Control Panel** → **Administrative Tools** → **Event Viewer**

### Monitor important logs

**Security log:**
- Windows Logs → Security
- Logs account logins, policy changes
- Review for suspicious logon attempts

**System log:**
- Windows Logs → System
- Hardware issues, driver problems, shutdowns

**Application log:**
- Windows Logs → Application
- App crashes, compatibility issues

### Filter logs

1. Right-click log (e.g., **Security**)
2. **Filter Current Log**
3. Set Event level: **Warning, Error** (ignore info)
4. Set timeframe: **Last 24 hours**
5. Click **OK**

## Disk cleanup

> **Benefit:** Free disk space improves both performance and security (less space for attackers to hide).

### Disk Cleanup utility

1. Press **Win+R**, type `cleanmgr`
2. Select drive (usually C:)
3. Check items to delete:
   - ✓ Temporary files
   - ✓ Recycle Bin
   - ✓ Downloads (if empty)
   - ✓ Thumbnails cache
4. Click **Clean up system files** (requires Admin)
5. Confirm removals

### Storage Sense (automatic)

1. **Settings** → **System** → **Storage**
2. Turn on **Storage Sense**
3. Set **Automatically free up space:** **Every month**

## Disk defragmentation

Modern SSDs don't need defragmentation; HDDs benefit occasionally:

### Check drive type

1. **Settings** → **System** → **Storage**
2. Look for drive type: **SSD** vs **HDD**

### Defragment HDD (if applicable)

1. Type `Defragment` in Start menu
2. Select **Defragment and Optimize Drives**
3. Select drive, click **Optimize**
4. Monitor progress (can take 30+ minutes)

> **Note:** SSDs don't need defragmentation; modern Windows detects this and skips the process.

## Startup performance

Check what's slowing boot:

1. Press **Ctrl+Shift+Esc** → Task Manager
2. Click **Startup** tab
3. Sort by **Startup impact:** High/Medium
4. Disable unnecessary programs:
   - Right-click → **Disable**
   - (You can re-enable if app stops working)

**Impact:** Disabling high-impact programs can reduce boot from 2+ minutes to <30 seconds.

## Driver updates

Keep hardware drivers current for stability/security:

### Check for updates

1. **Settings** → **Update & Security** → **Windows Update**
2. **Advanced options** → **Optional updates**
3. Look for **Driver updates**
4. Install any available

### GPU driver updates

GPU drivers are critical for gaming/graphics performance:

- **NVIDIA:** nvidia.com/Download/driverDetails
- **AMD:** amd.com/en/support
- Download and install latest driver

## System file integrity

Verify Windows system files haven't been modified:

1. Open **Command Prompt (Admin)**
2. Run:
   ```powershell
   sfc /scannow
   ```
3. Wait for scan to complete (~30 minutes)
4. If issues found, system repairs automatically

For more advanced scan:
```powershell
DISM /Online /Cleanup-Image /RestoreHealth
```

> **Tip:** Run sfc /scannow monthly to catch corruption early.

## Startup troubleshooting

If system won't boot normally:

1. Restart in **Safe Mode:**
   - **Settings** → **Update & Security** → **Recovery** → **Restart now** (under **Advanced startup**)
   - Select **Troubleshoot** → **Advanced options** → **Startup Settings** → **Safe Mode**

2. Use **Startup Repair:**
   - Same recovery menu → **Troubleshoot** → **Advanced options** → **Startup Repair**

3. If all else fails, **Reset this PC:**
   - Completely reinstalls Windows
   - Keep or remove files as needed

## Windows Activation

Ensure your Windows license is valid:

1. **Settings** → **Update & Security** → **Activation**
2. Should show **Windows is activated**
3. If issues, **Troubleshoot** to re-activate

Unactivated Windows limits functionality — keep activation current.

## Performance monitoring

### Task Manager overview

Quick performance snapshot:
- **Ctrl+Shift+Esc** → **Performance** tab
- Monitor: CPU, Memory, Disk, GPU usage

### Resource Monitor (detailed)

Advanced performance analysis:
1. Press **Win+R**, type `resmon`
2. View resource usage by process
3. Identify resource hogs

### Performance Monitor

Advanced logging and alerts:

1. Press **Win+R**, type `perfmon`
2. Create Data Collector Set to monitor over time
3. Set alerts for thresholds

## Task Scheduler maintenance

### Disable unnecessary scheduled tasks

1. Press **Win+R**, type `taskschd.msc`
2. Browse: **Task Scheduler Library** → **Microsoft** → **Windows**
3. Review scheduled tasks
4. Right-click unused tasks → **Disable**

> **Caution:** Disable only tasks you're certain aren't needed. When in doubt, leave it enabled.

**Next:** [Advanced Security Topics](09-advanced.md)
