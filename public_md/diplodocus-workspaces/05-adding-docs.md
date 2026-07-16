# Adding Your Docs — Contribution Workflow

This page walks through adding your project's documentation folder to the Diplodocus repository and making it available on the deployed site.

## Option 1: In Your Own Fork (Recommended for Shared Repos)

If Diplodocus is a shared documentation repository for your team or organization:

### Step 1: Fork the Repository

On GitHub, fork `https://github.com/joehunterdev/diplodocus.joehunter.dev.git` to your own account or organization.

### Step 2: Clone Your Fork Locally

```bash
cd ~/Projects/diplodocus-workspace
cd ..  # Go back to Projects/

git clone https://github.com/YOUR-ORG/diplodocus.joehunter.dev.git \
  diplodocus-workspace-personal

cd diplodocus-workspace-personal
git sparse-checkout init --cone
git sparse-checkout set public_md
```

### Step 3: Create Your Project Folder

```bash
mkdir -p public_md/my-project-docs
cd public_md/my-project-docs

cat > 01-overview.md << 'EOF'
# My Project Docs

This is documentation for my project.
EOF

mkdir attachments
```

### Step 4: Commit and Push

```bash
git add public_md/my-project-docs/
git commit -m "Add documentation for my-project-docs"
git push origin main
```

### Step 5: Open a Pull Request

On GitHub, create a PR from your fork to the main repository. The maintainer reviews and merges it.

```
Title: Add my-project-docs space
Description: Project-specific documentation for My Project

- 01-overview.md: Project overview and purpose
```

Once merged, your docs appear on the deployed site at `/my-project-docs/`.

## Option 2: Direct Contribution (For Team Members)

If you have push access to the main Diplodocus repository:

### Step 1: Clone the Repository

```bash
cd ~/Projects
git clone https://github.com/joehunterdev/diplodocus.joehunter.dev.git \
  diplodocus-workspace

cd diplodocus-workspace
```

### Step 2: Create Your Project Folder

```bash
mkdir -p public_md/my-project-docs
cd public_md/my-project-docs

cat > 01-overview.md << 'EOF'
# My Project Docs

Documentation for My Project.
EOF

mkdir attachments
```

### Step 3: Add More Pages

```bash
cat > 02-getting-started.md << 'EOF'
# Getting Started

Steps to get the project running locally.

## Prerequisites

- Node 18+
- Docker
- Git

## Setup

1. Clone the repo
2. Run `npm install`
3. Start Docker: `docker compose up`
EOF
```

### Step 4: Commit and Push

```bash
git add public_md/my-project-docs/
git commit -m "Add documentation for my-project-docs"
git push origin main
```

Your docs appear on the site within minutes (deployment is automatic on push).

## Naming Your Documentation Folder

Use a clear, URL-friendly slug:

| Good | Bad |
|------|-----|
| `my-project-docs` | `My-Project-Docs` (capitalized) |
| `client-acme-corp` | `acme` (too vague) |
| `feature-auth-system` | `new-feature-123` (not descriptive) |
| `laravel-13-guide` | `guide_laravel_v13` (underscores) |

**Rules:**
- Lowercase only
- Hyphens for word separation
- No special characters or spaces
- Max 50 characters

## File Naming

Inside your folder:

```
public_md/my-project-docs/
├── .diplodocus.json          ← Optional config
├── 01-overview.md            ← Flat-numbered (default)
├── 02-setup.md
├── 03-deployment.md
└── attachments/
    ├── screenshot.png
    └── architecture.pdf
```

**Rules:**
- Start with `NN-` (two digits: `01`, `02`, etc.)
- Lowercase, hyphens for words
- `.md` extension

## What Should You Document?

Good candidates for project-specific docs:

- **Setup & Installation** — Local development environment
- **Architecture** — How the system is structured
- **Configuration** — Environment variables, deployment settings
- **API Reference** — Endpoints, request/response formats
- **Database Schema** — Tables, relationships, migrations
- **Deployment** — How to ship to production
- **Troubleshooting** — Common issues and fixes
- **Contributing** — Guidelines for team members

Avoid:

- Duplicating content from shared guides (link instead)
- Proprietary secrets or credentials
- Personal notes unrelated to the project

## Referencing Other Guides

Link to shared guides within your docs:

```markdown
# Setup

For general Laravel setup, see the [Laravel 13 Guide](/laravel-full-stack-like-a-pro/setup).

For database design patterns, see the [Windows 10 Hardening](/windows-10-hardening/) guide.
```

Markdown links work across the site:
- `/space-slug/page-slug` — links to another page

## Adding Images and Files

Place images and downloads in an `attachments/` folder:

```
public_md/my-project-docs/
├── 01-overview.md
├── attachments/
│   ├── architecture.png
│   ├── setup-video.mp4
│   └── config-template.env
└── ...
```

Reference them in your markdown:

```markdown
## Architecture Diagram

![System diagram](attachments/architecture.png)

## Download Config Template

[Download config template](attachments/config-template.env)
```

Diplodocus automatically serves these files.

## Next Step

Learn about the deployment process and how your docs go live on the web.
