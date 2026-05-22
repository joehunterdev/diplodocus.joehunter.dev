# Getting Started

Before diving into specific hardening techniques, let's establish a baseline understanding of what security hardening means and how to approach it systematically.

## What is hardening?

Hardening is the process of reducing a system's vulnerability surface by:
- Disabling unnecessary services and features
- Configuring restrictive access controls
- Applying security settings and policies
- Removing or securing default configurations

It's about **defense in depth** — layering multiple security controls so that if one fails, others remain in place.

## Defense-in-depth strategy

Think of security like an onion with multiple layers:

1. **Physical & Device Security** — encryption, BIOS/firmware security
2. **Operating System** — patching, account controls, UAC
3. **Network** — firewall, segmentation
4. **Applications** — permissions, sandboxing
5. **User Behavior** — awareness, safe practices

Each layer works independently; compromising one doesn't automatically compromise the system.

## Our approach

This guide progresses from:
- **Foundation** — essential settings everyone should enable
- **Standard** — recommended configurations for typical users
- **Advanced** — additional controls for high-security needs

You don't need to implement everything — choose what matches your threat model and use case.

## Important notes

- **Always backup** before making system changes
- **Test in a VM first** if possible (virtual machine)
- **Document your changes** so you remember what you modified
- **Re-evaluate periodically** as threats and Windows updates evolve

**Next:** [System Update & Patch Management](03-updates-and-patching.md)
