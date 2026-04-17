# Studie 247 — Custom WordPress Theme

> Optag. Skab. Udgiv 247.

Custom SEO-optimeret WordPress-tema til Studie 247. Ingen page builder, ingen bloat — ren PHP/CSS/JS med fuld kontrol over markup, performance og SEO.

---

## Indhold

- [Hvad er dette](#hvad-er-dette)
- [Tech stack](#tech-stack)
- [Mappestruktur](#mappestruktur)
- [Lokal udvikling på Ubuntu VPS](#lokal-udvikling-på-ubuntu-vps)
- [WordPress-opsætning efter installation](#wordpress-opsætning-efter-installation)
- [Opret services (CPT)](#opret-services-cpt)
- [Brand guide](#brand-guide)
- [SEO features](#seo-features)
- [Roadmap](#roadmap)
- [Deployment (domæne senere)](#deployment-domæne-senere)

---

## Hvad er dette

Studie 247 er ét produktionsstudie med pakker omkring:
- **SoMe-videoer** (korte, skarpe clips)
- **Podcast** (plug & play setup)
- **Online kursus** (professionel undervisning)
- **Fotoshoot** (produkt, portræt, brand)
- **Råleje af studiet** (for erfarne teams)
- **Udstyrs-udlejning** (bygges til sidst)

Services er en Custom Post Type (`service`) og kan udvides frit fra WP-admin uden kodeændringer.

---

## Tech stack

- **WordPress 6.x** (PHP 8.1+)
- **Custom tema** — ingen page builder, ingen theme framework
- **Vanilla CSS** med design tokens (custom properties)
- **Vanilla JS** (~2 KB) — sticky header, mobilmenu, smooth scroll
- **Native meta boxes** — ingen ACF-afhængighed
- **SEO indbygget** — meta, OG, Twitter, JSON-LD, breadcrumbs, FAQPage

---

## Mappestruktur

```
studie247/                        ← tema-roden
├── style.css                     ← WordPress tema-header
├── functions.php                 ← bootstrapper inc/
├── index.php                     ← fallback/blog
├── front-page.php                ← forsiden
├── page.php                      ← standard-side
├── single-service.php            ← enkelt service-side
├── archive-service.php           ← /services/-oversigt
├── 404.php
├── header.php
├── footer.php
├── inc/
│   ├── theme-setup.php           ← theme supports, image sizes
│   ├── enqueue.php               ← CSS/JS enqueue
│   ├── menus.php                 ← nav menus + fallback m/ Udlejning
│   ├── cpt-service.php           ← CPT: Services
│   ├── cpt-case.php              ← CPT: Cases / showreel
│   ├── cpt-testimonial.php       ← CPT: Testimonials
│   ├── cpt-booking.php           ← CPT: Bookinger (stub)
│   ├── cpt-udlejning.php         ← CPT: Udstyrs-udlejning (aktiv, template senere)
│   ├── meta-boxes.php            ← Native meta felter
│   ├── seo.php                   ← Meta description, OG, Twitter
│   ├── schema.php                ← JSON-LD (LocalBusiness, Service, Breadcrumbs)
│   ├── svg-icons.php             ← Inline SVG-bibliotek
│   └── template-tags.php         ← Små view-helpers
├── template-parts/
│   ├── hero.php
│   ├── section-services.php
│   ├── section-studio.php
│   ├── section-process.php
│   ├── section-cases.php
│   ├── section-testimonials.php
│   ├── section-faq.php
│   └── section-cta.php
├── page-templates/
│   ├── page-priser.php
│   ├── page-studiet.php
│   ├── page-om.php
│   ├── page-kontakt.php
│   ├── page-udlejning.php        ← placeholder, bygges til sidst
│   └── page-book.php             ← placeholder, custom bookingsystem kommer
├── assets/
│   ├── css/
│   │   ├── variables.css         ← design tokens (farver, fonts, spacing)
│   │   ├── fonts.css             ← @font-face Aileron + Migra
│   │   ├── base.css              ← reset, typografi, body
│   │   ├── components.css        ← knapper, kort, forms, pill, accordion
│   │   └── sections.css          ← header, hero, services, studio, footer...
│   ├── js/
│   │   └── main.js
│   └── fonts/
│       ├── aileron/              ← drop .woff2 her (download fra fontsquirrel)
│       └── migra/                ← drop .woff2 her (kræver licens)
├── docs/
│   └── INSTALL-UBUNTU.md         ← fuld VPS-opsætning
└── README.md
```

---

## Lokal udvikling på Ubuntu VPS

Fuld step-by-step guide ligger i [`docs/INSTALL-UBUNTU.md`](docs/INSTALL-UBUNTU.md). Kort version:

```bash
# 1) Opdater og installer LEMP-stack
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server php8.3-fpm php8.3-mysql \
    php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip \
    php8.3-imagick unzip curl

# 2) Opret DB
sudo mysql <<SQL
CREATE DATABASE studie247 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'studie247'@'localhost' IDENTIFIED BY 'skift-mig';
GRANT ALL ON studie247.* TO 'studie247'@'localhost';
FLUSH PRIVILEGES;
SQL

# 3) Installer WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar && sudo mv wp-cli.phar /usr/local/bin/wp

# 4) Hent WordPress
sudo mkdir -p /var/www/studie247 && sudo chown -R $USER:www-data /var/www/studie247
cd /var/www/studie247
wp core download --locale=da_DK
wp config create --dbname=studie247 --dbuser=studie247 --dbpass='skift-mig'
wp core install \
    --url=http://<VPS-IP> \
    --title="Studie 247" \
    --admin_user=admin \
    --admin_password='skift-mig' \
    --admin_email=dig@studie247.dk

# 5) Klon temaet ind
cd wp-content/themes
git clone -b claude/design-moodboard-guide-mBFFi <REPO-URL> studie247
wp theme activate studie247

# 6) Nginx-config — se docs/INSTALL-UBUNTU.md
```

---

## WordPress-opsætning efter installation

Når temaet er aktiveret, gør følgende i WP-admin:

1. **Indstillinger → Permalinks** → vælg "Indlægsnavn" (kritisk for pæne URLs).
2. **Indstillinger → Læsning** → "Forside viser: en statisk side" → opret tom side "Forside" og vælg den.
3. **Udseende → Menuer** → opret menu "Hovedmenu" med:
   - Services (link til `/services/`)
   - Studiet
   - Priser
   - Udlejning
   - Om
   - Kontakt
   - Placér i "Hovedmenu"-position.
4. **Sider** → opret:
   - "Studiet" (Template: Studiet)
   - "Priser" (Template: Priser)
   - "Om" (Template: Om)
   - "Kontakt" (Template: Kontakt)
   - "Udlejning" (Template: Udlejning — placeholder)
   - "Book" (Template: Book)
5. **Services → Tilføj ny** — opret "SoMe-videoer", "Podcast", "Online kursus", "Fotoshoot".

---

## Opret services (CPT)

I WP-admin under **Services → Tilføj ny**:

| Felt | Beskrivelse |
|---|---|
| Titel | fx "Podcast" |
| Undertitel | Kort tagline vist på kortet |
| Ikon | Vælg fra dropdown (video, mic, academic, camera, sparkle) |
| Startpris | fx "fra 1.499 kr" |
| CTA-tekst | fx "Læs mere" |
| Inkluderet | En linje pr. punkt — vises på service-detalje-side |
| Fremhævet billede | 4:3 minimum 800×600px |
| Indhold | Fuldt indhold til service-detalje-side |

Nye services dukker automatisk op på forsiden og i `/services/`-oversigten — **ingen kode skal redigeres**.

---

## Brand guide

### Farver

| Rolle | Hex | Navn |
|---|---|---|
| Primær | `#9E2B25` | Brown Red |
| Baggrund | `#F4E9DD` | Bone |
| Kanter / kontrast | `#282828` | Old Black |

Alle farver kan bruges med 20% opacity til overlays (findes som `--s247-red-20` osv.).

### Typografi

- **Aileron** — sans-serif, body + Overskrift 1 (1. ord). Hent gratis på [fontsquirrel.com](https://fontsquirrel.com/fonts/aileron) og læg `.woff2`-filer i `assets/fonts/aileron/`.
- **Migra** — italic serif, Overskrift 2 (2. ord, altid kursiv). Kommerciel licens købes hos [pangrampangram.com](https://pangrampangram.com/products/migra). Læg `.woff2`-filer i `assets/fonts/migra/`.
- **Fallback** indtil fonts er på plads: Playfair Display Italic (Google Fonts, loades automatisk).

### Logo

- Luft omkring logoet = **20%** af logoets bredde (padding i CSS matches).
- Høj kontrast mellem lys/mørk: rød på sort, bone på rød, rød på bone.

---

## SEO features

Indbygget — ingen plugin kræves:

- ✅ `<title>` via `title-tag` theme support
- ✅ Meta description (auto fra excerpt/content)
- ✅ Canonical URL
- ✅ Open Graph (type, title, description, url, image, site_name, locale)
- ✅ Twitter Card (summary_large_image)
- ✅ JSON-LD: **LocalBusiness**, **Service**, **BreadcrumbList**, **FAQPage**
- ✅ Semantisk HTML5 (header, main, nav, article, section, footer)
- ✅ Responsivt billeder (`srcset` via `wp_get_attachment_image`)
- ✅ `loading="lazy"` default, `eager` + `fetchpriority="high"` på hero
- ✅ `theme-color` meta
- ✅ Skip link (a11y)
- ✅ `prefers-reduced-motion` support
- ✅ Fjernet WP-version + emoji-bloat fra `<head>`

**Anbefalede ekstra-plugins** (valgfrit):
- **Rank Math** eller **Yoast** — hvis du vil redigere meta pr. side fra admin (vores fallback virker ellers fint).
- **WP Super Cache** / **LiteSpeed Cache** — server-side caching.
- **EWWW Image Optimizer** — automatisk WebP-konvertering.

---

## Roadmap

### Fase 1 ✅ (nu)
- Tema-skelet, CSS, alle statiske sider, Services CPT, SEO, placeholdere

### Fase 2 — Indhold
- Upload hero-billede/video, studio-billeder, logo
- Opret de 4 første services med rigtige tekster
- Upload cases og testimonials

### Fase 3 — Custom Booking System
- Booking-kalender (dato/tid pr. studie)
- Service-valg, tillæg, betaling (fx Stripe/MobilePay)
- Admin-oversigt, email-bekræftelser
- iCal export

### Fase 4 — Udstyrs-udlejning
- Opret udstyr i CPT `udlejning_item` (kamera, lys, lyd, grip)
- Katalog-side med filter pr. kategori
- Lejeperiode-booking
- Integration med studie-booking

### Fase 5 — Optimering
- Performance-audit (Core Web Vitals)
- A/B-tests på hero og CTAs
- Blog til SEO long-tail

---

## Deployment (domæne senere)

Når domænet er klar:

```bash
# 1) Peg A-record mod VPS-IP
# 2) Installer Let's Encrypt
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d studie247.dk -d www.studie247.dk

# 3) Skift URLs i DB
cd /var/www/studie247
wp search-replace 'http://<gammel-ip>' 'https://studie247.dk' --all-tables
wp option update siteurl 'https://studie247.dk'
wp option update home 'https://studie247.dk'

# 4) Ryd cache
wp cache flush
```

---

## Udvikling

### Branch
Udvikling sker på `claude/design-moodboard-guide-mBFFi`.

### Test lokalt
```bash
cd /var/www/studie247
wp theme activate studie247
wp server --host=0.0.0.0 --port=8080   # eller brug Nginx
```

### Kodestil
- PHP: WordPress Coding Standards (tabs, snake_case)
- CSS: BEM-inspireret, design tokens i `:root`
- JS: Vanilla, IIFE, ingen framework

---

## Licens

Proprietær — alle rettigheder til Studie 247.
