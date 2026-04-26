# Configuration

Diplodocus works with zero configuration. If you want to customise branding,
stylesheets, or validation rules, copy `config.example.php` to
`config.php` and edit it.

## Quick start

```bash
cp config.example.php config.php
```

`config.php` is loaded automatically if present. A second file,
`config.local.php`, is also loaded and overrides `config.php` — use it
for machine-specific settings you don't want to commit.

## Every option, explained

### `app_name`

The name shown in the browser tab, the sidebar header, and the breadcrumb
"home" link.

```php
'app_name' => 'My Project Docs',
```

**Default**: `'Diplodocus'`

### `logo_url`

Optional image URL for the sidebar header. Leave empty to get a text-only
wordmark using the `app_name`.

```php
'logo_url' => 'assets/images/my-logo.svg',  // local
'logo_url' => 'https://cdn.example.com/logo.png',  // external
```

**Default**: `''` (empty → text wordmark)

### `stylesheets`

The list of CSS files loaded in `<head>`, in order.

> **Important** — `theme.css` MUST come first. Its CSS custom properties
> feed every stylesheet after it. Moving it changes nothing visible;
> removing it breaks the whole design.

```php
'stylesheets' => [
    'assets/css/theme.css',          // ← variables, load first
    'assets/css/tailwind.min.css',   // ← utility classes
    'assets/css/highlight-dark.min.css',
    'assets/css/diplodocus.css',         // ← structural CSS
    'assets/css/my-overrides.css',   // ← your custom rules
],
```

### `scripts`

JavaScript files loaded at the end of `<body>`.

```php
'scripts' => [
    'assets/js/highlight.min.js',
    'assets/js/app.js',
    'assets/js/my-custom.js',
],
```

### `default_theme`

Which colour scheme to start in. User's browser preference
(`prefers-color-scheme: dark`) will override this if `enable_dark_mode` is
true.

```php
'default_theme' => 'light',  // or 'dark'
```

### `enable_dark_mode`

Whether the dark mode toggle appears in the UI. Set to false to force a
single colour scheme.

```php
'enable_dark_mode' => true,
```

### `excluded_dirs`

Folder names that should NOT appear as projects in the sidebar. The engine
folders are excluded automatically; add your own here.

```php
'excluded_dirs' => [
    'src', 'lib', 'assets', 'templates',
    '.spaces', '.vscode', '.backup', '.git', '.claude',
    'vendor', 'node_modules',
    'archive',        // ← your additions
    'drafts',
],
```

### `allowed_file_types`

Which file extensions the asset router is allowed to serve. Add new types
as needed.

```php
'allowed_file_types' => [
    'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp',
    'pdf', 'doc', 'docx',
    'json', 'xml', 'csv',
    'mp4', 'webm',    // ← enable video
],
```

### `block_on_security_issues`

If true, the validation modal blocks navigation until security issues
(API keys, credentials, etc.) are resolved. Set to false for development.

```php
'block_on_security_issues' => true,
```

### `block_on_lint_issues`

Same, but for markdown lint warnings (heading hierarchy, broken links).
Lint issues are usually less critical, so this defaults to false.

```php
'block_on_lint_issues' => false,
```

## Customising the theme palette

Most branding changes should happen in `assets/css/theme.css`, not in
`config.php`. Open the file and edit the custom properties:

```css
:root {
  --dc-brand-primary:       #1e3a5f;  /* ← your brand colour */
  --dc-brand-primary-hover: #2d4a6f;
  --dc-brand-accent:        #3b82f6;
  /* …everything else inherits from these */
}
```

See [Theming & Branding](10-theming-and-branding.md) for the full theme
reference.

## Environment-specific config

For settings that differ between local/staging/production, use
`config.local.php` (loaded after `config.php` and gitignored):

```php
// config.local.php — NEVER commit this file
<?php
return [
    'block_on_security_issues' => false,  // relax for dev
    'app_name' => 'Diplodocus (local)',
];
```

## Next

- [Theming & branding](10-theming-and-branding.md)
