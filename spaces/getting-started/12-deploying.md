# Deploying

Diplodocus is pure PHP. Any host that runs PHP 8.0+ can run Diplodocus with no
build step and no database provisioning.

## Apache (shared hosting, cPanel)

The shipped `.htaccess` handles everything. Typical upload flow:

1. Upload the Diplodocus folder to `public_html/` (or a subfolder)
2. Make sure `AllowOverride All` is set for the directory
3. Visit your domain — you should see the spaces site

```apache
# .htaccess (ships with Diplodocus)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    RewriteRule ^assets/.*$ - [L]
    RewriteRule ^(src|lib|templates|\.spaces|\.backup|\.vscode|\.claude|\.git)/ - [F,L]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?$1 [L,QSA]
</IfModule>
```

## Nginx

```nginx
server {
    listen 80;
    server_name spaces.example.com;
    root /var/www/diplodocus;
    index index.php;

    # Assets served directly
    location /assets/ {
        try_files $uri =404;
    }

    # Block internal folders
    location ~ ^/(src|lib|templates|\.spaces|\.backup|\.git)/ {
        deny all;
        return 403;
    }

    # Everything else goes through index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Caddy

```caddyfile
spaces.example.com {
    root * /var/www/diplodocus
    php_fastcgi unix//run/php/php8.2-fpm.sock

    @blocked path_regexp ^/(src|lib|templates|\.spaces|\.backup|\.git)/
    respond @blocked 403

    try_files {path} {path}/ /index.php?{query}
    file_server
}
```

## Docker

Minimal `Dockerfile`:

```dockerfile
FROM php:8.2-apache
COPY . /var/www/html/
RUN a2enmod rewrite
```

Build and run:

```bash
docker build -t my-diplodocus-spaces .
docker run -p 8080:80 my-diplodocus-spaces
```

## Docker Compose

```yaml
services:
  spaces:
    image: php:8.2-apache
    volumes:
      - ./:/var/www/html
    ports:
      - "8080:80"
    command: >
      bash -c "a2enmod rewrite && apache2-foreground"
```

## PHP built-in server (local only)

For local development or a quick demo, no web server needed:

```bash
cd /path/to/diplodocus
php -S localhost:8000
```

Do **not** use the built-in server for production — it's single-threaded
and has no security hardening.

## Shared hosting checklist

- [ ] PHP version is 8.0+ (`php -v` via SSH, or a `phpinfo()` test file)
- [ ] `mod_rewrite` is enabled
- [ ] `AllowOverride All` is set (some shared hosts disable this)
- [ ] Folder permissions are 755 for dirs, 644 for files
- [ ] `.env`, `config.php`, `config.local.php` are NOT world-readable
- [ ] Deleted `debug_scan.php` and any other dev-only files

## Security hardening

Before going public, check that these are in place:

### 1. Block access to internal folders

Already configured in the shipped `.htaccess` and Nginx examples above.
Verify by visiting `https://yourdomain.com/src/App.php` — you should get
a 403, not PHP source.

### 2. Disable PHP error display

In production `php.ini`:

```ini
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

### 3. Set security headers

The shipped `.htaccess` includes these — verify they're active:

```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

### 4. Run the security scanner before publishing

```bash
php cli.php scan-security
```

Make sure no API keys, credentials, or private data have made it into
your markdown.

## Zero-downtime deploys

Diplodocus has no state, no cache to warm, and no database migrations. A
standard `rsync` or `git pull` is sufficient:

```bash
# On the server
cd /var/www/diplodocus
git pull origin main
```

For atomic swaps, use a symlink pattern:

```bash
# Build new version in a dated folder
rsync -a /tmp/diplodocus-new/ /var/www/releases/2026-04-19/
# Atomically swap
ln -sfn /var/www/releases/2026-04-19 /var/www/diplodocus
```

## CDN for assets

Since everything under `assets/` is static, you can front it with any
CDN (Cloudflare, Fastly, CloudFront). Update the `stylesheets` and
`scripts` paths in `config.php` to absolute URLs:

```php
'stylesheets' => [
    'https://cdn.example.com/diplodocus/theme.css',
    'https://cdn.example.com/diplodocus/diplodocus.css',
],
```

## You're done

That's the whole guide. Drop your own `.md` folders next to
`getting-started/` and you're ready to publish.

Questions, bugs, or ideas? Open an issue on GitHub.

## Back to start

- [Welcome](01-welcome.md)
