# Real-World Example — Complete Walkthrough

Let's walk through the entire workflow from zero to deployed documentation. We'll create documentation for a fictional `crm-system` project.

## Scenario

You're building a CRM (Customer Relationship Management) system. You want to:

1. Set up a local development environment with both the app and docs
2. Create project-specific documentation
3. Deploy it to the web
4. Share it with your team

## Step 1: Create the Project Structure

```bash
# Create a projects folder
mkdir -p ~/Projects
cd ~/Projects

# Create your CRM app (or clone an existing one)
mkdir crm-system
cd crm-system
git init

# Add some basic files (or use an existing Laravel/React/etc app)
echo "# CRM System" > README.md
mkdir src
echo "// Your code here" > src/index.js

git add .
git commit -m "Initial CRM project"

# Go back to Projects
cd ..
```

Now you have:

```
~/Projects/crm-system/  ← Your actual CRM application
```

## Step 2: Clone Diplodocus Workspace

```bash
cd ~/Projects

# Clone Diplodocus with sparse checkout
git clone --filter=blob:none \
  https://github.com/joehunterdev/diplodocus.joehunter.dev.git \
  diplodocus-workspace

cd diplodocus-workspace

# Enable sparse checkout
git sparse-checkout init --cone

# Check out guides you want to reference
git sparse-checkout set \
  public_md/laravel-full-stack-like-a-pro \
  public_md/getting-started
```

Now you have:

```
~/Projects/
├── crm-system/          ← Your app
└── diplodocus-workspace/ ← Docs repo (sparse)
    └── public_md/
        ├── laravel-full-stack-like-a-pro/
        └── getting-started/
```

## Step 3: Create CRM Documentation Folder

```bash
cd ~/Projects/diplodocus-workspace/public_md

# Create the CRM docs space
mkdir crm-system-docs
cd crm-system-docs

# Create the welcome page
cat > 01-overview.md << 'EOF'
# CRM System Documentation

Welcome to the CRM System documentation. This guide covers setup, architecture, and deployment.

## What is the CRM?

A Customer Relationship Management system for managing leads, contacts, and sales pipelines.

## Key Features

- Contact management
- Pipeline tracking
- Email integration
- Reporting and analytics

## Quick Navigation

- **Getting Started** — Set up the CRM locally
- **Architecture** — How the system is structured
- **API Reference** — Available endpoints
- **Deployment** — Ship to production
EOF

# Create the getting started page
cat > 02-getting-started.md << 'EOF'
# Getting Started

## Prerequisites

- Node 18+ (for the frontend) or PHP 8.1+ (for Laravel backend)
- PostgreSQL 14+
- Docker (optional, but recommended)

## Local Setup

1. Clone the CRM repository:
   ```bash
   git clone https://github.com/your-org/crm-system.git
   cd crm-system
   ```

2. Install dependencies:
   ```bash
   npm install
   # or
   composer install
   ```

3. Set up the database:
   ```bash
   docker compose up -d postgres
   npm run migrate
   ```

4. Start the development server:
   ```bash
   npm run dev
   ```

5. Visit http://localhost:3000

## Troubleshooting

If you get a database connection error, verify PostgreSQL is running:

```bash
docker ps | grep postgres
```
EOF

# Create the architecture page
cat > 03-architecture.md << 'EOF'
# Architecture

## System Overview

The CRM is built with:

- **Frontend:** React 18 + TypeScript
- **Backend:** Laravel 11 or Node.js Express
- **Database:** PostgreSQL
- **Cache:** Redis (optional)
- **Search:** Elasticsearch (optional)

## Key Components

```
┌─────────────────────────────────────┐
│  React Frontend (Web + Mobile)      │
│  ├─ Contact Management              │
│  ├─ Pipeline Tracker                │
│  └─ Reports Dashboard               │
└────────────┬────────────────────────┘
             │
┌────────────▼────────────────────────┐
│  REST API (Node.js / Laravel)       │
│  ├─ /contacts                       │
│  ├─ /pipelines                      │
│  ├─ /deals                          │
│  └─ /reports                        │
└────────────┬────────────────────────┘
             │
┌────────────▼────────────────────────┐
│  PostgreSQL Database                │
│  ├─ contacts table                  │
│  ├─ pipelines table                 │
│  └─ deals table                     │
└─────────────────────────────────────┘
```

## Data Models

- **Contact** — Customer information, email, phone
- **Pipeline** — Sales stages (Prospecting, Qualified, Negotiation, Closed)
- **Deal** — Opportunity with value, contact, timeline

See the database schema for full details.
EOF

# Create an attachments folder for images
mkdir attachments

# Add a simple diagram as an image (placeholder)
echo "[CRM Architecture Diagram]" > attachments/architecture-diagram.txt

# Create configuration file (flat-numbered is default, but let's be explicit)
cat > .diplodocus.json << 'EOF'
{
  "spec": "flat-numbered"
}
EOF
```

Your folder now looks like:

```
public_md/crm-system-docs/
├── .diplodocus.json
├── 01-overview.md
├── 02-getting-started.md
├── 03-architecture.md
└── attachments/
    └── architecture-diagram.txt
```

## Step 4: Preview Locally (Optional)

If you want to see how it renders before pushing:

```bash
cd ~/Projects/diplodocus-workspace

# Run Diplodocus locally
php -S localhost:8000 index.php

# Visit in your browser
# http://localhost:8000/crm-system-docs/overview
```

You should see your three pages rendered with a sidebar and navigation.

## Step 5: Commit and Push

```bash
cd ~/Projects/diplodocus-workspace

# Stage your new documentation folder
git add public_md/crm-system-docs/

# Commit with a descriptive message
git commit -m "Add CRM System documentation (overview, getting started, architecture)"

# Push to GitHub
git push origin main
```

## Step 6: Verify Deployment

After a few minutes:

1. Visit the home page: https://diplodocus.joehunter.dev/
2. Look for **CRM System Docs** in the sidebar
3. Click and verify all three pages render correctly

Or visit directly:

- https://diplodocus.joehunter.dev/crm-system-docs/overview
- https://diplodocus.joehunter.dev/crm-system-docs/getting-started
- https://diplodocus.joehunter.dev/crm-system-docs/architecture

## Step 7: Share with Your Team

Share the link with your team:

> Check out the CRM System documentation: https://diplodocus.joehunter.dev/crm-system-docs/

Your team can now reference the docs while working on the app.

## Step 8: Update the Docs

As your project evolves, update the docs:

```bash
cd ~/Projects/diplodocus-workspace/public_md/crm-system-docs

# Edit the overview
nano 01-overview.md

# Add a new page for API reference
cat > 04-api-reference.md << 'EOF'
# API Reference

## Contacts

### GET /api/contacts

Fetch all contacts.

```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  ]
}
```

### POST /api/contacts

Create a new contact.
EOF

# Commit and push
cd ~/Projects/diplodocus-workspace
git add public_md/crm-system-docs/
git commit -m "Add API reference and update overview"
git push origin main
```

Your new page appears on the site within minutes.

## Next Step

Check out the FAQ for common questions and troubleshooting tips.
