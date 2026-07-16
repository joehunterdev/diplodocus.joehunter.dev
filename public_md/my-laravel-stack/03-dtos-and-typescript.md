# DTOs & TypeScript

DTOs are the **strict contract between Laravel and Inertia**, and the **single source of truth for every frontend type**. A PHP DTO marked `#[TypeScript]` is both the shape sent to the page and the origin of the matching TypeScript type. This is the most important best practice in the stack.

## Core rules

- Every Inertia page gets its data from a DTO — **never a raw Eloquent model**.
- Include only the fields the UI actually needs; don't mirror the whole table "just in case".
- The DTO is the contract: if changing it breaks the frontend build, that's the type-checker doing its job.

## Wiring the transformer

Configure the transformer in a service provider (no config file needed with the v3 factory API):

```php
// app/Providers/TypeScriptTransformerServiceProvider.php
namespace App\Providers;

use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerServiceProvider as BaseProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Transformers\{AttributedClassTransformer, EnumTransformer};
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;

class TypeScriptTransformerServiceProvider extends BaseProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            ->transformer(AttributedClassTransformer::class)
            ->transformer(EnumTransformer::class)
            ->transformDirectories(app_path('Data'), app_path('Enums'))
            ->outputDirectory(resource_path('js/types'))
            ->writer(new GlobalNamespaceWriter('generated.d.ts'))
            ->formatter(PrettierFormatter::class);
    }
}
```

Register it in `bootstrap/providers.php`. Then:

```bash
php artisan typescript:transform
```

…writes `resources/js/types/generated.d.ts` as a global ambient namespace — usable anywhere **without importing**:

```ts
declare namespace App {
  namespace Data {
    export type PostData = {
      id: number;
      title: string;
      status: App.Enums.PostStatus;
      publishedAt: string | null;
    };
  }
  namespace Enums {
    export type PostStatus = "draft" | "published" | "archived";
  }
}
```

## Writing a DTO

```php
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PostData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public PostStatus $status,          // PHP enum → TS string union
        public ?string $publishedAt,
        /** @var App\Data\TagData[] */
        public array $tags,                 // docblock drives array element type
    ) {}

    public static function fromModel(Post $post): self
    {
        return new self(
            id: $post->id,
            title: $post->title,
            status: $post->status,
            publishedAt: $post->published_at?->toIso8601String(),
            tags: TagData::collect($post->tags),
        );
    }
}
```

| Rule | Detail |
|---|---|
| `#[TypeScript]` | Required, or it won't appear in `generated.d.ts` |
| Strict types | `int`/`string`/`bool`/enums — avoid `mixed` |
| `/** @var X[] */` | The only way array element types survive into TS |
| `fromModel()` factory | Central mapping point; keeps controllers thin |
| Nested DTOs | Relations become their own DTO, never a raw model |
| `*Data` / `*PageData` naming | `*PageData` for the top-level props of a page |

## Consuming it in React

Page DTOs are `Responsable`, so pass the object straight to `Inertia::render`:

```php
// Controller — thin: validate, delegate, render a DTO
public function index(): Response
{
    return Inertia::render('Posts/Index', PostIndexPageData::from($this->posts->list()));
}
```

```tsx
// resources/js/Pages/Posts/Index.tsx
import type { PageProps } from '@/types';

interface Props extends PageProps, App.Data.PostIndexPageData {}

export default function Index({ posts }: Props) {
  return <ul>{posts.map((p) => <li key={p.id}>{p.title}</li>)}</ul>;
}
```

Feature modules may re-export the generated types under local aliases, but must **never redefine the shape by hand**:

```ts
// resources/js/features/posts/types.ts
// Generated from PHP DTOs — run `php artisan typescript:transform` to regenerate.
export type Post = App.Data.PostData;
```

> **Danger** — Never hand-write a TypeScript interface that duplicates a DTO. The moment the PHP and TS drift, you have two contracts and no source of truth. Regenerate instead.

## The golden rule

**If changing a DTO breaks the frontend → that's correct.** A type error after a DTO change is the compiler forcing the UI to handle the new shape. Run `php artisan typescript:transform`, let `tsc` list the breakages, fix them.

## Next

- [Frontend — React & Tailwind](04-frontend-react-tailwind.md)
