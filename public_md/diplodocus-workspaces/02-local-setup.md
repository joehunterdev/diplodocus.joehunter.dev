# Local Setup — Clone and Sparse Checkout

This page walks through setting up a local development environment where you have both your project and the Diplodocus guides you need, without cloning the entire Diplodocus repository.

## Prerequisites

- Git (tested with Git 2.25+)
- A local project folder (e.g., Laravel app, React frontend, etc.)
- A GitHub account (to fork/push if adding project docs)

## Step 1: Create a Workspace Folder

First, create a directory to hold both your project and the Diplodocus workspace:

```bash
mkdir ~/Projects
cd ~/Projects
```

## Step 2: Initialize Your Project

Create or clone your project into this directory:

```bash
# Create a new Laravel project
composer create-project laravel/laravel somenewproject

# OR clone an existing project
git clone https://github.com/yourusername/somenewproject.git
```

Your folder structure now looks like:

```
~/Projects/
└── somenewproject/
    ├── app/
    ├── routes/
    ├── composer.json
    └── ...
```

## Step 3: Clone Diplodocus with Sparse Checkout

Create a separate folder for the Diplodocus workspace:

```bash
# Clone with minimal data (blob:none filter)
git clone --filter=blob:none \
  https://github.com/joehunterdev/diplodocus.joehunter.dev.git \
  diplodocus-workspace

cd diplodocus-workspace

# Enable sparse checkout
git sparse-checkout init --cone

# Check out only the folders you need
git sparse-checkout set \
  public_md/laravel-full-stack-like-a-pro \
  public_md/getting-started
```

After sparse checkout, your workspace contains only the folders you specified:

```
diplodocus-workspace/
└── public_md/
    ├── laravel-full-stack-like-a-pro/
    │   ├── 01-overview.md
    │   ├── 02-database.md
    │   └── ...
    └── getting-started/
        ├── 01-welcome.md
        └── ...
```

## Step 4: Adding More Folders Later

As you work, you might want to add another guide or create your project docs:

```bash
cd ~/Projects/diplodocus-workspace

git sparse-checkout set \
  public_md/laravel-full-stack-like-a-pro \
  public_md/getting-started \
  public_md/somenewproject-docs
```

Git fetches the new folder without re-cloning everything.

## Step 5: Open Both in VSCode

In VSCode, open the `~/Projects` folder as a workspace:

```
File → Open Folder → ~/Projects
```

Now you have both projects visible:

```
Projects
├── somenewproject/          ← Your app (node_modules, vendor, etc.)
└── diplodocus-workspace/    ← Docs only (sparse, small)
    └── public_md/
        ├── laravel-full-stack-like-a-pro/
        └── somenewproject-docs/
```

Open `public_md/laravel-full-stack-like-a-pro/02-database.md` to reference the guide while you're working on your app.

## Viewing Docs Locally (Optional)

If you want to run Diplodocus locally to see your docs rendered:

```bash
cd ~/Projects/diplodocus-workspace
php -S localhost:8000 index.php

# Visit http://localhost:8000/laravel-full-stack-like-a-pro/overview
```

But this is optional — you can just read the `.md` files in VSCode.

## Keeping Your Workspace Updated

To pull new changes from the remote Diplodocus repository:

```bash
cd ~/Projects/diplodocus-workspace
git pull
```

Only the sparse-checked folders update. The rest of the repo stays lightweight.

## Troubleshooting

**Q: `sparse-checkout` command not recognized**

A: Upgrade Git to 2.25+:

```bash
git --version  # Check your version
# Then upgrade via your package manager or https://git-scm.com
```

**Q: I want to add a new guide folder, but `git sparse-checkout set` seems to remove old ones**

A: `set` replaces the list. To keep existing folders, list them again:

```bash
git sparse-checkout set \
  public_md/laravel-full-stack-like-a-pro \
  public_md/getting-started \
  public_md/new-folder
```

**Q: The sparse-checked folder is still large**

A: Make sure you cloned with `--filter=blob:none`. Also, `git gc --aggressive` can help:

```bash
git gc --aggressive
git prune
```

## Next Step

You now have a lightweight local setup. Next, learn how to structure your own project documentation folder.
