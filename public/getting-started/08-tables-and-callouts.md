# Tables & Callouts

## Basic tables

```markdown
| Name | Type | Default |
|---|---|---|
| app_name | string | "Diplodocus" |
| logo_url | string | "" |
| default_theme | string | "light" |
```

Renders:

| Name | Type | Default |
|---|---|---|
| app_name | string | "Diplodocus" |
| logo_url | string | "" |
| default_theme | string | "light" |

## Column alignment

Use `:` on either side of the separator to align:

```markdown
| Left | Centre | Right |
|:-----|:------:|------:|
| a    | b      | c     |
```

| Left | Centre | Right |
|:-----|:------:|------:|
| a    | b      | c     |
| foo  | bar    | 123.45 |
| hello | world | 9.99 |

## Tables with rich content

Cells can contain inline markdown:

| Feature | Status | Notes |
|---|---|---|
| Sidebar | ✅ Done | Auto-built from folder contents |
| TOC | ✅ Done | Uses `##` and `###` only |
| Search | ⚠️ Client-side | Filters sidebar, not full-text yet |
| Dark mode | ✅ Done | `data-theme="dark"` on `<html>` |
| **Break change alert** | 🔴 Planned | Will warn on renames |

## Callouts

Markdown doesn't have a native callout syntax, so Diplodocus uses blockquotes
with bold-word labels:

> **Note** — A standard informational callout. Use for context, background,
> or extra details the reader might want.

> **Tip** — A suggestion or best practice. Use for guidance that's
> helpful but optional.

> **Warning** — Something the reader should pay attention to, but that
> won't break things immediately.

> **Danger** — A destructive action, security risk, or something that will
> break things if ignored.

> **Example** — A concrete illustration of the concept above.

The pattern: blockquote + leading `**Word**` + em dash + body.

## Multi-paragraph callouts

A single `>` prefix on every line keeps the paragraphs inside one callout:

> **Note** — the first paragraph of the callout.
>
> The second paragraph continues in the same block.
>
> ```php
> // You can even nest code blocks inside callouts.
> echo "hello";
> ```

## Nested tables and lists

Lists inside table cells work, though the rendering is cramped:

| Feature | Components |
|---|---|
| Sidebar | <ul><li>Project list</li><li>Page list</li><li>Search box</li></ul> |
| TOC | <ul><li>H2 scan</li><li>H3 scan</li><li>Scroll-spy</li></ul> |

For anything non-trivial, flatten the data into more rows instead.

## Large tables

Diplodocus doesn't paginate tables. For very wide tables, consider:

1. Splitting into multiple smaller tables
2. Linking out to a CSV in `attachments/` that the reader can download
3. Using HTML `<div style="overflow-x:auto">` to enable horizontal scroll

## Next

- [Configuration](09-configuration.md)
- [CLI tools](10-cli-tools.md)
