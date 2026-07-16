# SEO

The core SEO kit every project ships with: one `config/seo.php` as the single source of truth, a **master indexability switch**, dynamic `robots.txt`, a named-route sitemap, full Open Graph / Twitter cards with a share image, canonical URLs, and JSON-LD structured data. All server-rendered — crawlers and link-preview scrapers get everything without executing a line of JavaScript.

## The master switch — `SEO_INDEXABLE`

The single most useful idea here: **one env flag controls whether the site exists to search engines.**

```php
// config/seo.php (top of file)
'indexable' => env('SEO_INDEXABLE', false),
```

- Default **false** — pre-launch, staging, and demo sites emit `<meta name="robots" content="noindex, nofollow">` on every page *and* `robots.txt` disallows all crawling. You can still verify the domain in Search Console (verification doesn't require indexing).
- Set `SEO_INDEXABLE=true` **in the production `.env` only** at launch — that one change opens crawling + indexing site-wide.

> **Danger** — Staging is the classic leak: an indexable staging clone competes with production in search results. The `false` default means a forgotten env key fails *closed*, never open.

## `config/seo.php`

Everything meta lives here — site defaults, OG copy, structured-data fields, and a per-route override map:

```php
<?php

return [
    'indexable' => env('SEO_INDEXABLE', false),

    // Site-wide fallbacks
    'site_name'           => 'My App',
    'default_title'       => 'My App — What It Does, Where',
    'default_description' => 'One or two sentences. This is your search snippet — write it like ad copy, 150–160 chars.',

    // Open Graph / Twitter (the social headline can differ from <title>)
    'og_title'       => 'The marketing headline for shares',
    'og_description' => 'The share-card blurb.',
    'og_image'       => '/images/og/default.png',
    'twitter_handle' => '@myapp',
    'locale'         => 'en_GB',

    // Structured data (schema.org)
    'logo'         => '/images/favicon.png',
    'areas_served' => ['City One', 'City Two'],
    'same_as'      => array_values(array_filter([
        env('SEO_FACEBOOK_URL'),
        env('SEO_INSTAGRAM_URL'),
        env('SEO_LINKEDIN_URL'),
    ])),

    // Per-route meta, keyed by route name
    'pages' => [
        'static.about' => [
            'title'       => 'About My App',
            'description' => 'Page-specific snippet.',
        ],
        'static.contact' => [
            'title'       => 'Contact My App',
            'description' => '…',
        ],
    ],
];
```

**Meta resolution order** (implemented in the layout): ① explicit prop/page value → ② `seo.pages` map by current route name → ③ site-wide defaults. Static pages never touch a controller to get correct meta — naming the route is enough.

## The `<head>` block

In an Inertia app this belongs in the **root Blade template** (`resources/views/app.blade.php`) — link-preview scrapers (WhatsApp, Slack, X, Facebook) don't run JS, so OG tags rendered by React are invisible to them. Use Inertia's `<Head>` component only for the client-side `<title>` on navigation.

```blade
@php
    $routeName = request()->route()?->getName();
    $pageMeta = config("seo.pages.{$routeName}", []);

    $metaTitle       = $title ?? $pageMeta['title'] ?? config('seo.default_title');
    $metaDescription = $description ?? $pageMeta['description'] ?? config('seo.default_description');
    $ogTitle         = $title ?? $pageMeta['title'] ?? config('seo.og_title');
    $ogDescription   = $description ?? $pageMeta['description'] ?? config('seo.og_description');
    $canonicalUrl    = url()->current();
    $ogImageUrl      = url(config('seo.og_image'));
@endphp

@unless(config('seo.indexable'))
    <meta name="robots" content="noindex, nofollow">
@endunless

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:image" content="{{ $ogImageUrl }}">
<meta property="og:image:alt" content="{{ config('seo.site_name') }} — preview">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:locale" content="{{ config('seo.locale') }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImageUrl }}">
```

## The share image (`og/default.png`)

The image behind every social share. Specs that matter:

| Property | Value |
|---|---|
| Size | **1200 × 630 px** (the `summary_large_image` / OG standard, ~1.91:1) |
| Format | PNG or JPG; keep it well under ~1 MB (some scrapers cap at 300 KB — aim there) |
| Location | `public/images/og/default.png` — a **stable path**; scrapers cache aggressively |
| URL | Must be **absolute** (`url(...)` in the layout handles it) |
| Content | Logo + headline readable at thumbnail size; safe margins ≥ 60 px (platforms crop edges) |
| Alt | Always ship `og:image:alt` |

A pragmatic default: screenshot the polished homepage at 1200×630 and overlay the wordmark. Per-page share images (e.g. per product/article) follow the same resolution order — an explicit prop overriding the default.

> **Tip** — After changing the image, force-refresh the scraper caches: Facebook [Sharing Debugger](https://developers.facebook.com/tools/debug/), [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/), and X card validator. Otherwise the old card can linger for days.

## Dynamic `robots.txt`

Serve it from a route, not a static file, so the master switch controls it too:

```php
// routes/web.php
Route::get('/robots.txt', function () {
    $lines = config('seo.indexable')
        ? ['User-agent: *', 'Disallow:', '', 'Sitemap: '.url('/sitemap.xml')]
        : ['User-agent: *', 'Disallow: /'];

    return response(implode("\n", $lines), 200)
        ->header('Content-Type', 'text/plain');
})->name('robots');
```

Delete `public/robots.txt` — the physical file would shadow the route.

## Sitemap

A small controller over **named routes** (they're already the app's URL source of truth via Ziggy), rendered through a Blade view to XML:

```php
// app/Http/Controllers/SitemapController.php
class SitemapController extends Controller
{
    public function index(): Response
    {
        $routes = [
            ['url' => route('home'),           'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => route('static.about'),   'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => route('static.contact'), 'priority' => '0.6', 'changefreq' => 'yearly'],
            ['url' => route('static.privacy'), 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        return response(view('sitemap', ['routes' => $routes])->render(), 200)
            ->header('Content-Type', 'application/xml');
    }
}
```

```blade
{{-- resources/views/sitemap.blade.php --}}
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($routes as $r)
    <url>
        <loc>{{ $r['url'] }}</loc>
        <priority>{{ $r['priority'] }}</priority>
        <changefreq>{{ $r['changefreq'] }}</changefreq>
    </url>
@endforeach
</urlset>
```

Only list **public, indexable** pages — never auth, dashboards, or anything behind login. For dynamic content (products, articles), append a DB query's URLs to the array; if that grows past a few thousand URLs, graduate to [spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap) with a scheduled generation command.

## Structured data (JSON-LD)

One `<script type="application/ld+json">` in the same layout, built from config so it can't drift from the meta:

```blade
@php
    $structuredData = array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'LocalBusiness',   // or Organization / WebSite per project
        'name'        => config('seo.site_name'),
        'url'         => url('/'),
        'logo'        => url(config('seo.logo')),
        'description' => config('seo.default_description'),
        'areaServed'  => config('seo.areas_served'),
        'sameAs'      => config('seo.same_as'),
    ], fn ($v) => $v !== [] && $v !== null);
@endphp
<script type="application/ld+json">
    {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
```

Validate with Google's [Rich Results Test](https://search.google.com/test/rich-results). Pick the `@type` per project — `LocalBusiness` for location-based services, `Organization` or `WebSite` otherwise.

## Launch checklist

1. `SEO_INDEXABLE=true` in the **production** `.env` only
2. `og/default.png` in place (1200×630), share card verified in the Facebook debugger
3. `/robots.txt` shows `Disallow:` (empty) + the sitemap line
4. `/sitemap.xml` renders and lists only public pages
5. Canonical URLs use the production domain (`APP_URL` correct)
6. JSON-LD passes the Rich Results Test
7. Domain verified in Search Console; sitemap submitted

## Next

- Back to [Overview](00-overview.md)
