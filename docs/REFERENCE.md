# Studie 247 — Reference & datamodel

Ét samlet dokument til hvor alting ligger: hvilke data der gemmes, hvordan dashboardet/CRM henter dem, hvor man retter mails og skabeloner, og hvordan sitet flyttes mellem servere.

---

## 1. Overblik — hvad der ligger hvor

| Funktion | Fil / placering | Hvad gør det |
|---|---|---|
| Hovedmenu | `header.php`, `inc/menus.php` | Navigation øverst + fallback når WP-menu ikke er sat |
| Forsiden | `front-page.php` + `template-parts/section-*.php` | Hero, services, cases, studie, process, osv. |
| Udstyrs-arkiv | `archive-udlejning_item.php` + `template-parts/product-card.php` | `/udlejning/` listen med filtre |
| Udstyrs-detalje | `single-udlejning_item.php` | Produkt-side + "Send forespørgsel", relaterede produkter |
| Booking/forespørgsel | `page-booking-studie.php` | Kalender + formål + varighed + pris-beregning |
| Godkendelse | `inc/booking-approval.php` | Godkend/afvis i wp-admin, sender mail |
| CSV-import | `inc/udlejning-import.php` | Masse-opret udstyr via CSV + ZIP + Drive-links |
| Kontakt-form (A/B) | `page-kontakt.php` | "Vælg din side"-form |
| Kontakt-flow | `page-kontakt-os.php` | 3-trin formular |
| Mails | `inc/mail.php`, `inc/mail-templates.php` | SMTP + redigerbare skabeloner |
| REST-API | `inc/rest-api.php` | Eksternt dashboard/CRM |
| GDPR-samtykke | `inc/template-tags.php` → `studie247_consent_field()` | Checkbox + bevis-lagring |

---

## 2. Deployment — flytning mellem servere

På ny server:

```bash
# 1. Klon tema-repo ind i wp-content/themes
cd /var/www/dit-site/wp-content/themes
git clone -b claude/fix-batch-network-error-dVmYs https://github.com/z15dk/s247.git studie247
chown -R www-data:www-data studie247

# 2. Aktivér temaet i wp-admin → Udseende → Temaer

# 3. Tilføj dine wp-config.php-konstanter (se §3 nedenfor)

# 4. Genindlæs rewrite-regler: wp-admin → Indstillinger → Permalinks → Gem
```

Efter hver kode-opdatering:

```bash
cd /var/www/dit-site/wp-content/themes/studie247
git pull origin claude/fix-batch-network-error-dVmYs
```

CSS/JS har filemtime-baseret cache-busting, så browsere henter friske versioner automatisk. Ingen `?ver=`-bump nødvendig.

---

## 3. wp-config.php — alle konstanter

Tilføj før linjen `/* That's all, stop editing! */`:

```php
/* ─── Studie 247: SMTP (mail-udbyder) ─── */
define( 'S247_SMTP_HOST',      'smtp.nordicway.dk' );   // fra Nordicway
define( 'S247_SMTP_PORT',      587 );                   // 587 (tls) eller 465 (ssl)
define( 'S247_SMTP_USER',      'info@s247.dk' );
define( 'S247_SMTP_PASS',      'din-mail-password' );
define( 'S247_SMTP_SECURE',    'tls' );                 // 'tls', 'ssl' eller ''
define( 'S247_MAIL_FROM',      'info@s247.dk' );
define( 'S247_MAIL_FROM_NAME', 'Studie 247' );
```

Hvis `S247_SMTP_HOST` ikke sættes, falder `wp_mail()` tilbage til PHP's indbyggede `mail()` — virker måske, men leverer ofte i spam.

**Test**: wp-admin → Værktøjer → **Mail-test**. Viser aktuel config (uden password) og sender en test-mail.

---

## 4. Brugere og rettigheder til dashboard-API

1. Gå til **wp-admin → Brugere → Tilføj ny**
2. Opret fx `dashboard-api` med rolle **Redaktør**
3. Klik brugerens navn → rul ned til **Adgangskoder til applikationer**
4. Skriv fx "CRM Dashboard" og klik Tilføj ny
5. Kopier de 4×6 tegn (fx `abcd efgh ijkl mnop qrst uvwx`) — vises **kun én gang**
6. Gem dem sikkert i dit dashboard/CRM

Disse bruges som HTTP Basic Auth:
```
Authorization: Basic base64(dashboard-api:abcdefghijklmnopqrstuvwx)
```

---

## 5. REST-API — alle endpoints

Base-URL: `https://s247.dk/wp-json/wp/v2/`

### 5.1 Bookinger (studie + udlejning) — **kræver auth**

```
GET /booking?per_page=100&orderby=date&order=desc
GET /booking/{id}
```

Status-filtrering:
```
GET /booking?status=pending        # afventer godkendelse
GET /booking?status=publish        # godkendte
GET /booking?status=trash          # afvist/aflyst
```

Eksempel-response (shortened):
```json
{
  "id": 142,
  "date": "2026-04-22T10:00:00",
  "status": "pending",
  "title": { "rendered": "Lars Hansen — 2026-05-10 10:00" },
  "meta": {
    "_s247_date": "2026-05-10",
    "_s247_start": "10:00",
    "_s247_duration": "3 dage",
    "_s247_name": "Lars Hansen",
    "_s247_email": "lars@example.dk",
    "_s247_phone": "+45 12345678",
    "_s247_company": "Acme ApS",
    "_s247_cvr": "12345678",
    "_s247_notes": "Henter selv kl. 10.",
    "_s247_produkt": "sony-fs6",
    "_s247_produkt_id": 87,
    "_s247_type": "",
    "_s247_estimated_price": 4497,
    "_s247_use_type": "",
    "_s247_edit_type": "",
    "_s247_podcast_type": "",
    "_s247_tilkoeb": "",
    "_s247_video_count": 0,
    "_s247_video_duration": 0,
    "_s247_format": "",
    "_s247_consent_timestamp": "2026-04-19 14:22:31",
    "_s247_consent_ip": "188.182.254.192",
    "_s247_consent_ua": "Mozilla/5.0 ..."
  }
}
```

**Studie-booking** (ingen produkt_id) har desuden udfyldt `_s247_use_type` (Podcast, Kursusvideo, …), `_s247_edit_type` (Redigering / Kun filerne), samt eventuelt `_s247_podcast_type`, `_s247_tilkoeb`, `_s247_video_count`, `_s247_video_duration`, `_s247_format`.

### 5.2 Kontakt-beskeder — **kræver auth**

```
GET /kontakt_besked?per_page=100&orderby=date&order=desc
```

Hver besked har:
```json
{
  "id": 301,
  "title": { "rendered": "Mette Jensen — Udstyrs-leje" },
  "content": { "rendered": "Kan I levere til Aarhus?" },
  "date": "2026-04-19T09:15:00",
  "meta": {
    "_s247_name": "Mette Jensen",
    "_s247_email": "mette@example.dk",
    "_s247_phone": "+45 87654321",
    "_s247_topic": "Udstyrs-leje",
    "_s247_side": "",
    "_s247_message": "Kan I levere til Aarhus?",
    "_s247_source": "kontakt-os",
    "_s247_consent_timestamp": "2026-04-19 09:15:00",
    "_s247_consent_ip": "...",
    "_s247_consent_ua": "..."
  }
}
```

`_s247_source` er enten `"kontakt"` (fra /kontakt/) eller `"kontakt-os"` (fra /kontakt-os/).

### 5.3 Udstyr/varer — **offentlig læsning + auth-låst stats**

```
GET /udlejning_item?per_page=100
GET /udlejning_item/{id}
```

```json
{
  "id": 87,
  "title": { "rendered": "Sony FS6" },
  "slug": "sony-fs6",
  "status": "publish",
  "meta": {
    "_s247_pris_dag": "1.499 kr",
    "_s247_pris_uge": "5.999 kr",
    "_s247_deposit": "10.000 kr",
    "_s247_sku": "CAM-FS6",
    "_s247_in_stock": "1",
    "_s247_antal": 2,
    "_s247_ejer": "Studie 247",
    "_s247_serienummer": "SN-12345",
    "_s247_product_uid": "S247-00042",
    "_s247_state_images": "145,146,147"
  },
  "rental_stats": {
    "total_inquiries": 12,
    "pending_inquiries": 1,
    "approved_rentals": 9,
    "rejected_inquiries": 2,
    "active_rentals": 1,
    "upcoming_rentals": 2,
    "completed_rentals": 6,
    "total_earnings": 38493,
    "rental_history": [
      {
        "id": 142,
        "state": "upcoming",
        "start_date": "2026-05-10",
        "end_date": "2026-05-12",
        "duration": "3 dage",
        "price": 4497,
        "customer": "Lars Hansen",
        "email": "lars@example.dk",
        "company": "Acme ApS",
        "cvr": "12345678"
      }
    ]
  }
}
```

`rental_stats` vises kun for auth'ede requests. Uautentificerede kald får `rental_stats: null`.

### 5.4 Skrive/opdatere data fra dashboardet

Alle endpoints understøtter også `POST`/`PATCH`/`DELETE` når brugeren er auth'et med `edit_posts`-cap.

**Opret ny booking manuelt** (fx fra CRM):
```bash
curl -u dashboard-api:'APP_PASSWORD' \
  -X POST https://s247.dk/wp-json/wp/v2/booking \
  -H 'Content-Type: application/json' \
  -d '{
    "title":"Manuel booking — Lars",
    "status":"pending",
    "meta":{
      "_s247_date":"2026-06-01",
      "_s247_start":"10:00",
      "_s247_duration":"6 timer",
      "_s247_name":"Lars Hansen",
      "_s247_email":"lars@example.dk",
      "_s247_phone":"+4512345678"
    }
  }'
```

**Opdater felter på eksisterende booking**:
```bash
curl -u dashboard-api:'APP_PASSWORD' \
  -X PATCH https://s247.dk/wp-json/wp/v2/booking/142 \
  -H 'Content-Type: application/json' \
  -d '{ "meta": { "_s247_notes": "Ny note tilføjet fra CRM" } }'
```

**Slet en booking** (`force=true` = permanent, ellers flyttes til papirkurv):
```bash
curl -u dashboard-api:'APP_PASSWORD' \
  -X DELETE 'https://s247.dk/wp-json/wp/v2/booking/142?force=true'
```

**Godkend en booking** (genvej — sender også bekræftelses-mail automatisk):
```bash
curl -u dashboard-api:'APP_PASSWORD' \
  -X POST https://s247.dk/wp-json/s247/v1/booking/142/approve
```

**Afvis en booking** (genvej — sender afvisnings-mail):
```bash
curl -u dashboard-api:'APP_PASSWORD' \
  -X POST https://s247.dk/wp-json/s247/v1/booking/142/reject
```

**Markér som intern brug** (nulstiller pris, beholder booking ellers):
```bash
# Toggle
curl -u dashboard-api:'APP_PASSWORD' \
  -X POST https://s247.dk/wp-json/s247/v1/booking/142/internal

# Eller eksplicit
curl -u dashboard-api:'APP_PASSWORD' \
  -X POST https://s247.dk/wp-json/s247/v1/booking/142/internal \
  -H 'Content-Type: application/json' \
  -d '{ "internal": true }'
```
Response: `{ "id": 142, "internal": true, "estimated_price": 0, "original_price": 1500 }`.
Oprindelig pris gemmes i `_s247_estimated_price_original` og gendannes automatisk når intern-state sættes af igen. Admin-knappen i wp-admin bruger samme helper — WP og dashboard er altid i sync. Hvis dashboardet i stedet PATCH-er `_s247_internal` direkte på booking-objektet, spejler en `updated_post_meta`-hook automatisk logikken.

Samme skrive-pattern virker på `/kontakt_besked` og `/udlejning_item` — fx tilføj et tag/kategori, redigér pris, eller markér en besked som læst ved at opdatere et valgfrit meta-felt.

### 5.5 Eksempel — curl til test

```bash
# Auth-test (kontakt-beskeder)
curl -u dashboard-api:'abcdefghijklmnopqrstuvwx' \
  https://s247.dk/wp-json/wp/v2/kontakt_besked?per_page=5

# Produkt-stats
curl -u dashboard-api:'abcdefghijklmnopqrstuvwx' \
  https://s247.dk/wp-json/wp/v2/udlejning_item/87
```

---

## 6. Datamodel — alle CPT'er + meta-felter

### `udlejning_item` (udstyr/varer)

| Meta-key | Type | Beskrivelse |
|---|---|---|
| `_s247_pris_dag` | string | Fx "1.499 kr" |
| `_s247_pris_uge` | string | Fx "5.999 kr" |
| `_s247_deposit` | string | Depositum |
| `_s247_sku` | string | Vare-nr (bruges til CSV-match) |
| `_s247_in_stock` | string | "1" eller "0" |
| `_s247_antal` | integer | Lager-mængde (styrer kalender-blokering) |
| `_s247_ejer` | string | Udstyrs-ejer (internt) |
| `_s247_serienummer` | string | Internt |
| `_s247_product_uid` | string | Auto-genereret S247-xxxx |
| `_s247_state_images` | string | Komma-separerede attachment-IDs til tilstands-dokumentation |

Taxonomy: `udlejning_kategori` (hierarkisk).

### `booking` (bookinger + lejeforespørgsler)

| Meta-key | Type | Beskrivelse |
|---|---|---|
| `_s247_date` | string (YYYY-MM-DD) | Dato/start-dato |
| `_s247_start` | string (HH:MM) | Start-tid (for udlejning default "10:00") |
| `_s247_duration` | string | "6 timer", "12 timer", "1 dag", "2 dage", "1 uge", "2 uger" |
| `_s247_name` | string | Kundens navn |
| `_s247_email` | string | |
| `_s247_phone` | string | |
| `_s247_company` | string | Virksomhedsnavn (valgfrit) |
| `_s247_cvr` | string | 8 cifre (valgfrit) |
| `_s247_notes` | string | Kundens fritekst-noter |
| `_s247_produkt` | string | Produkt-slug (tom for studie-booking) |
| `_s247_produkt_id` | integer | Produkt-ID (tom for studie-booking) |
| `_s247_type` | string | Legacy type-felt |
| `_s247_estimated_price` | integer | Beregnet pris i DKK (udlejning) |
| `_s247_use_type` | string | Studie-formål: podcast/kursusvideo/undervisningsvideo/some-content/annonce-video |
| `_s247_edit_type` | string | "redigering" / "kun-filer" |
| `_s247_podcast_type` | string | "lyd" / "video" (kun ved podcast) |
| `_s247_tilkoeb` | string | "jingle-standard" / "jingle-skraeddersyet" |
| `_s247_video_count` | integer | Antal videoer (1-20) |
| `_s247_video_duration` | integer | Min pr. video (1-30) |
| `_s247_format` | string | "16:9"/"9:16"/"4:5"/"1:1" (SoMe) |
| `_s247_consent_timestamp` | string | Tidspunkt for GDPR-samtykke (mysql-format) |
| `_s247_consent_ip` | string | IP ved samtykke |
| `_s247_consent_ua` | string | Browser/user-agent ved samtykke |

**Post-status mapping** (booking-godkendelse):
- `pending` → afventer admin-godkendelse
- `publish` → godkendt, sender bekræftelses-mail
- `trash` → afvist/aflyst, sender aflysnings-mail

### `kontakt_besked` (indkomne kontakt-beskeder)

| Meta-key | Type | Beskrivelse |
|---|---|---|
| `_s247_name` | string | |
| `_s247_email` | string | |
| `_s247_phone` | string | |
| `_s247_topic` | string | Fra /kontakt-os/: Booking / Priser / Udstyrs-leje / Samarbejde / Andet |
| `_s247_side` | string | Fra /kontakt/: "A" / "B" |
| `_s247_message` | string | Beskedens indhold |
| `_s247_source` | string | "kontakt" / "kontakt-os" |
| `_s247_consent_timestamp` | string | |
| `_s247_consent_ip` | string | |
| `_s247_consent_ua` | string | |

---

## 7. Mail-skabeloner — wp-admin → Værktøjer → Mail-skabeloner

Tre automatiske mails sendes til kunder:

| Skabelon-nøgle | Hvornår sendes | Default subject |
|---|---|---|
| `contact` | Når kontakt-formen indsendes | "Tak for din henvendelse — Studie 247" |
| `booking_received` | Når booking/udlejning indsendes | "Tak for din booking af {booking_label} — Studie 247" |
| `booking_approved` | Når admin klikker Godkend | "Din booking er godkendt — Studie 247" |

**Shortcodes** virker i både subject og body. Linjer hvor en shortcode er tom fjernes automatisk — så "Produkt: {produkt}" forsvinder for studie-bookinger uden produkt.

Alle tilgængelige shortcodes pr. skabelon listes på admin-siden og kan klikkes for at kopieres.

**Nulstil**-knappen pr. skabelon sletter brugerens ændringer og falder tilbage til standard-teksten i koden.

---

## 8. CSV-import af udstyr

`wp-admin → Udlejning → Importér CSV`.

- Upload **CSV** direkte, eller **ZIP** med CSV + billed-mappe
- Kolonner: `title, excerpt, content, pris_dag, pris_uge, deposit, sku, in_stock, kategori, image_url, image_file, state_image_1..4, state_url_1..4`
- Kun `title` er påkrævet
- Matcher eksisterende produkter via SKU → title og opdaterer i stedet for at duplikere
- Understøtter Google Drive share-links i `image_url` og `state_url_*` (auto-rewrites til direkte hotlink). Filer skal være delt som "Alle med linket"
- Henter billeder i baggrunden: 1 række pr. batch når billeder er slået til (15 sek timeout pr. URL)

Hent skabelon via "Hent skabelon"-knappen.

---

## 9. Booking-flow

### Studie-booking (`/booking-studie/`)

1. Bruger vælger **Formål**: Podcast / Kursusvideo / Undervisningsvideo / SoMe / Annonce-video
2. Afslør betingede felter (edit-type → jingle / video-detaljer / SoMe-format)
3. Vælg dato i kalender
4. Vælg tidspunkt (8-20) + varighed (6 eller 12 timer)
5. Udfyld personlige oplysninger + virksomhed/CVR (valgfrit)
6. Accepter GDPR-samtykke
7. Send → status `pending` → admin godkender i wp-admin

### Udlejning (`/booking-studie/?produkt=<slug>`)

Samme side, men med produkt forudfyldt:
- Varighed: 1/2/3/4 dage, 1/2 uger
- Pris-beregning live:
  - 1 dag = 1× pris_dag
  - 2 dage = 2× pris_dag
  - 3 dage = 3× pris_dag
  - 4 dage = 3.5× pris_dag
  - 1 uge = 1× pris_uge
  - 2 uger = 1.5× pris_uge
- Ingen time-slots (pickup default 10:00)
- Kalender blokerer kun dage hvor **alle** `_s247_antal` enheder er udlejet via godkendte bookinger

---

## 10. GDPR — hvad gemmes

For hver indkommen besked/booking gemmes:
- `_s247_consent_timestamp` — præcis tidspunkt for samtykke
- `_s247_consent_ip` — IP brugeren sendte fra
- `_s247_consent_ua` — browser/user-agent

Disse tre felter er juridisk bevis iht. GDPR art. 7 (dokumentér at samtykke blev givet).

**Privatlivspolitikken** linkes fra samtykke-checkboxen. Sæt URL'en via wp-admin → Indstillinger → Privatliv → vælg side, eller opret en side med slug `privatlivspolitik` (fallback).

---

## 11. Hurtig fejlsøgning

| Symptom | Tjek |
|---|---|
| Mails kommer ikke | Værktøjer → Mail-test. Hvis "SMTP aktiv" er grøn og fejl vises → tjek password/host hos Nordicway. Hvis rød → udfyld `S247_SMTP_*`-konstanter i wp-config.php |
| `/udlejning/` viser index.php-layout | Indstillinger → Permalinks → Gem (force-reload af rewrite-regler) |
| REST 401 fra dashboard | Check Application Password er genereret for en bruger med edit_posts-rolle. Brug HTTP Basic Auth format: username:app-password |
| CSV-import hænger på "Klargør batch" | Deaktivér "Hent billeder" — en død URL låser request. Evt. `systemctl reload php8.3-fpm` hvis opcache holder gammel kode |
| Browser henter ikke ny JS/CSS efter git pull | Hard-refresh (Cmd/Ctrl+Shift+R). Filemtime-cache-buster skulle ellers klare det |
| Ny meta-field ikke i REST-response | Tjek at den er registreret i `inc/rest-api.php` under korrekt `$post_type`-nøgle |

---

## 12. Branch-struktur

- `claude/fix-batch-network-error-dVmYs` — aktuel arbejds-branch, indeholder alle funktioner
- `claude/fix-menu-component-Jkt89` — tidligere branch (basis-template)
- `claude/design-moodboard-guide-mBFFi` — tidligere branch

Produktion bør følge `claude/fix-batch-network-error-dVmYs` indtil features merges til `main`.
