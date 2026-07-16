# Deployment — From Git to Live

Diplodocus deployment is automatic. When you push to GitHub, the site updates within minutes. This page explains the flow and what happens behind the scenes.

## The Deployment Flow

```
1. You push to GitHub
   git push origin main

2. GitHub webhook triggers deployment
   (via GitHub Actions or direct hook)

3. Diplodocus server fetches latest changes
   git pull

4. New docs are immediately live
   https://diplodocus.joehunter.dev/my-project-docs/overview

5. Sitemap is regenerated
   https://diplodocus.joehunter.dev/sitemap.xml

6. Search engines crawl the new pages
   (may take 24-48 hours for indexing)
```

## What Deploys Automatically

When you push a commit to the main branch:

✅ **New documentation folders** in `public_md/`
✅ **Updated markdown files** (any page you edit)
✅ **New attachments** (images, PDFs, files in `attachments/`)
✅ **Configuration changes** (`.diplodocus.json`)

❌ **Private documentation** (in `private_md/`) — kept private, not indexed
❌ **Comments in `.md` files** — not visible in rendered output anyway

## Deployment Checklist

Before pushing, verify your docs:

- [ ] All `.md` files are properly formatted (valid markdown)
- [ ] Every `.md` file starts with a top-level `# H1` heading
- [ ] File naming follows the pattern (`01-`, `02-`, etc.)
- [ ] Images in `attachments/` are actually referenced in your markdown
- [ ] Links to other guides use the correct URLs (`/space-slug/page-slug`)
- [ ] `.diplodocus.json` has valid JSON (no trailing commas, etc.)
- [ ] No secrets, passwords, or API keys in the content

## Pushing Your Docs

### First-Time Push

```bash
cd ~/Projects/diplodocus-workspace

# Stage your new folder
git add public_md/my-project-docs/

# Commit with a clear message
git commit -m "Add documentation for my-project-docs"

# Push to the main branch
git push origin main
```

After a few minutes, visit:

```
https://diplodocus.joehunter.dev/my-project-docs/
```

You should see your new documentation space live.

### Updating Docs Later

```bash
cd ~/Projects/diplodocus-workspace

# Edit a page locally
nano public_md/my-project-docs/01-overview.md

# Stage and commit
git add public_md/my-project-docs/01-overview.md
git commit -m "Update overview with better examples"

# Push
git push origin main
```

Changes appear within minutes.

## Viewing Live Docs

After deployment, your docs are accessible at:

```
https://diplodocus.joehunter.dev/{folder-slug}/{page-slug}

Examples:
https://diplodocus.joehunter.dev/my-project-docs/overview
https://diplodocus.joehunter.dev/my-project-docs/getting-started
https://diplodocus.joehunter.dev/my-project-docs/deployment
```

## Verifying Deployment

**Check if your docs are live:**

1. Visit the home page: https://diplodocus.joehunter.dev/
2. Look for your space in the sidebar
3. Click through to verify all pages render correctly

**Check the sitemap:**

https://diplodocus.joehunter.dev/sitemap.xml

Search for your space slug (e.g., `my-project-docs`) to confirm it's indexed.

**Search engine indexing:**

It can take 24-48 hours for Google to index your new pages. You can speed this up by:

1. Going to Google Search Console: https://search.google.com/search-console
2. Adding your site (if not already added)
3. Submitting the sitemap: https://diplodocus.joehunter.dev/sitemap.xml
4. Manually requesting indexing for individual URLs

## Rollback / Removing Docs

If you accidentally pushed something that should be removed:

```bash
# Delete the folder locally
rm -rf public_md/my-project-docs/

# Commit the deletion
git commit -am "Remove my-project-docs (accidental push)"

# Push
git push origin main
```

The space will be removed from the site within minutes.

## Deployment Troubleshooting

### My docs don't appear after I pushed

**Possible causes:**

1. **Folder name is wrong** — Make sure it's in `public_md/`, not `docs/` or elsewhere
2. **No `.md` files in the folder** — Diplodocus scans for `*.md` files
3. **File names don't follow the pattern** — Use `01-name.md`, not `name.md`
4. **Missing `# H1` heading** — Every `.md` file needs a top-level heading
5. **Git push failed silently** — Check the output: `git push origin main` should show success

### Sitemap doesn't include my pages

The sitemap regenerates on deployment. If your pages aren't listed:

1. Verify they appear on the live site first
2. Wait a few minutes and check again: https://diplodocus.joehunter.dev/sitemap.xml
3. If still missing, check the page for errors in the browser console

### Pages render with formatting errors

Common issues:

- **Missing blank lines before lists** — Markdown requires a blank line before `- item`
- **Code blocks not rendering** — Use triple backticks: ` ``` `
- **Links broken** — Check the URL slug (use `/space/page`, not `/space/page.md`)

Try viewing the raw markdown locally in VSCode to spot syntax issues.

## Next Step

Now that you understand the full deployment flow, let's walk through a complete real-world example from start to finish.
