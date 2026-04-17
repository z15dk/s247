# Logo-filer

Læg dine logo-filer her — temaet samler dem automatisk op.

## Filnavne (brug SVG hvor muligt, PNG som backup)

| Fil                          | Bruges hvor                                 |
|------------------------------|---------------------------------------------|
| `studie247-main.svg`         | Default horizontal, mørk (Old Black)        |
| `studie247-main-red.svg`     | Horizontal, rød (Brown Red)                 |
| `studie247-main-bone.svg`    | Horizontal, bone — til mørk baggrund        |
| `studie247-stacked.svg`      | Stacked (Main Logo) rød                     |
| `studie247-symbol.svg`       | Kun 247-mærket, mørk                        |
| `studie247-symbol-red.svg`   | Kun 247-mærket, rød                         |
| `studie247-symbol-bone.svg`  | Kun 247-mærket, bone — til mørk baggrund    |

## Hvor de bruges i sitet

- **Hero (top venstre):** `studie247-main` (mørk på bone-baggrund)
- **Floating pill-header:** `studie247-main-bone` (lys på mørk pill)
- **Footer:** `studie247-main-bone`
- **Favicon:** `studie247-symbol` (kan uploades separat via WP Customize)

## To måder at få dem ind

### A) Via WordPress admin (nemmest)

SVG-uploads er nu tilladt i temaet. Gå til:

1. **Medier → Tilføj ny** og drop dine `.svg`-filer
2. **Udseende → Tilpas → Webstedsidentitet → Logo** og vælg hovedlogoet

Hvis du stadig får fejl ved upload, tjek:
- Filen er under 64 MB (se `upload_max_filesize` i php.ini)
- Mappen `wp-content/uploads/` er skrivbar (`chown -R www-data:www-data`)

### B) Direkte i temaet (versioneres med i git)

SCP eller SFTP filerne til:

```
/var/www/studie247/wp-content/themes/studie247/assets/images/logo/
```

Sæt rettigheder:

```bash
chown -R www-data:www-data /var/www/studie247/wp-content/themes/studie247/assets/images/logo/
```

Bagefter committer vi dem ind i git så de følger med fremtidige deploys.
