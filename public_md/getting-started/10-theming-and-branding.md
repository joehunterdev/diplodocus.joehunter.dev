# Theming & Branding

Diplodocus's design is driven by one file: `assets/css/theme.css`. Edit it and
every page, sidebar, table, button, and code block on your site repaints.

![Theme picker preview](attachments/11a-theme-picker.svg)

## The theme contract

`theme.css` contains **only CSS custom properties**. No selectors, no
layout, no `!important`. Every colour, font, radius, and shadow used
elsewhere in the engine is a `var(--dc-*)` reference back to this file.

This means:

- To rebrand the whole site → edit one file
- To swap to dark mode → flip one attribute
- To integrate with an existing design system → map their variables into ours

## The variable groups

```css
:root {
  /* Brand */
  --dc-brand-primary:       #1e3a5f;
  --dc-brand-primary-hover: #2d4a6f;
  --dc-brand-accent:        #3b82f6;

  /* Surfaces */
  --dc-bg-base:     #f3f4f6;
  --dc-bg-surface:  #ffffff;
  --dc-bg-sidebar:  #ffffff;
  --dc-bg-code:     #0f172a;

  /* Text */
  --dc-text-primary:   #111827;
  --dc-text-secondary: #374151;
  --dc-text-muted:     #6b7280;

  /* Borders */
  --dc-border-subtle: #e5e7eb;
  --dc-border-strong: #9ca3af;

  /* Typography */
  --dc-font-body: 'Inter', system-ui, sans-serif;
  --dc-font-mono: 'JetBrains Mono', ui-monospace, monospace;

  /* Spacing */
  --dc-space-1: 0.25rem;
  --dc-space-4: 1rem;
  --dc-space-8: 2rem;

  /* Radii */
  --dc-radius-sm: 0.25rem;
  --dc-radius-md: 0.5rem;
  --dc-radius-lg: 0.75rem;

  /* Shadows */
  --dc-shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
  --dc-shadow-md: 0 4px 12px rgba(0,0,0,0.15);

  /* Syntax highlighting */
  --dc-hl-keyword: #ff7b72;
  --dc-hl-string:  #79c0ff;

  /* Tables */
  --dc-table-header-bg:   var(--dc-brand-primary);
  --dc-table-header-text: #ffffff;
}
```

See [the full theme.css](../assets/css/theme.css) for the complete list.

## Changing your brand colour

Easiest possible rebrand — change 3 lines:

```diff
  :root {
-   --dc-brand-primary:       #1e3a5f;
-   --dc-brand-primary-hover: #2d4a6f;
-   --dc-brand-accent:        #3b82f6;
+   --dc-brand-primary:       #10b981;  /* emerald */
+   --dc-brand-primary-hover: #059669;
+   --dc-brand-accent:        #14b8a6;  /* teal */
  }
```

That cascade hits: sidebar active states, link colours, table headers,
button backgrounds, attachment gallery hover states, and more.

## Dark mode

Diplodocus ships with a dark-mode override block in the same file:

```css
:root[data-theme="dark"],
body[data-theme="dark"] {
  --dc-bg-base:       #0f172a;
  --dc-bg-surface:    #1e293b;
  --dc-text-primary:  #f8fafc;
  /* … */
}
```

Activate it with one line of JavaScript:

```js
document.documentElement.dataset.theme = 'dark';
```

Or respect the user's OS preference:

```js
if (matchMedia('(prefers-color-scheme: dark)').matches) {
  document.documentElement.dataset.theme = 'dark';
}
```

## Logo

Two ways to brand the sidebar header:

### Text wordmark (default)

Set `logo_url` to empty in `config.php` and the sidebar shows a coloured
square with the first letter of `app_name`, next to the full name.

### Custom logo

```php
// config.php
'logo_url' => 'assets/images/my-logo.svg',
```

Drop your image into `assets/images/` (you'll need to create the folder —
Diplodocus doesn't ship one) and point `logo_url` at it. SVG is recommended
so it scales cleanly with zoom.

## Adding an override stylesheet

If you want to add rules without editing `diplodocus.css`, append your own
file to the `stylesheets` config after `diplodocus.css`:

```php
'stylesheets' => [
    'assets/css/theme.css',
    'assets/css/tailwind.min.css',
    'assets/css/highlight-dark.min.css',
    'assets/css/diplodocus.css',
    'assets/css/my-overrides.css',  // ← yours
],
```

## Typography swap

To use a different font, add the font import to `diplodocus.css` (or your
override file), then change the variable:

```css
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono');

:root {
  --dc-font-body: 'Inter', sans-serif;
  --dc-font-mono: 'JetBrains Mono', monospace;
}
```

## Verify you didn't hardcode anything

Before calling your theme "done", grep for leftover hex values:

```bash
# From project root — should return empty
grep -E '#[0-9a-fA-F]{3,8}' assets/css/diplodocus.css
```

If it prints anything, you've introduced a hardcoded colour that won't
respond to theme changes. Move it into `theme.css` as a new variable.

## Next

- [Deploying](11-deploying.md)
