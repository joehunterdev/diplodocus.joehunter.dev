# Frontend — React & Tailwind

The frontend is Inertia v2 + React 18 + TypeScript, styled with Tailwind v4, and held to an Airbnb ESLint + Prettier standard. React is UI only — no business logic, no data fetching layer beyond what Inertia gives you.

## Structure & conventions

```
resources/js/
├── app.tsx            # Inertia client entry
├── bootstrap.ts       # axios + Ziggy globals
├── Pages/             # Inertia pages (Breeze + app)   ← Capitalized
├── Components/        # Breeze UI primitives            ← Capitalized
├── Layouts/           # AuthenticatedLayout, GuestLayout ← Capitalized
├── features/          # feature modules                 ← lowercase
│   └── posts/
│       ├── components/
│       ├── hooks/
│       ├── types.ts
│       └── index.ts   # barrel — the feature's public API
├── shared/            # cross-feature components/hooks/utils
└── types/             # generated.d.ts, index.d.ts, global.d.ts
```

| Rule | Detail |
|---|---|
| Casing split | Breeze scaffold stays Capitalized; app code is lowercase `features/`/`shared/` |
| Pages are thin | A page wires props to a container/feature; it holds no logic |
| Feature isolation | No cross-feature runtime imports; share via `shared/` or type-only imports |
| Barrel files | Import from `@/features/posts`, never deep paths |
| Dependency direction | `types/ → shared/ → features/ → Pages/ → Layouts/` |
| Types from DTOs | Consume `App.Data.*`; never hand-redefine (see [DTOs](03-dtos-and-typescript.md)) |

## Forms — the standard pattern

Inertia `useForm` for client state + error bag; a Laravel `FormRequest` is the authority on validation; errors render through Breeze's `InputError`. No client-side schema library.

```tsx
import { useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';

export default function CreatePost() {
  const form = useForm({ title: '', body: '' });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    form.post(route('posts.store'));
  };

  return (
    <form onSubmit={submit}>
      <input
        value={form.data.title}
        onChange={(e) => form.setData('title', e.target.value)}
      />
      <InputError message={form.errors.title} />
      <button type="submit" disabled={form.processing}>Save</button>
    </form>
  );
}
```

Field names in `form.errors.*` line up 1:1 with the `FormRequest` rule keys — that's what makes error display automatic. Navigate with Ziggy's `route()` and Inertia's `<Link>`/`router`; reach for `axios` only for non-navigational JSON.

## Casing at the boundary — camelCase front, snake_case back

The backend speaks Laravel-native **snake_case** (columns, rule keys); the frontend speaks idiomatic **camelCase** (props, variables). Neither side bends — a transformer converts keys at the Inertia boundary.

```php
// app/Traits/HasCamelCaseKeys.php
namespace App\Traits;

use Illuminate\Support\Str;

trait HasCamelCaseKeys
{
    public function transformKeysToCamelCase($data)
    {
        if ($data instanceof \Illuminate\Database\Eloquent\Collection
            || $data instanceof \Illuminate\Database\Eloquent\Model) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }

        return array_map(function ($value) {
            return is_array($value) || is_object($value)
                ? $this->transformKeysToCamelCase($value)
                : $value;
        }, array_combine(
            array_map([Str::class, 'camel'], array_keys($data)),
            $data,
        ));
    }
}
```

Two ways to apply it:

1. **Per-response (trait)** — `use HasCamelCaseKeys;` in a controller and wrap the payload: `'channelData' => $this->transformKeysToCamelCase($channelData)`. Explicit, easy to adopt incrementally.
2. **Globally (middleware)** — a middleware after `HandleInertiaRequests` that runs the same transform over every Inertia props array, so no controller ever thinks about it. Prefer this on a fresh project; the trait remains for one-off JSON responses.

On DTO-first projects, you can get the same result at the source instead: name DTO constructor properties in camelCase inside `fromModel()` (`publishedAt: $post->published_at`), and the generated TS types are camelCased for free. Either way the rule is the same — **snake never leaks into React, camel never leaks into Eloquent**.

> **Note** — Inbound is the mirror image: `useForm` field names must match the `FormRequest` rule keys. Keep form fields snake_case (they're the *contract*, not UI variables), or map them in the form hook before posting.

## Tailwind v4

Tailwind v4 uses the Vite plugin and CSS-first `@theme` tokens — **no `tailwind.config.js`**.

```ts
// vite.config.ts
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    laravel({ input: ['resources/js/app.tsx'], refresh: true }),
    react(),
    tailwindcss(),
  ],
});
```

### Base design tokens

This is the `@theme` block every project starts from — copy it into `resources/css/app.css` and re-tint the colors per brand. The *names* stay stable across projects; only values change.

```css
/* resources/css/app.css */
@import "tailwindcss";

@theme {
  /* Colors */
  --color-background: #0f172a;
  --color-surface: #111827;
  --color-primary: #3b82f6;
  --color-secondary: #8b5cf6;
  --color-accent: #06b6d4;
  --color-text: #f8fafc;
  --color-muted: #94a3b8;
  --color-border: #334155;

  /* Typography */
  --font-sans: Inter, system-ui, sans-serif;
  --font-mono: "JetBrains Mono", monospace;

  /* Radius */
  --radius-xs: 4px;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;

  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;

  /* Shadows */
  --shadow-card: 0 8px 24px rgb(0 0 0 / 0.08);
  --shadow-focus: 0 0 0 3px rgb(59 130 246 / 0.35);

  /* Animation */
  --ease-standard: cubic-bezier(0.4, 0, 0.2, 1);
  --duration-fast: 150ms;
  --duration-normal: 250ms;
}
```

Every token becomes a utility automatically — no config, no plugin:

| Token group | Generated utilities (examples) |
|---|---|
| `--color-*` | `bg-background`, `bg-surface`, `text-text`, `text-muted`, `border-border`, `bg-primary`, `ring-accent` |
| `--font-*` | `font-sans`, `font-mono` |
| `--radius-*` | `rounded-xs` … `rounded-xl` (overrides Tailwind's default scale with these values) |
| `--spacing-*` | `p-md`, `gap-lg`, `mt-xl` — named steps alongside the numeric scale |
| `--shadow-*` | `shadow-card`, `shadow-focus` |
| `--ease-*` / `--duration-*` | `ease-standard`, `duration-fast`, `duration-normal` |

Keep **all** tokens in this one block — components never hardcode a hex, a radius, or a duration. Rebranding a project is editing eight color lines.

> **Tip** — The tokens are also plain CSS variables at runtime (`var(--color-primary)`), so the rare hand-written CSS rule and any JS that needs a brand color read from the same source of truth.

> **Warning** — Breeze currently scaffolds Tailwind **v3** (`tailwind.config.js` + `@tailwind` directives). On a v4 project, upgrade the generated auth pages: swap the directives for `@import "tailwindcss"`, move theme values into `@theme`, and register `@tailwindcss/vite`. Do this once, right after `breeze:install`.

## Airbnb ESLint + Prettier

The JS/TS quality gate is Airbnb's ruleset plus Prettier for formatting, with `tsc --strict` as the type gate. (Packages are installed on [Create the project](01-create-the-project.md).)

Airbnb's shared configs predate ESLint 9's flat config, so consume them through `FlatCompat`:

```js
// eslint.config.js
import { FlatCompat } from '@eslint/eslintrc';
import prettier from 'eslint-config-prettier';

const compat = new FlatCompat({ baseDirectory: import.meta.dirname });

export default [
  ...compat.extends(
    'airbnb',
    'airbnb/hooks',
    'airbnb-typescript',
  ),
  prettier, // must be last — turns off rules Prettier owns
  {
    languageOptions: { parserOptions: { project: './tsconfig.json' } },
    rules: {
      'react/react-in-jsx-scope': 'off',        // React 18 + automatic runtime
      'import/prefer-default-export': 'off',
      'react/jsx-props-no-spreading': 'off',     // Inertia/Breeze spread props
    },
  },
];
```

```json
// package.json
{
  "scripts": {
    "lint": "eslint resources/js --ext .ts,.tsx",
    "lint:fix": "eslint resources/js --ext .ts,.tsx --fix",
    "format": "prettier --write resources/js",
    "build": "tsc && vite build"
  }
}
```

| Gate | Command | Enforces |
|---|---|---|
| Lint | `npm run lint` | Airbnb correctness + style rules |
| Format | `npm run format` | Prettier (single source of formatting) |
| Types | `npx tsc` | `strict` type safety; blocks `build` |

> **Tip** — `eslint-config-prettier` (last in the array) disables every ESLint rule that would fight Prettier. Let ESLint judge *code*, Prettier own *formatting* — never both on the same concern.

> **Note** — If flat-config friction with the Airbnb TS preset gets in the way, the fallback is to pin ESLint 8 and use a classic `.eslintrc.cjs` with the same `extends`. Prefer the flat-config route to stay current.

## Next

- [AI-driven dev docs](05-ai-driven-dev-docs.md)
