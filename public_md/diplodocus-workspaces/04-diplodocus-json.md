# Configuration — .diplodocus.json

`.diplodocus.json` is an optional file that configures how Diplodocus renders your documentation space. If it doesn't exist, sensible defaults apply.

## When Do You Need It?

**You need `.diplodocus.json` if you want to:**
- Use feature-driven structure (instead of flat-numbered)
- Set custom sidebar behavior (future feature)
- Specify metadata about your space (future feature)

**You do NOT need it if:**
- Using flat-numbered structure (the default)
- Fine with default rendering

## Basic Configuration

Create a `.diplodocus.json` file in the root of your space folder:

```bash
public_md/my-laravel-app-docs/.diplodocus.json
```

## Example: Feature-Driven Spec

To switch from flat-numbered to feature-driven:

```json
{
  "spec": "feature-driven"
}
```

Your folder structure then becomes:

```
public_md/my-laravel-app-docs/
├── .diplodocus.json
├── authentication/
│   ├── 01-overview.md
│   ├── 02-setup.md
│   └── 03-permissions.md
├── database/
│   ├── 01-schema.md
│   └── 02-migrations.md
└── attachments/
    └── schema-diagram.png
```

Diplodocus recognizes the feature folders and renders them as collapsible groups in the sidebar.

## Example: Flat-Numbered Spec (Explicit)

To be explicit about using flat-numbered (though it's the default):

```json
{
  "spec": "flat-numbered"
}
```

Your structure:

```
public_md/my-laravel-app-docs/
├── .diplodocus.json
├── 01-overview.md
├── 02-architecture.md
├── 03-setup.md
├── 04-deployment.md
└── attachments/
```

## Reference

### `spec` Key

| Value | Behavior |
|-------|----------|
| `"flat-numbered"` | Files like `01-name.md`, `02-name.md` render in order. Default if `.diplodocus.json` is missing. |
| `"feature-driven"` | Folders organize features; each feature has numbered pages inside. Use for large, modular projects. |

## Future Extensions

As Diplodocus evolves, `.diplodocus.json` may support:

```json
{
  "spec": "feature-driven",
  "sidebar_collapse": true,
  "homepage": "01-overview.md",
  "theme": "dark"
}
```

These are planned but not yet implemented.

## Creating Your Configuration File

For a feature-driven space:

```bash
cd public_md/my-laravel-app-docs
cat > .diplodocus.json << 'EOF'
{
  "spec": "feature-driven"
}
EOF
```

For a flat-numbered space (or to be explicit):

```bash
cat > .diplodocus.json << 'EOF'
{
  "spec": "flat-numbered"
}
EOF
```

## Deployment

Commit `.diplodocus.json` to Git just like any other file:

```bash
git add .diplodocus.json
git commit -m "Add configuration for feature-driven structure"
git push
```

Diplodocus picks it up on the next deployment.

## Next Step

Now that you understand structure and configuration, learn how to add your docs to the Diplodocus repository and deploy them.
