# .htaccess Examples

Two `.htaccess` files ship with every Hostinger deploy: a small one at the **project root** (forces the PHP version and funnels everything into `public/`), and a hardened one in **`public/`** (security headers, hotlink protection, caching, compression, Laravel routing). Copy both, adjust the domains, done.

## Root `.htaccess`

On Hostinger shared hosting the app root sits inside `public_html/`, so the root file has two jobs: pin the PHP handler and rewrite everything to `public/`.

```apache
# Force PHP 8.5 on Hostinger
AddHandler application/x-httpd-php85 .php

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Prevent access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

# Prevent access to vendor and other directories
RedirectMatch 403 ^/vendor/.*$
RedirectMatch 403 ^/bootstrap/.*$
```

> **Note** — The `AddHandler` line must match the PHP version your plan runs (`php85` here). If artisan works over SSH but the site serves a different PHP version, this line is the first place to look.

## `public/.htaccess`

The full hardened version — Laravel's stock rules plus security headers, hotlink protection, cache policy, and compression.

```apache
# =============================================================================
# Security — disable directory browsing
# =============================================================================
Options -Indexes

# =============================================================================
# Security headers
# =============================================================================
<IfModule mod_headers.c>
    # Prevent clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"

    # Block MIME-type sniffing
    Header always set X-Content-Type-Options "nosniff"

    # Basic XSS protection for older browsers
    Header always set X-XSS-Protection "1; mode=block"

    # Don't send referrer to external sites
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Only allow HTTPS for this site (1 year; enable once fully on HTTPS)
    # Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # Restrict powerful browser features
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=(self), payment=(self)"

    # Remove server signature
    Header always unset X-Powered-By
    Header always unset Server
</IfModule>

# =============================================================================
# Hotlink protection — block direct image/font linking from other domains
# =============================================================================
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Allow blank referrer (direct access / curl) and same-origin requests
    RewriteCond %{HTTP_REFERER} !^$
    RewriteCond %{HTTP_REFERER} !^https?://(www\.)?my-app\.localhost [NC]
    RewriteCond %{HTTP_REFERER} !^https?://localhost(:[0-9]+)? [NC]
    RewriteCond %{HTTP_REFERER} !^https?://(www\.)?example\.com [NC]
    RewriteCond %{HTTP_REFERER} !^https?://(www\.)?stg\.example\.com [NC]
    RewriteRule \.(jpe?g|png|gif|svg|webp|ico|woff2?|ttf|otf|eot)$ - [F,NC,L]
</IfModule>

# =============================================================================
# Browser caching
# =============================================================================
<IfModule mod_expires.c>
    ExpiresActive On

    # Versioned build assets (Vite appends content hash) — cache 1 year
    <FilesMatch "\.(js|css)$">
        ExpiresDefault "access plus 1 year"
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>

    # Images & fonts — cache 6 months
    <FilesMatch "\.(jpe?g|png|gif|svg|webp|ico|woff2?|ttf|otf|eot)$">
        ExpiresDefault "access plus 6 months"
        Header set Cache-Control "public, max-age=15552000"
    </FilesMatch>

    # HTML / PHP — no cache (always fresh)
    <FilesMatch "\.(html|php)$">
        ExpiresDefault "access plus 0 seconds"
        Header set Cache-Control "no-store, no-cache, must-revalidate"
    </FilesMatch>
</IfModule>

# =============================================================================
# Compression
# =============================================================================
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript
    AddOutputFilterByType DEFLATE application/javascript application/json
    AddOutputFilterByType DEFLATE image/svg+xml font/woff2
</IfModule>

# =============================================================================
# Laravel routing
# =============================================================================
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Adjust per project

| Section | What to change |
|---|---|
| `AddHandler` (root) | PHP version for the host (`php85` on Hostinger currently) |
| Hotlink `RewriteCond` list | Swap `example.com` / `stg.example.com` / `my-app.localhost` for the real local, staging, and production domains |
| HSTS header | Uncomment only when the domain is fully and permanently HTTPS |
| `Permissions-Policy` | Grant back `geolocation`/`payment` etc. only if the app actually uses them |

> **Warning** — Test the hotlink rules after adjusting: a typo'd domain regex blocks your **own** images and fonts. The blank-referrer allowance keeps curl/direct loads working while you verify.

> **Tip** — Keep `public/.htaccess` committed and out of the FTP `exclude` list; it *is* part of the deploy (the `.gitignore` on [page 10](10-workspace-and-gitignore.md) explicitly un-ignores it with `!/public/.htaccess`).

## Next

- [Workspace & gitignore](10-workspace-and-gitignore.md)
