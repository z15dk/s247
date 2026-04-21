# Flyt Studie 247 til Nordicway

Trin-for-trin guide til at få temaet + WordPress over på Nordicway-
hosting med dit nye domæne. Standard delt hosting-setup (cPanel eller
lignende kontrolpanel).

---

## 0. Før du starter — hav disse ting klar

- [ ] Nordicway-konto oprettet + FTP/SFTP-adgang
- [ ] Domænet registreret og peger på Nordicway (eller klar til at pege)
- [ ] Admin-adgang til cPanel/kontrolpanel
- [ ] Din database-eksport fra det gamle setup (`.sql`-fil)
- [ ] Nye medier/billeder klar til upload (originalerne gik tabt — du
      uploader dem via WP Media Library efter deploy)

---

## 1. Opret database på Nordicway

I kontrolpanelet:

1. **MySQL Databases** → Opret ny database, fx `studie247_db`
2. Opret MySQL-bruger `studie247_user` med stærk adgangskode
3. Tilknyt brugeren til databasen med **alle rettigheder**

Skriv disse tre værdier ned — de skal bruges i `wp-config.php`:

```
DB_NAME = studie247_db
DB_USER = studie247_user
DB_PASSWORD = <genereret-adgangskode>
DB_HOST = localhost   (Nordicway default — tjek deres guide)
```

---

## 2. Installér WordPress

Nordicway har oftest en **WordPress-installer** (Softaculous eller
lignende) i kontrolpanelet. Brug den:

1. Vælg domænet
2. Installér WordPress i roden (ikke i en undermappe)
3. Sæt admin-bruger: `rune@z15.dk` / stærk kode
4. Gennemfør installationen

Alternativt manuelt: download WP fra wordpress.org → upload til
`public_html/` via FTP → kør `/wp-admin/install.php`.

---

## 3. Upload temaet via FTP/SFTP

Temaet ligger på GitHub på branchen
`claude/fix-batch-network-error-dVmYs`.

**Fra din Mac:**

```bash
# Klon repoet hvis du ikke har det lokalt
git clone https://github.com/z15dk/s247.git ~/Desktop/studie247-theme
cd ~/Desktop/studie247-theme
git checkout claude/fix-batch-network-error-dVmYs

# Upload med rsync (kræver SFTP) — tilpas sti, server, port
rsync -avz --progress ./ <sftp-bruger>@<sftp-host>:public_html/wp-content/themes/studie247/
```

Eller via FileZilla: træk hele `s247`-mappen ind i
`public_html/wp-content/themes/` og **omdøb den til `studie247`**
(vigtigt — sti skal matche tema-navnet).

---

## 4. Aktivér temaet

1. Log ind på `https://<dit-domæne>/wp-admin/`
2. **Udseende → Temaer** → Aktivér **Studie 247**
3. Tillad at temaets migrations kører (sker automatisk ved første
   besøg — seeder team-bios, dashboard-side, demo-service m.m.)

---

## 5. Opsæt wp-config.php (SMTP + sikkerhed)

Rediger `public_html/wp-config.php` og tilføj **over** linjen
`/* That's all, stop editing! */`:

```php
/* ───────── SMTP-mail via dit mail-udbyder ───────── */
define( 'SMTP_HOST',      'smtp.dit-smtp-host.dk' );
define( 'SMTP_PORT',      587 );
define( 'SMTP_SECURE',    'tls' );
define( 'SMTP_USER',      'noreply@dit-domæne.dk' );
define( 'SMTP_PASS',      'smtp-adgangskode' );
define( 'SMTP_FROM',      'noreply@dit-domæne.dk' );
define( 'SMTP_FROM_NAME', 'Studie 247' );

/* Tving HTTPS for admin + cookies */
define( 'FORCE_SSL_ADMIN', true );

/* Skjul admin-toolbar for ikke-admins (temaet gør det også selv) */
```

Nordicway tilbyder typisk SMTP via dit eget domæne — find
oplysningerne under **Email Accounts** → **Configure Mail Client** i
cPanel.

---

## 6. Tilret URLs

Hvis du tidligere har haft siden liggende på IP'en (fx
`http://178.104.121.60`), skal databasen peges om til det nye domæne.

1. Gå ind i **phpMyAdmin** → vælg databasen
2. I tabellen `wp_options` find rækkerne `siteurl` og `home`
3. Ret begge til `https://dit-domæne.dk`

**Eller via WP-CLI** (hvis Nordicway tillader det):

```bash
wp search-replace 'http://178.104.121.60' 'https://dit-domæne.dk' --all-tables
```

---

## 7. Permalinks + .htaccess

1. Log ind på WP → **Indstillinger → Permalinks**
2. Vælg **Indlægsnavn**
3. Klik **Gem ændringer** (genererer `.htaccess` automatisk)

Tjek at `.htaccess` i roden indeholder standard WP-regler:

```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

---

## 8. SSL-certifikat

Nordicway leverer gratis Let's Encrypt. Aktivér under **SSL/TLS** i
cPanel for både `dit-domæne.dk` og `www.dit-domæne.dk`.

Tjek at siden loader på `https://` uden blandet-indhold-advarsler.

---

## 9. Post-deploy tjek

Gå igennem denne liste på det nye domæne:

- [ ] Forsiden loader
- [ ] `/dashboard/` → login virker, alle sektioner er tilgængelige
- [ ] Opret en test-booking → modtager mail
- [ ] Opret en bruger under Brugere-modulet → velkomstmail ankommer
- [ ] CSV-eksport af kunder virker
- [ ] Månedsrapport-eksport virker
- [ ] Mobil-visning af `/dashboard/` ser korrekt ud
- [ ] Ingen PHP-fejl i `wp-content/debug.log` (hvis `WP_DEBUG_LOG` er tændt)

---

## 10. Medier

Da de originale media-filer ikke er tilgængelige, skal du genuploade:

1. Log ind som admin → **Medier → Tilføj nyt**
2. Upload produktbilleder, team-billeder, hero-billede m.fl.
3. Gå ind på de relevante poster (services, udlejning, customizer) og
   vælg billederne på ny

Temaet henter allerede team-bios automatisk (`studie247_seed_team_bios`)
og demo-servicen "Podcast-produktion" — du skal kun tilføje billeder.

---

## 11. Daglig drift på Nordicway

**Kommende opdateringer fra GitHub** — Nordicway-hosting har ofte SSH
adgang. Du kan så lave samme flow som før:

```bash
ssh <bruger>@<sftp-host>
cd public_html/wp-content/themes/studie247
git pull origin claude/fix-batch-network-error-dVmYs
```

Hvis Nordicway **ikke** tillader git på serveren, er alternativet:

1. Træk opdateringerne på din Mac
2. rsync kun de ændrede filer op

```bash
# på Mac
cd ~/Desktop/studie247-theme
git pull
rsync -avz --progress ./ <sftp-bruger>@<sftp-host>:public_html/wp-content/themes/studie247/ --exclude '.git'
```

---

## 12. Backup-rutine (GØR DET MED DET SAMME)

Så vi aldrig mister medier igen:

1. Aktivér Nordicways **daglige backup** hvis tilgængelig
2. Installér pluginet **UpdraftPlus** → schedulér backup til
   Google Drive / Dropbox ugentligt
3. Backup skal indeholde både **database** og **wp-content/uploads**

---

## Support

Tekniske spørgsmål til temaet — se `docs/REFERENCE.md` i repoet for
komplet API-dokumentation (CPTs, meta-nøgler, REST-endpoints,
permissions-model).
