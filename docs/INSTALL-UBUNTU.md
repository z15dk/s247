# Ubuntu VPS setup — Studie 247

Fuld guide til at få WordPress + Studie 247-temaet op at køre på en frisk Ubuntu-VPS (22.04 eller 24.04).

---

## 0. Forudsætninger

- Ubuntu 22.04 / 24.04 VPS med SSH-adgang
- En bruger med `sudo`
- VPS'ens IP-adresse (domæne tilføjes senere)

---

## 1. System update

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget unzip git ufw
```

### Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw --force enable
```

---

## 2. Installer LEMP (Nginx + MySQL + PHP)

```bash
sudo apt install -y nginx mysql-server \
    php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd \
    php8.3-mbstring php8.3-xml php8.3-zip php8.3-imagick \
    php8.3-intl php8.3-bcmath
```

### PHP tuning (anbefalet)

Rediger `/etc/php/8.3/fpm/php.ini`:

```
upload_max_filesize = 64M
post_max_size = 64M
memory_limit = 256M
max_execution_time = 300
max_input_vars = 3000
```

Genstart: `sudo systemctl restart php8.3-fpm`

---

## 3. Sæt MySQL op

```bash
sudo mysql_secure_installation
```

Opret database + bruger:

```bash
sudo mysql <<'SQL'
CREATE DATABASE studie247 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'studie247'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PW';
GRANT ALL ON studie247.* TO 'studie247'@'localhost';
FLUSH PRIVILEGES;
SQL
```

> Gem password sikkert — du skal bruge det om lidt.

---

## 4. Installer WP-CLI

```bash
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
wp --info   # verificer installation
```

---

## 5. Download og installer WordPress

```bash
sudo mkdir -p /var/www/studie247
sudo chown -R $USER:www-data /var/www/studie247
cd /var/www/studie247

wp core download --locale=da_DK

wp config create \
    --dbname=studie247 \
    --dbuser=studie247 \
    --dbpass='CHANGE_ME_STRONG_PW' \
    --extra-php <<'PHP'
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISALLOW_FILE_EDIT', true );
define( 'FS_METHOD', 'direct' );
PHP

wp core install \
    --url="http://<DIN-VPS-IP>" \
    --title="Studie 247" \
    --admin_user=admin \
    --admin_password='CHANGE_ME_STRONG_ADMIN' \
    --admin_email="dig@studie247.dk" \
    --skip-email
```

### Rettigheder

```bash
sudo chown -R www-data:www-data /var/www/studie247
sudo find /var/www/studie247 -type d -exec chmod 755 {} \;
sudo find /var/www/studie247 -type f -exec chmod 644 {} \;
```

---

## 6. Klon Studie 247-temaet

```bash
cd /var/www/studie247/wp-content/themes
sudo -u www-data git clone -b claude/design-moodboard-guide-mBFFi <REPO-URL> studie247

wp theme activate studie247 --path=/var/www/studie247
```

---

## 7. Nginx vhost

Opret `/etc/nginx/sites-available/studie247`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name _;   # skift til studie247.dk når domæne er klar

    root /var/www/studie247;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript
               application/json application/xml application/rss+xml image/svg+xml;

    # Cache statics aggressively
    location ~* \.(jpg|jpeg|png|webp|avif|gif|ico|svg|woff2?|ttf|otf|eot|mp4|webm)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location ~* \.(css|js)$ {
        expires 7d;
        add_header Cache-Control "public";
        access_log off;
    }

    # WordPress permalinks
    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    # PHP handler
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Block sensitive files
    location ~ /\.(ht|git|env) { deny all; }
    location ~ ^/(wp-config\.php|readme\.html|license\.txt) { deny all; }
    location = /xmlrpc.php { deny all; }

    # Favicon + robots
    location = /favicon.ico { log_not_found off; access_log off; }
    location = /robots.txt  { log_not_found off; access_log off; allow all; }

    client_max_body_size 64M;
}
```

Aktiver:

```bash
sudo ln -s /etc/nginx/sites-available/studie247 /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

Besøg nu `http://<DIN-VPS-IP>` — du bør se forsiden.

---

## 8. Permalinks + setup

I WP-admin (`http://<VPS-IP>/wp-admin`):

1. **Indstillinger → Permalinks** → "Indlægsnavn" → Gem.
2. **Udseende → Menuer** → opret "Hovedmenu":
   - Services → `/services/`
   - Studiet → `/studiet/`
   - Priser → `/priser/`
   - Udlejning → `/udlejning/`
   - Om → `/om/`
   - Kontakt → `/kontakt/`
   - Tildel "Hovedmenu"-location.
3. **Sider** — opret:
   - Forside (Template: Standard)
   - Studiet (Template: Studiet)
   - Priser (Template: Priser)
   - Om (Template: Om)
   - Kontakt (Template: Kontakt)
   - Udlejning (Template: Udlejning)
   - Book (Template: Book)
4. **Indstillinger → Læsning** → "Forside viser: en statisk side" → vælg Forside.
5. **Services → Tilføj ny** — opret de 4 første services.

---

## 9. Fonts

Download Aileron og læg i `assets/fonts/aileron/`:
- `Aileron-Regular.woff2`
- `Aileron-Bold.woff2`

[https://fontsquirrel.com/fonts/aileron](https://fontsquirrel.com/fonts/aileron)

Køb Migra og læg i `assets/fonts/migra/`:
- `Migra-ExtralightItalic.woff2`
- `Migra-ExtraboldItalic.woff2`

[https://pangrampangram.com/products/migra](https://pangrampangram.com/products/migra)

Indtil da bruges Playfair Display Italic som fallback (loades automatisk fra Google Fonts).

---

## 10. Når domænet er klar

```bash
# DNS: peg A-record mod VPS-IP
# Skift server_name i Nginx-config
sudo sed -i 's/server_name _;/server_name studie247.dk www.studie247.dk;/' /etc/nginx/sites-available/studie247
sudo nginx -t && sudo systemctl reload nginx

# Certbot
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d studie247.dk -d www.studie247.dk \
    --redirect --agree-tos --non-interactive -m dig@studie247.dk

# Skift URLs i DB
cd /var/www/studie247
wp search-replace "http://<gammel-ip>" "https://studie247.dk" --all-tables
wp option update siteurl "https://studie247.dk"
wp option update home    "https://studie247.dk"
wp cache flush
```

---

## 11. Backups (anbefalet)

Tilføj cron (daglig DB-dump kl. 3):

```bash
sudo crontab -e
```

```
0 3 * * * cd /var/www/studie247 && /usr/local/bin/wp db export /var/backups/s247-$(date +\%F).sql --add-drop-table && find /var/backups/ -name 's247-*.sql' -mtime +14 -delete
```

---

## Fejlfinding

### "White screen" / 500
```bash
sudo tail -f /var/www/studie247/wp-content/debug.log
sudo tail -f /var/log/nginx/error.log
```

### Permissions error ved upload
```bash
sudo chown -R www-data:www-data /var/www/studie247/wp-content/uploads
```

### Permalinks virker ikke
```bash
wp rewrite flush --hard --path=/var/www/studie247
```
