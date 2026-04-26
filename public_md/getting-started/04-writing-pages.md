# Writing Pages

Diplodocus uses **GitHub-flavoured markdown** via the Parsedown parser. If it
renders on GitHub, it renders in Diplodocus.

This page is a live cheat sheet — everything you see rendered below
corresponds to a markdown primitive you can use in your own pages.

## Headings

```markdown
# Heading 1
## Heading 2
### Heading 3
#### Heading 4
##### Heading 5
###### Heading 6
```

`H1` is your page title (only one per page). `H2` and `H3` are what the
right-hand table of contents shows.

## Paragraphs and emphasis

This is a regular paragraph. You can use *italic*, **bold**, ***both***,
`inline code`, and ~~strikethrough~~ right inside text.

Hard line breaks need two trailing spaces at the end of a line.
Otherwise consecutive lines collapse into one paragraph.

## Lists

### Unordered

- Apples
- Oranges
  - Blood oranges
  - Navel oranges
- Bananas

### Ordered

1. Preheat the oven to 200°C
2. Mix the dry ingredients
3. Add the wet ingredients
4. Bake for 30 minutes

### Task lists

- [x] Write the installation page
- [x] Write the folder structure page
- [ ] Record a demo video
- [ ] Translate to Spanish

## Links

- Internal link: [Folder structure](03-folder-structure.md)
- Anchor link: [jump to lists](#lists)
- External link: [Parsedown on GitHub](https://github.com/erusev/parsedown)

## Images

```markdown
![Alt text](attachments/03a-folder-tree.png)
```

See [Attachments & Images](05-attachments-and-images.md) for the full story.

## Blockquotes

> A quote makes a statement. A blockquote makes it official.

> Multiple lines
> extend the
> blockquote.

> **Warning** — the first bold word in a blockquote acts as a callout label.

## Horizontal rules

Three or more dashes on their own line:

---

...produces a horizontal rule.

## Inline code and code blocks

`inline code` uses single backticks. Fenced code blocks use triple backticks
with an optional language hint:

````markdown
```php
echo "Hello, Diplodocus";
```
````

See [Code Blocks & Highlighting](07-code-blocks-and-highlighting.md) for
the language list.

## Tables

```markdown
| Column A | Column B | Column C |
|:---------|:--------:|---------:|
| left     | centre   | right    |
| aligned  | aligned  | aligned  |
```

Renders as:

| Column A | Column B | Column C |
|:---------|:--------:|---------:|
| left     | centre   | right    |
| aligned  | aligned  | aligned  |

## HTML passthrough

Raw HTML works for the things markdown can't express. Use sparingly:

```html
<details>
  <summary>Click to expand</summary>
  Hidden content.
</details>
```

<details>
  <summary>Click to expand</summary>
  Hidden content.
</details>

## Next

- [Attachments & images](05-attachments-and-images.md)
- [Linking between pages](06-linking-between-pages.md)
