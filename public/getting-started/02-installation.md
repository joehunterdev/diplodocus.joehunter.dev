# Installation

Diplodocus is a single folder of PHP and CSS files. There is no installer — you
just put the folder somewhere a PHP interpreter can reach.

## Requirements

- [ ] PHP **8.0** or later
- [ ] A web server (Apache, Nginx, Caddy, or the built-in `php -S`)
- [ ] Mod rewrite or equivalent (optional — for clean URLs)

That's it. No database, no Composer, no Node.

## Three ways to install

### 1. Git clone (recommended)

```bash
git clone https://github.com/joehunter/diplodocus.git
cd diplodocus
php -S localhost:8000
```

Open `http://localhost:8000` — you should see this page.

### 2. Download the zip

1. Download the latest zip from the releases page
2. Extract into your web root (e.g. `htspaces/diplodocus/`)
3. Visit `http://localhost/diplodocus/`

### 3. Docker one-liner

```bash
docker run -p 8000:80 -v "$PWD:/var/www/html" php:8.2-apache
```

## Apache configuration

Diplodocus ships with an `.htaccess` file that handles routing. It should work
out of the box. If clean URLs aren't working, make sure `mod_rewrite` is
enabled and `AllowOverride All` is set for the directory.

```apache
<Directory /var/www/html/diplodocus>
    AllowOverride All
    Require all granted
</Directory>
```

## Nginx configuration

Nginx doesn't read `.htaccess`. Use this in your server block:

```nginx
server {
    listen 80;
    root /var/www/html/diplodocus;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }

    # Block internal folders
    location ~ ^/(src|lib|templates|\.spaces|\.backup|\.git)/ {
        deny all;
    }
}
```

## First-time checklist

- [x] PHP version is 8.0 or higher (`php -v`)
- [x] Web server is serving the Diplodocus folder
- [x] You can reach `index.php` in a browser
- [ ] Optionally: copy `config.example.php` → `config.php` to customise
- [ ] Optionally: drop your own folder of `.md` files next to `getting-started/`

## Next

- [Folder structure](03-folder-structure.md) — how Diplodocus discovers your content
- [Writing pages](04-writing-pages.md) — the markdown you can use
