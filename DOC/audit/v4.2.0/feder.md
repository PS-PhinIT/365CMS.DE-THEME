# Audit-Plan feder (365CMS 4.2.0)

> **PR:** 1 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Persönlicher Blog/Autorenseite |
| Dateien | 31 (3.995 Zeilen PHP, 282 Zeilen JS) |
| Header öffnet `<main>` | ja |
| Dark Mode | **ja** (`data-theme`, `prefers-color-scheme`, `localStorage`) |
| Eigene Vorlagen | `blog.php`, `author.php`, `category.php`, `tag.php` |
| Customizer | **eigener Admin-Customizer** `admin/customizer.php` (456 Z.) mit Upload, `css/customizer-admin.css`, `js/customizer-admin.js` |
| Besonderheit | eigene SQL-Abfragen (Beiträge, Kategorien mit Zählung), Teilen-Links, Lesezeit, verwandte Beiträge |

## 2. Angriffsfläche

- **Admin-Customizer** (`admin/customizer.php`): `isAdmin()`-Prüfung (Z. 230), CSRF (Z. 256), `$_POST`/`$_FILES` (Z. 252–278), `feder_customizer_store_upload()`, Tab-Parameter (Z. 244), Redirect (Z. 231).
- **SQL** in `functions.php` (Z. 759–830): `feder_get_posts()`, `feder_count_posts()`, `feder_get_categories()`, `feder_adjacent_posts()`, `feder_related_posts()`.
- Request-Daten: `functions.php:546/956/1090–1092`, `blog.php:51`.
- JS: `js/theme.js` (Farbschema, Lesefortschritt), `localStorage` (Z. 117).
- Teilen-Links (LinkedIn, Bluesky) in `blog-single.php:56–57/114`.

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S4/S5 | Customizer: CSRF für Speichern **und** Zurücksetzen, `isAdmin()` vor jeder Ausgabe/Aktion, Direktaufruf | `admin/customizer.php:225–290` |
| S6 | Upload: Endung + `finfo`-MIME, SVG, Größe, Dateiname, `is_uploaded_file`, Zielverzeichnis | `feder_customizer_store_upload()` |
| S8 | `$_POST[$input]` als Array → `(string)`-Cast/TypeError; Feldtyp-Validierung (Farbe, Zahl, URL, Auswahl) beim Speichern | `admin/customizer.php:270–280` |
| S7 | Alle Abfragen prepared, Publikationsfilter `cms_post_publication_where()`, `LIMIT/OFFSET` als Integer, Kategorie-/Tag-Slugs gebunden | `functions.php:740–840` |
| S1 | Rohe Ausgaben `$content`, `$count`, `$currentPage`, `$coreItems` | `blog-single.php:96`, `search.php:28`, `blog.php:67/98`, `functions.php:988` |
| S2 | `feder_safe_url()`, Social-Links, Teilen-URLs (`rawurlencode`) | `functions.php`, `home.php:64`, `footer.php:43` |
| S3 | `feder_css_color/_number`, `feder_sanitize_custom_css()` | `functions.php` |
| S10 | `theme.js`: `localStorage`-Wert nur `light`/`dark`/`auto` | `js/theme.js:100–125` |
| S10 | `customizer-admin.js`: DOM-Senken bei Vorschau | `js/customizer-admin.js` |
| B1 | `feder_format_date()` mit ungültigen Daten, `feder_reading_time()` mit leerem Inhalt | `functions.php` |
| B4 | `slug_en`/Locale in Links (`feder_post_link`) | `functions.php` |
| B5 | `blog.php`/Archive: `page`-Grenzen, leere Kategorie, unbekannter Autor/Tag → 404? | `blog.php`, `author.php`, `category.php`, `tag.php` |
| P1 | Abfragen je Seite (Startseite, Artikel mit Nachbarn/verwandten Beiträgen), Request-Cache | `functions.php` |
| P3/P4 | Farbschema ohne Aufblitzen (Script-Position), Bildmaße, LCP | `header.php`, `js/theme.js` |
| Q3 | Dark-Mode-Kontraste, Fokus, Zielgrößen Teilen-Knöpfe | `style.css` |
| K2 | Kontolinks/Moderator | `header.php` |
| K3 | Navigation/Fallback-Menü `/forum` | `feder_default_menu()` |
| K4 | Präfix `fdr-` vs. Community-Tokens; Dark-Mode-Werte in die Brücke (Member-Shell bleibt hell) | `style.css`, `css/cms-v4.css` |
| K5 | Header-`<main>` | |
| K6 | Slug-/Anker-Erzeugung im Theme? | `functions.php` |
| K8/K9 | Suchformular GET; Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- Neue SQL-Abfragen nur gegen Stubs geprüft (`ONLY_FULL_GROUP_BY`) → Abfragen erneut lesen.
- Member-Shell bewusst nur hell.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde

_Wird nach dem Audit ausgefüllt._
