# FAQ — Sparse Checkout, Workspaces, and Deployments

## Sparse Checkout

### Q: Will `sparse-checkout` slow down my Git operations?

**A:** No, it actually speeds things up. You're only cloning the data for the folders you specify. A sparse clone of one or two guide folders is typically < 1 MB, versus 50+ MB for the entire repo.

### Q: Can I add more folders to my sparse checkout later?

**A:** Yes. Just run `git sparse-checkout set` again with the full list:

```bash
git sparse-checkout set \
  public_md/laravel-full-stack-like-a-pro \
  public_md/getting-started \
  public_md/crm-system-docs
```

Git fetches only the new data.

### Q: What if I want to clone the entire repo (not sparse)?

**A:** You can always do a full clone:

```bash
git clone https://github.com/joehunterdev/diplodocus.joehunter.dev.git my-full-copy
```

But sparse checkout is recommended to keep your local copy lightweight.

### Q: Do I need to use sparse checkout?

**A:** No, it's optional. You can clone the entire repo and manually ignore folders in VSCode. But sparse checkout is cleaner and faster.

## Workspaces

### Q: Can I have project docs outside the main Diplodocus repo?

**A:** Yes, but they won't be auto-deployed. You have two options:

1. **Add them to the Diplodocus repo** — Push to GitHub, they deploy automatically (recommended)
2. **Store them in your project repo** — Keep docs private, not indexed by Google

For shared, public documentation, use the Diplodocus repo.

### Q: Can I nest docs folders (e.g., `clients/my-client-docs/`)?

**A:** No, Diplodocus expects flat-numbered or feature-driven folders directly in `public_md/`. Create `my-client-docs/` at the top level, not in a subfolder.

### Q: Can multiple projects share the same docs folder?

**A:** Technically yes, but it's messy. Use separate folders for separate projects to keep the sidebar clean.

### Q: What's the difference between `public_md/` and `private_md/`?

**A:** 
- `public_md/` — Deployed, indexed by search engines, appears in sitemap
- `private_md/` — Never deployed, not indexed, uses `.gitignore` (kept local only)

Use `public_md/` for docs you want to share. Use `private_md/` for private notes.

## Deployment and Updates

### Q: How long does deployment take after I push?

**A:** Usually within 1-5 minutes. The server pulls the latest changes and regenerates the sitemap.

### Q: Do I need to do anything special to deploy?

**A:** No, just `git push`. Deployment is automatic.

### Q: What if I accidentally pushed something I shouldn't have?

**A:** Delete the folder and push again:

```bash
git rm -r public_md/bad-docs/
git commit -m "Remove bad-docs"
git push
```

The docs are removed within minutes.

### Q: Can I schedule deployments (e.g., deploy at a specific time)?

**A:** Not currently. Deployment happens on every push to `main`. If you want to stage changes, use a separate branch and merge to `main` when ready.

## Naming and Organization

### Q: What naming conventions should I follow?

**A:**
- Folder slug: lowercase with hyphens (`my-project-docs`, `client-acme`)
- File names: `NN-page-name.md` (two digits, lowercase, hyphens)
- No capital letters, spaces, or special characters

### Q: Can I rename a folder after I've pushed it?

**A:** Yes, but old URLs will break. Update any links that point to the old slug.

```bash
mv public_md/old-name public_md/new-name
git add public_md/
git commit -m "Rename docs folder"
git push
```

### Q: Should I create one big folder for all docs or split by feature?

**A:** Split by feature if docs are large (>10 pages). Examples:

- `crm-system-docs/` — All docs for the CRM in one space
- `api-reference/` — Separate space for API docs
- `deployment-guide/` — Separate space for deployment

One space per project is usually the right balance.

## Content and Markdown

### Q: Can I use HTML in Markdown files?

**A:** Yes, but sparingly. Diplodocus supports inline HTML in `.md` files. Avoid large HTML blocks — keep docs mostly Markdown for portability.

### Q: Can I use external images (from URLs)?

**A:** Yes:

```markdown
![Alt text](https://example.com/image.png)
```

But it's better to store images locally in `attachments/`:

```markdown
![Alt text](attachments/image.png)
```

Local images load faster and don't break if the external URL goes down.

### Q: What if my markdown file has a syntax error?

**A:** Diplodocus tries to render it anyway, but it may look broken. Check for:

- Missing blank lines before lists
- Unclosed code blocks
- Mismatched brackets or parentheses

Validate your markdown in VSCode (install a Markdown linter) before pushing.

### Q: Can I use footnotes or references?

**A:** Diplodocus uses the CommonMark standard. Footnotes aren't standard Markdown, but you can work around it:

```markdown
Here's some text[^1].

[^1]: This is a footnote.
```

Test locally to verify it renders correctly.

## Search and Discoverability

### Q: How long does it take for Google to index my new docs?

**A:** Typically 24-48 hours. You can speed this up:

1. Go to Google Search Console
2. Add the sitemap: https://diplodocus.joehunter.dev/sitemap.xml
3. Manually request indexing for specific URLs

### Q: Why don't my docs appear in Google search?

**A:** Common reasons:

1. **Thin content** — Pages with < 300 words often aren't indexed
2. **No external backlinks** — Google needs external sites linking to you
3. **Recently published** — Wait 48 hours and check again
4. **Not submitted to GSC** — Add the sitemap in Google Search Console

Longer, original content with external backlinks ranks better.

### Q: Can I see which pages are indexed?

**A:** Yes, use Google Search Console:

1. Visit https://search.google.com/search-console
2. Add your domain
3. Check the **Coverage** report

## Troubleshooting

### Q: My page renders as blank or with an error

**A:** Possible causes:

1. **No `# H1` heading** — Every page needs a top-level heading
2. **Syntax error in `.md` or `.json`** — Check for typos
3. **Deployment in progress** — Wait a few minutes and refresh

### Q: Git says "sparse-checkout" command not found

**A:** You need Git 2.25+. Upgrade Git:

```bash
git --version
# If < 2.25, upgrade via https://git-scm.com
```

### Q: I pushed but my docs don't appear

**A:**

1. Verify the folder is in `public_md/`, not elsewhere
2. Verify all `.md` files are properly named (`NN-name.md`)
3. Verify the first line of each `.md` file is `# H1 Heading`
4. Wait a few minutes and refresh the browser (Ctrl+Shift+R for hard refresh)

If still missing, check the Git push output for errors.

## More Questions?

Not covered here? Open an issue or discussion on GitHub:

https://github.com/joehunterdev/diplodocus.joehunter.dev

Or reference the other guides in this workspace for detailed information.
