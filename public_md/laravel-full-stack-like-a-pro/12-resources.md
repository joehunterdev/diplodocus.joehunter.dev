# Resources

The reading list behind this stack — official docs worth reading cover-to-cover, the educators who shaped these conventions, repos worth studying, and the pattern/anti-pattern checklists distilled from all of it.

## Official documentation

Read these end-to-end, not just when stuck. The Inertia docs in particular carry architectural recommendations most people skip.

| Docs | Why |
|---|---|
| [Laravel Documentation](https://laravel.com/docs) | The framework, canonically |
| [Inertia.js Documentation](https://inertiajs.com) | The transport layer — including the *why* of server-driven SPAs |
| [React Documentation](https://react.dev) | Modern React; see the three guides below |
| [TypeScript Handbook](https://www.typescriptlang.org/docs/handbook/intro.html) | The language, from the source |

## Educators & blogs

| Resource | Best for |
|---|---|
| [Laravel Daily](https://laraveldaily.com) ([YouTube](https://www.youtube.com/@LaravelDaily)) | Project structure, services, authorization, testing, validation, clean controllers |
| [Laravel News](https://laravel-news.com) | Staying current — releases, ecosystem packages, community practice |
| [Spatie Blog](https://spatie.be/blog) + [Packages](https://spatie.be/open-source) | **The** architecture reference — clean services, actions, DTOs, enums, events. Study how they write packages |
| [Tighten Blog](https://tighten.com/insights/) | Inertia, React, testing, performance |
| [Jonathan Reinink](https://reinink.ca) | Inertia co-creator — the *why* behind its design |
| [Aaron Francis](https://aaronfrancis.com) | Queues, jobs, scaling, debugging |
| [Total TypeScript](https://www.totaltypescript.com) (Matt Pocock) | Utility types, generics, discriminated unions, mapped types, narrowing |

**React specifically** — skip random tutorials; these three official guides teach modern React:

- [Thinking in React](https://react.dev/learn/thinking-in-react)
- [Escape Hatches](https://react.dev/learn/escape-hatches)
- [Managing State](https://react.dev/learn/managing-state)

## Repos worth reading

Don't just watch videos — read production-quality code:

- [laravel/breeze](https://github.com/laravel/breeze) — the scaffold this stack builds on
- [laravel/jetstream](https://github.com/laravel/jetstream) — a richer first-party structure to compare against
- [inertiajs/inertia](https://github.com/inertiajs/inertia) — the transport itself
- [laravel/framework](https://github.com/laravel/framework) — how the core team structures code

## Patterns worth mastering

The Laravel + Inertia + React checklist — most are documented earlier in this guide:

- Thin controllers · FormRequests for validation · **Policies for authorization** ([page 00](00-overview.md))
- Service classes · **Action classes for reusable operations** ([page 00](00-overview.md))
- DTOs + type-safe shared props ([page 03](03-dtos-and-typescript.md))
- Eloquent scopes · route model binding
- Jobs/queues · events/listeners (when the hosting supports them)
- Custom React hooks · reusable layouts · **feature-based folders, not file-type folders** ([page 04](04-frontend-react-tailwind.md))
- Testing with PHPUnit (Pest is the community's direction; consistency wins here)

## Mistakes to avoid

- Fat controllers; business logic in React components, Blade, or JSX
- Massive Eloquent models; global state for everything
- Repositories layered over Eloquent "for architecture's sake" — Eloquent *is* the abstraction
- Copying enterprise architectures into small/medium projects
- **API Resources alongside DTOs** — pick one serialization layer (here: DTOs)
- **Client-side schema validation (Zod) duplicating FormRequests** — the server is the authority; a second schema in a second language is drift waiting to happen

## Watching brief

Things to adopt deliberately, not retrofit mid-project:

| Candidate | When |
|---|---|
| React 19 | Next greenfield, once Breeze/ecosystem ship on it |
| [shadcn/ui](https://ui.shadcn.com) | Next greenfield — Tailwind-native copy-in components you own; replaces the Breeze primitives (and the "don't touch `Components/`" rule) |
| [TanStack Table](https://tanstack.com/table) / [TanStack Query](https://tanstack.com/query) | Only when a real need outgrows Inertia partial reloads + polling |

## Next

- [SEO](13-seo.md)
