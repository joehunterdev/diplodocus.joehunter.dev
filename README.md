# Diplodocus

**Markdown-first, zero-build documentation site.**

Drop a folder of `.md` files into `public/`, point your browser at
`index.php`, and you have a clean, searchable spaces site. No Node, no, composer
database, no build step. Just PHP and markdown.

---

## Quick start

```bash
git clone https://github.com/joehunter/diplodocus.git
cd diplodocus
php -S localhost:8000
```

Open `http://localhost:8000` — you'll see the built-in `getting-started/`
guide, which demonstrates every feature.

## How it works

```
diplodocus/
├── index.php            ← thin bootstrap (< 15 lines)
├── src/                 ← Diplodocus\ OOP engine
├── lib/                 ← vendored Parsedown + scanners
├── assets/css/
│   ├── theme.css        ← ONE file controls all colours/fonts/radii
│   └── diplodocus.css       ← structural CSS (uses var(--nv-*) only)
├── templates/           ← PHP templates
│
└── public/                ← your content lives here
    ├── getting-started/
    │   ├── 01-welcome.md
    │   ├── 02-installation.md
    │   └── attachments/
    └── my-other-project/
        └── 01-overview.md
```

**Rules:**

1. Each folder inside `public/` is a doc site (shown in the project picker).
2. Each `NN-slug.md` file inside a project is a page (sorted by `NN`).
3. Each project can have its own `attachments/` folder for images/PDFs.

That's the whole content model. Rename a file on disk, refresh the
browser, the sidebar updates.

## Features

- Automatic sidebar + table of contents
- Multi-project hosting
- Syntax highlighting (highlight.js, 190+ languages)
- Attachment gallery with image lightbox
- Dark mode
- Client-side search
- Security scanner (catches API keys, secrets in markdown)
- Markdown linter
- Zero-build — works on any PHP 8.0+ host

## Theming

Every colour, font, and radius lives in `assets/css/theme.css` as CSS
custom properties. Change your brand with three lines:

```diff
  :root {
-   --nv-brand-primary:       #1e3a5f;
-   --nv-brand-primary-hover: #2d4a6f;
+   --nv-brand-primary:       #10b981;
+   --nv-brand-primary-hover: #059669;
  }
```

The sidebar, links, table headers, and button backgrounds all repaint.
`diplodocus.css` contains zero hardcoded colours — every value flows through
a variable.

## CLI

```bash
php cli.php scan-security    # Find exposed secrets in your markdown
php cli.php lint             # Check markdown formatting
php cli.php check-all        # Run both
```

## Requirements

- PHP 8.0 or higher
- A web server (Apache/Nginx/Caddy) or the built-in `php -S`

## License

MIT. See [LICENSE](LICENSE).

## Credits

- [Parsedown](https://github.com/erusev/parsedown) — markdown parser
- [highlight.js](https://highlightjs.org/) — syntax highlighting
- [Tailwind CSS](https://tailwindcss.com/) — utility classes
- [Inter](https://rsms.me/inter/) — typeface
