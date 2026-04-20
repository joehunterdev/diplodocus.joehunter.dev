# Linking Between Pages

Diplodocus resolves four kinds of links. All use standard markdown syntax —
nothing proprietary.

## 1. Page within the same project

Use a relative path to the other `.md` file:

```markdown
[Installation](02-installation.md)
[Folder structure](03-folder-structure.md)
```

Renders:

- [Installation](02-installation.md)
- [Folder structure](03-folder-structure.md)

## 2. Anchor within the current page

Every heading gets an auto-generated ID based on its text. Link to it with
`#slug`:

```markdown
[Jump to best practices](#best-practices)
```

The slug is the heading text, lowercased, with spaces → hyphens:

| Heading | Anchor |
|---|---|
| `## Best practices` | `#best-practices` |
| `### The Three Rules` | `#the-three-rules` |
| `## 5 Minute Setup` | `#5-minute-setup` |

### Best practices

Anchor links work inside the current page and from any other page:

```markdown
[See best practices](06-linking-between-pages.md#best-practices)
```

## 3. Page in a different project

Go up one level, then into the sibling project:

```markdown
[API overview](../my-api-spaces/01-overview.md)
[User guide intro](../user-guide/01-introduction.md)
```

## 4. External links

Absolute URLs are passed through unchanged and open in the same tab by
default:

```markdown
[Parsedown on GitHub](https://github.com/erusev/parsedown)
[Mintlify](https://mintlify.com)
```

- [Parsedown on GitHub](https://github.com/erusev/parsedown)
- [Mintlify](https://mintlify.com)

To force an external link to open in a new tab, fall back to raw HTML:

```html
<a href="https://mintlify.com" target="_blank" rel="noopener">Mintlify</a>
```

## Link to an attachment

Attachments are just files — link to them like any other path:

```markdown
[Download the spec](attachments/05b-sample.pdf)
[View the diagram](attachments/03a-folder-tree.png)
```

## Link to a specific anchor on another page

Combine paths:

```markdown
[Read about Rule 2](03-folder-structure.md#rule-2-files-in-a-project-are-pages)
```

## Broken link detection

The Diplodocus linter (`php cli.php lint`) checks for:

- [x] Links to `.md` files that don't exist
- [x] Links to attachments that don't exist
- [x] Anchor links whose target heading is missing
- [ ] External links (intentionally not checked — too noisy)

Run the linter before publishing:

```bash
php cli.php lint
```

## What Diplodocus does NOT do with links

- **No URL rewriting** — `02-installation.md` stays `02-installation.md`
  in the rendered HTML. This preserves the GitHub-readable property: your
  files work even without Diplodocus.
- **No back-references** — if you want a "Back to top" link, write one.
- **No automatic cross-references** — mentioning a heading by name
  doesn't auto-link it. Use explicit anchor syntax.

## Next

- [Code blocks & highlighting](07-code-blocks-and-highlighting.md)
- [Tables & callouts](08-tables-and-callouts.md)
