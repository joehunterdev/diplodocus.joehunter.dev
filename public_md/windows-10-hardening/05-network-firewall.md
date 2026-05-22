# Network & Firewall Security

Network security controls prevent unauthorized access from the network and restrict outbound connections to trusted destinations.

## Windows Defender Firewall

### Verify firewall is enabled

1. **Settings** → **Update & Security** → **Windows Security** → **Firewall & network protection**
2. Confirm all three are enabled:
   - Domain network
   - Private network
   - Public network
3. All should show **Active** with green checkmarks

### Understand firewall modes

**Domain Network (Connected at work):**
- Most restrictive
- Appropriate when on corporate network
- IT policies override local settings

**Private Network (Home/trusted):**
- Balanced — blocks inbound by default, allows local browsing
- Appropriate for home WiFi

**Public Network (Coffee shop/airport):**
- Most restrictive — blocks most inbound connections
- Use when on untrusted networks

### Inbound/Outbound rules

Firewall operates in two directions:

**Inbound (blocking by default):**
- Prevents external systems from initiating connections
- Applications request exceptions (e.g., gaming, file sharing)
- Safer approach — deny by default

**Outbound (allowing by default):**
- Most traffic is allowed to leave your system
- Can be restricted to trusted destinations (advanced)

### Advanced Firewall Settings

Access **Windows Defender Firewall with Advanced Security:**

```
Start → Windows Defender Firewall with Advanced Security
(or: wf.msc)
```

#### Inbound rules

- **Scope:** Shows source IP/network
- **Action:** Allow, Block, or Allow if secure
- **Profile:** Domain, Private, Public

Review installed application rules — disable unused ones:

1. Select **Inbound Rules**
2. Right-click suspicious or old applications
3. Click **Disable** (don't delete initially)

#### Outbound rules (advanced)

To restrict outbound to trusted destinations:

1. Select **Outbound Rules**
2. Click **New Rule** → **Port**
3. Protocol: TCP/UDP, Port: 443 (HTTPS), 80 (HTTP)
4. Action: Allow
5. Repeat for other necessary ports (mail, DNS, etc.)
6. Create final rule: All → Block (catch-all)

**Warning:** This requires careful planning; can break legitimate apps.

## Network segmentation

### Disable file sharing when not needed

1. **Settings** → **Network & Internet** → **Advanced network settings** → **Advanced sharing options**
2. Under **Private (current network):**
   - Turn off **Network discovery**
   - Turn off **File and printer sharing**
3. Confirm applies to **All networks**

### HomeGroup (Windows 10 1903+)

HomeGroup was deprecated; disable if still present:

```
Control Panel → HomeGroup → Leave
```

## WiFi security

### Connect to WPA3 or WPA2

When available, use modern encryption:

- **WPA3** — latest, best security
- **WPA2** — acceptable current standard
- **WEP** — obsolete, don't use

### WiFi settings

1. **Settings** → **Network & Internet** → **WiFi**
2. Click **Manage known networks**
3. For each network:
   - Set **Metered connection:** OFF (if unlimited)
   - Ensure **Auto-connect:** ON (convenience vs. choosing each time)
   - Set **Hidden network:** OFF (less secure)

### Guest WiFi

If router supports guest network:
- Create separate guest network for visitors
- Use different password than main network
- Isolate from main system devices

## DNS security (advanced)

DNS queries currently unencrypted — can be monitored. Modern DNS security options:

### DNS-over-HTTPS (DoH)

1. **Settings** → **Network & Internet** → **DNS server assignment**
2. Select **Automatic (DHCP)** or switch to:
   - **Cloudflare:** 1.1.1.1
   - **Quad9:** 9.9.9.9
   - **Google:** 8.8.8.8

3. Enable **Encrypted DNS queries**

### Pi-hole / local DNS (advanced)

Running local DNS filter on your network. See [Network Filtering](06-network-filtering.md) for details.

## VPN considerations

VPNs encrypt network traffic but **don't address other security issues**:

- Only use VPNs for **untrusted networks** (public WiFi)
- Choose **reputable providers** (avoid free VPNs)
- Don't use VPN to bypass malware protection
- VPN is NOT a substitute for antivirus/firewall

**Next:** [Application & Software Security](06-applications-security.md)
