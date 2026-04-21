# Flyt Studie 247 til Nordicway via SSH

Komplet SSH-baseret deploy. Forudsætter at Nordicway har givet dig
SSH-adgang (står typisk i velkomstmailen eller under "SSH Access" i
cPanel).

Alle kommandoer kører på Nordicway-serveren med mindre andet er angivet.

---

## 0. Forudsætninger

Login-oplysninger du skal bruge:

- SSH-bruger, host og port fra Nordicway
- Domænenavn — peger allerede på serveren (DNS kan tage 1-24 timer)
- MySQL-database + bruger oprettet i kontrolpanelet

**Opret database først** via cPanel → MySQL Databases:

- Database: `studie247_db`
- Bruger: `studie247_user` + stærk password
- Tilknyt brugeren med ALL PRIVILEGES

Skriv værdierne ned.

---

## 1. Log ind på Nordicway via SSH

På din Mac:

```bash
ssh -p <port> <bruger>@<ssh-host>
```

(Nordicway bruger ofte port 22022 for shared hosting — se deres guide.)

---

## 2. Find rigtige sti til dit domæne

```bash
cd ~
ls -la
```

På cPanel-hosting er det typisk `~/public_html/` for primærdomænet,
eller `~/domains/<dit-domæne>/public_html/` hvis det er et addon-
domæne. Fortsæt i den rigtige mappe:

```bash
cd ~/public_html    # tilpas hvis addon-domæne
```

---

## 3. Installér WordPress via WP-CLI

Nordicway har oftest WP-CLI forinstalleret. Tjek:

```bash
wp --info
```

Hvis det virker:

```bash
# Download WP-core
wp core download --locale=da_DK

# Opret wp-config.php med dine DB-credentials
wp config create \
  --dbname=studie247_db \
  --dbuser=studie247_user \
  --dbpass='<din-db-adgangskode>' \
  --dbhost=localhost \
  --locale=da_DK

# Kør installationen
wp core install \
  --url="https://<dit-domæne>.dk" \
  --title="Studie 247" \
  --admin_user=rune \
  --admin_password='<stærk-admin-kode>' \
  --admin_email=rune@z15.dk
```

Hvis WP-CLI **ikke** er tilgængelig — brug Nordicways
WordPress-installer i cPanel i stedet og vend tilbage hertil.

---

## 4. Clone temaet direkte fra GitHub

```bash
cd wp-content/themes
git clone https://github.com/z15dk/s247.git studie247
cd studie247
git checkout claude/fix-batch-network-error-dVmYs
```

---

## 5. Aktivér temaet + migrations

```bash
cd ~/public_html
wp theme activate studie247
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard
```

Migrations (team-bios, dashboard-side, demo-service) kører automatisk
ved første request. Tving dem i gang med:

```bash
wp eval 'do_action("after_setup_theme");'
```

---

## 6. Tilføj SMTP-konstanter til wp-config.php

Find SMTP-oplysningerne i cPanel → **Email Accounts** →
**Connect Devices** / **Configure Mail Client**.

```bash
wp config set SMTP_HOST      'smtp.dit-domæne.dk'          --type=constant
wp config set SMTP_PORT      '587'                         --type=constant --raw
wp config set SMTP_SECURE    'tls'                         --type=constant
wp config set SMTP_USER      'noreply@dit-domæne.dk'       --type=constant
wp config set SMTP_PASS      '<smtp-adgangskode>'          --type=constant
wp config set SMTP_FROM      'noreply@dit-domæne.dk'       --type=constant
wp config set SMTP_FROM_NAME 'Studie 247'                  --type=constant
wp config set FORCE_SSL_ADMIN 'true'                       --type=constant --raw
```

Test at mail sender:

```bash
wp eval 'wp_mail("rune@z15.dk","SMTP-test fra Studie 247","Hvis du modtager denne, virker mailen.");'
```

---

## 7. SSL (Let's Encrypt)

Hvis Nordicway ikke automatisk har udstedt certifikat:

```bash
# cPanel AutoSSL (kør fra SSH hvis det er muligt)
uapi --user=<cpanel-bruger> SSL install_ssl domain=<dit-domæne>.dk
```

Eller klik **SSL/TLS Status → Run AutoSSL** i cPanel.

Tving HTTPS ved at tilføje til `.htaccess` i roden (lige under
`# BEGIN WordPress`):

```apache
RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 8. Post-deploy sanity check

```bash
# Tjek at alt virker fra CLI
wp site url              # forventet: https://dit-domæne.dk
wp option get blogname   # forventet: Studie 247
wp theme list --status=active   # forventet: studie247
wp user list
wp post list --post_type=page

# PHP-fejl?
tail -n 50 ~/public_html/wp-content/debug.log 2>/dev/null
```

Åbn i browser:

- [ ] `https://dit-domæne.dk` — forsiden loader
- [ ] `https://dit-domæne.dk/dashboard/` — login + sektioner virker
- [ ] Test-booking → mail ankommer
- [ ] Opret bruger via dashboardet → velkomstmail ankommer
- [ ] Mobil-visning af dashboard (Safari iPhone)

---

## 9. Upload medier via WP-CLI

Hvis du har billederne i en mappe lokalt, upload med scp + importer:

```bash
# på Mac
scp -P <port> -r ~/Desktop/s247-billeder/ <bruger>@<ssh-host>:~/tmp-medier/

# på serveren
cd ~/tmp-medier
for f in *.{jpg,jpeg,png,webp}; do
  wp media import "$f" --path=~/public_html
done
rm -rf ~/tmp-medier
```

Eller upload simpelthen via **Medier → Tilføj nyt** i wp-admin —
det er hurtigere for få hundrede filer.

---

## 10. Backup fra dag 1

```bash
# Installér UpdraftPlus og lad det schedulere til Google Drive
wp plugin install updraftplus --activate
```

Herefter: log ind på wp-admin → **Indstillinger → UpdraftPlus** →
Opsæt Google Drive-kobling → schedulér uge-backup af både database og
filer.

---

## 11. Daglig drift — pull fra GitHub

For fremtidige opdateringer:

```bash
ssh -p <port> <bruger>@<ssh-host>
cd ~/public_html/wp-content/themes/studie247
git pull origin claude/fix-batch-network-error-dVmYs
```

Hvis du vil forenkle kommandoen, tilføj en alias i `~/.bashrc` på
serveren:

```bash
echo "alias s247-update='cd ~/public_html/wp-content/themes/studie247 && git pull origin claude/fix-batch-network-error-dVmYs'" >> ~/.bashrc
source ~/.bashrc
```

Så skriver du bare `s247-update` fremover.

---

## Fejlsøgning

**"This site can't be reached"** → DNS er ikke færdig. Vent 1-24 timer,
eller tjek med `dig <dit-domæne>.dk` om A-record peger på Nordicway.

**502 Bad Gateway / 500 Internal** → tjek `~/public_html/wp-content/debug.log`.
Slå debug til:

```bash
wp config set WP_DEBUG       true --type=constant --raw
wp config set WP_DEBUG_LOG   true --type=constant --raw
wp config set WP_DEBUG_DISPLAY false --type=constant --raw
```

**Mail sender ikke** → SMTP-credentials forkerte. Tjek port (587=TLS,
465=SSL). Bekræft med:

```bash
wp eval 'wp_mail("rune@z15.dk","test","test");' --debug
```

**Billeder loader ikke** → fil-permissions:

```bash
cd ~/public_html
find wp-content/uploads -type d -exec chmod 755 {} \;
find wp-content/uploads -type f -exec chmod 644 {} \;
```

---

## Hvis SSH/WP-CLI ikke er tilgængelig

Nordicway har forskellige produktniveauer. Hvis du kun har FTP/cPanel,
se `docs/DEPLOY-NORDICWAY.md` for FTP-baseret flow.
