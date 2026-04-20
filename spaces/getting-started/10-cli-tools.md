# CLI Tools

Diplodocus ships with a small CLI for validating your documentation before
publishing. Run it from the project root.

```bash
php cli.php help
```

## The three commands

### `scan-security`

Scans every `.md` file for sensitive data and prints a report.

```bash
php cli.php scan-security
```

What it catches:

- [x] API keys (Stripe, AWS, GitHub, GitLab, generic `sk_`, `pk_`, `ghp_` prefixes)
- [x] Passwords and credentials (`password: ...`, `pwd=...`)
- [x] Private keys (RSA, EC, PEM headers)
- [x] OAuth tokens
- [x] Credit card numbers (basic Luhn check)
- [x] Non-placeholder email addresses

Placeholder patterns (`user@example.com`, `xxx-xxx-xxx`) are ignored.

### `lint`

Validates markdown formatting:

```bash
php cli.php lint
```

Checks applied:

| Check | Severity |
|---|---|
| H1 exists and is the first heading | Error |
| Heading levels don't skip (h1 → h3 without h2) | Warning |
| Fenced code blocks are closed | Error |
| Links to `.md` files resolve on disk | Warning |
| Anchor targets exist in the linked page | Warning |
| Line length ≤ 120 chars | Info |
| Trailing whitespace | Info |

### `check-all`

Runs both security scan and lint in one command:

```bash
php cli.php check-all
```

## Sample output

```
🔍 Security Scan Starting...
==================================================

✅ No security issues found in 14 files.

📝 Markdown Lint Starting...
==================================================

getting-started/05-attachments-and-images.md:
  Warning (line 23): Link to 'attachments/05a-gallery-demo.png' but file not found

✓ Lint complete.
  Errors:   0
  Warnings: 1
```

## Exit codes

| Code | Meaning |
|---|---|
| 0 | All checks passed |
| 1 | Lint warnings (non-blocking) |
| 2 | Security issues found |
| 3 | CLI usage error |

## CI integration

### GitHub Actions

```yaml
name: Docs validation
on: [push, pull_request]
jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: php cli.php check-all
```

### GitLab CI

```yaml
validate_spaces:
  image: php:8.2-cli
  script:
    - php cli.php check-all
```

### Pre-commit hook

```bash
#!/usr/bin/env bash
# .git/hooks/pre-commit
php cli.php scan-security || {
  echo "Security issues found. Fix before committing."
  exit 1
}
```

## Running against a single project

Pass a path to scope the check to one project:

```bash
php cli.php lint getting-started/
php cli.php scan-security my-api-spaces/
```

## Common false positives

The security scanner is regex-based and errs on the side of alerting. If
you're documenting an API that uses a specific token format, you can:

1. Use a placeholder: `sk_test_xxxxxxxxxx`
2. Exclude the file via a config rule (coming in a future release)
3. Accept the warning and carry on

## Next

- [Theming & branding](11-theming-and-branding.md)
- [Deploying](12-deploying.md)
