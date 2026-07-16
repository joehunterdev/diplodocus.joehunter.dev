# Create the Project

The scaffold is stock Laravel + Breeze, then a small, fixed set of packages layered on top. Nothing here is app-specific — it's the same starting point every time.

## 1. Scaffold Laravel + Breeze

```bash
laravel new my-app
cd my-app

# Breeze with the React + Inertia + TypeScript stack
composer require laravel/breeze --dev
php artisan breeze:install react --typescript
```

`breeze:install react --typescript` gives you Inertia v2, `@inertiajs/react`, a Vite + React + TS frontend, Tailwind, and the full auth flow (login, register, password reset, email verification, profile) as `.tsx` pages under `resources/js/Pages/Auth`.

> **Tip** — Add SSR later with `--ssr` if you need it. It's off by default here to keep shared-hosting deploys simple.

## 2. Add the core packages

**Composer (runtime):**

```bash
composer require inertiajs/inertia-laravel tightenco/ziggy \
  spatie/laravel-data spatie/laravel-typescript-transformer
```

**Composer (dev / tooling):**

```bash
composer require --dev laravel/boost laravel/pail laravel/pint
```

**npm (frontend + linting):**

```bash
npm install
npm install -D eslint prettier \
  eslint-config-airbnb eslint-config-airbnb-typescript \
  @typescript-eslint/eslint-parser @typescript-eslint/eslint-plugin \
  eslint-plugin-react eslint-plugin-react-hooks eslint-plugin-jsx-a11y \
  eslint-plugin-import eslint-config-prettier
```

| Package | Role |
|---|---|
| `inertiajs/inertia-laravel` + `@inertiajs/react` | Server-driven SPA transport |
| `tightenco/ziggy` | Use named Laravel routes in JS (`route('name')`) |
| `spatie/laravel-data` | DTOs — the typed Inertia payload |
| `spatie/laravel-typescript-transformer` | Generates `.d.ts` from those DTOs |
| `laravel/boost` | MCP server + AI guidelines for coding agents |
| `laravel/pail` | Tail application logs from the terminal |
| `laravel/pint` | PHP code style (Laravel preset) |
| ESLint (Airbnb) + Prettier | JS/TS quality gate — see [Frontend](04-frontend-react-tailwind.md) |

Tailwind v4 setup and the Airbnb ESLint config are covered on [Frontend — React & Tailwind](04-frontend-react-tailwind.md). DTO wiring is on [DTOs & TypeScript](03-dtos-and-typescript.md). Boost is on [AI-driven dev docs](05-ai-driven-dev-docs.md).

## 3. Directory layout

Two conventions do most of the work: **backend layering** in `app/`, and a **casing split** in `resources/js/`.

```
app/
├── Console/Commands/       # RemoteCmds lives here (see page 07)
├── Data/                   # Spatie DTOs (#[TypeScript])
├── Enums/                  # PHP enums (also transformed to TS)
├── Http/
│   ├── Controllers/        # Thin orchestrators
│   └── Requests/           # FormRequest per write action
├── Models/                 # Lightweight Eloquent models
├── Providers/              # incl. TypeScriptTransformerServiceProvider
└── Services/               # Business logic

resources/js/
├── app.tsx                 # Inertia entry
├── bootstrap.ts            # axios + Ziggy
├── Pages/                  # Breeze + app pages   ← Capitalized (scaffold)
├── Components/             # Breeze UI primitives  ← Capitalized (scaffold)
├── Layouts/                # Auth/Guest layouts    ← Capitalized (scaffold)
├── features/               # App feature modules   ← lowercase (your code)
├── shared/                 # Cross-feature code    ← lowercase (your code)
└── types/                  # generated.d.ts, index.d.ts, global.d.ts
```

> **Tip** — The **capitalized `Pages`/`Components`/`Layouts`** are Breeze scaffolding — leave them recognizable. **Lowercase `features/` and `shared/`** are where your application code goes. The casing itself signals "framework vs app" at a glance.

## 4. Local site & dev commands

Every project runs at **`sitename.localhost`** (add it to the Apache/hosts config once) and PHP serves on port 80, so the local URL mirrors production's shape. My [dev-tools](https://github.com/joehunterdev/dev-tools) suite handles the XAMPP side of this — vhost/hosts wiring and local-site setup — so a new `sitename.localhost` is a one-command job:

```bash
php artisan serve --host=my-app.localhost --port=80
```

The daily driver is **`npm run hot`** — Vite HMR plus the browser opening itself, via `concurrently`:

```json
// package.json scripts
{
  "scripts": {
    "dev": "vite",
    "build": "tsc && vite build",
    "preview": "vite preview",
    "hot": "concurrently \"npm run dev\" \"npm run open\"",
    "hot:fresh": "php artisan migrate:fresh --seed",
    "hot:all": "npm run hot:fresh && npm run hot",
    "serve:dev": "php artisan serve --host=my-app.localhost --port=80",
    "open": "start chrome http://my-app.localhost",
    "lint": "eslint resources/js --ext .ts,.tsx",
    "lint:fix": "eslint resources/js --ext .ts,.tsx --fix",
    "format": "prettier --write resources/js"
  }
}
```

| Script | Use |
|---|---|
| `npm run hot` | The everyday loop — Vite HMR + Chrome opens the site directly |
| `npm run hot:fresh` | Rebuild the DB (`migrate:fresh --seed`) |
| `npm run hot:all` | Fresh DB, then straight into the hot loop |

```bash
# Regenerate TypeScript types after touching a DTO or Enum
php artisan typescript:transform

# Quality gates
vendor/bin/pint --dirty     # PHP style
npm run lint                # ESLint (Airbnb)
npm run format              # Prettier
npx tsc                     # Type check
```

> **Tip** — Keep Vite's dev server pinned in `vite.config.ts` (`server: { host: '127.0.0.1', port: 5173, hmr: { host: 'localhost', port: 5173 } }`) so HMR works reliably when the site itself runs on `my-app.localhost`. If Vite ever crashes it can leave a stale `public/hot` file behind — delete it and assets serve from the last build again.

## Next

- [Database — PostgreSQL](02-database-postgresql.md)
