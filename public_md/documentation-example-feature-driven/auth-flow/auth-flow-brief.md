# Auth Flow — Brief

A second feature in the example project, scaffolded with only a brief.

Use this folder to see how the engine handles **partial features** —
features that don't yet have all three docs. The plan and implementation
files are intentionally missing here. The engine shows whichever docs
exist; nothing breaks.

## What it is

Single sign-on for the workspace, replacing the legacy session-cookie
flow.

## Why we need it

Compliance team flagged the legacy cookies for token-storage issues.
Replacing the flow is the cleanest fix.

## What success looks like

- Users authenticate once per device, not once per project
- No session-token state stored client-side
- Existing API tokens keep working during transition

## Open concerns

- Backwards compatibility for `?api_key=` query-param logins
- Mobile deep-link redirect handling

---

> **Note** Once you've thought it through, the next step is to write
> `auth-flow-plan.md` in this same folder. The engine will pick it up
> automatically.
