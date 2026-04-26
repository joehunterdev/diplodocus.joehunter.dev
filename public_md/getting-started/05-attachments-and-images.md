# Attachments & Images

Every project has its own `attachments/` folder for images, PDFs, CSVs,
JSON, and other binary content. This page shows how to use it.

## The basic pattern

```markdown
![Descriptive alt text](attachments/05a-gallery-demo.png)
```

Rendered:

![Gallery demo screenshot](attachments/05a-gallery-demo.png)

> The image above may show a broken-image icon if the placeholder PNG
> hasn't been filled in yet — that's fine, the path is what matters.

## Naming convention

Prefix each attachment with the page number it belongs to, and a letter
for ordering within that page:

```
attachments/
├── 05a-gallery-demo.svg      ← first image on page 05
├── 05b-sample.pdf            ← PDF referenced by page 05
└── 05c-data-sample.csv       ← CSV referenced by page 05
```

Why bother? When you delete page `05-*.md`, you can grep for `05*` in
attachments/ and prune cleanly.

## Downloadable files

Markdown links work for any file type. Diplodocus serves them directly with
the right MIME type:

```markdown
[Download the full spec (PDF)](attachments/05b-sample.pdf)
[Download the sample dataset (CSV)](attachments/05c-data-sample.csv)
[Download the raw config (JSON)](attachments/05d-config.json)
```

## Supported file types

Diplodocus serves these file types directly (configured in
`src/Config.php:allowed_file_types`):

| Category | Extensions |
|---|---|
| Images | `png`, `jpg`, `jpeg`, `gif`, `svg`, `webp` |
| Documents | `pdf`, `doc`, `docx` |
| Data | `json`, `xml`, `csv` |
| Web | `html`, `htm`, `css`, `js` |

To allow additional file types, add them to `allowed_file_types` in your
`config.php`.

## The attachment gallery

When a project has an `attachments/` folder, Diplodocus automatically builds
an **attachment gallery** — a sidebar/lightbox view that lets users
browse every image and file without needing to know the path.

Features:

- **Image lightbox** — click any image to view it full-screen
- **Tabbed browsing** — images, documents, and data each get their own tab
- **Data preview** — CSV and JSON files are rendered as tables/trees inline
- **Download buttons** — one click to save any file

## Large binary files

Diplodocus doesn't copy or process your attachments — it serves them directly
from disk. That means:

- No size limit (beyond what your web server allows)
- No conversion or optimisation — the file you commit is the file that's served
- `.gitignore` patterns for `*.png`, `*.pdf`, etc. work as expected

## Images in tables

You can embed images inside table cells:

| Diagram | Description |
|---|---|
| ![Context](attachments/05a-gallery-demo.png) | System context view |

## Best practices

- Name files descriptively (`03a-architecture.png`, not `img1.png`)
- Compress PNGs/JPGs before committing (Diplodocus doesn't do it for you)
- Keep an `attachments/README.md` noting large/reusable assets
- Don't embed images > 2MB — consider a linked download instead

## Next

- [Linking between pages](06-linking-between-pages.md)
- [Code blocks & highlighting](07-code-blocks-and-highlighting.md)
