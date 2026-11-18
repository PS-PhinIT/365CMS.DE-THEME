# Audit-Plan business (365CMS 4.2.0)

> **PR:** 1 | **Version vor Audit:** 4.0.0 (Kit 1.0.1) | **Ziel:** 4.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Unternehmenspräsentation |
| Dateien | 25 (2.744 Zeilen PHP, 110 Zeilen JS) |
| Header öffnet `<main>` | **ja** (relevant für K5) |
| Dark Mode | nein |
| Eigene Vorlagen | `blog.php` |
| Customizer | Core-Customizer über `theme.json` (9 Bereiche, `biz_hero`, `biz_content`) |
| Besonderheit | eigene Konfigurationsschicht `biz_config()` (liest `theme.json`), `biz_sanitize_content_html()` (seit 4.0 nicht mehr für Core-HTML genutzt) |

## 2. Angriffsfläche

- `blog.php:43–45`: `$_SERVER['REQUEST_URI']`, `$_GET` → Paginierungslinks.
- `functions.php:256`: `file_get_contents($jsonFile)` (theme.json) – Häufigkeit je Request.
- Rohe Ausgaben in `header.php:47/63/70` (`$bodyCls`, `$bizSiteTitle`), `footer.php:79/121` (`$siteTitle`, `$copyText`).
- Menü-HTML `biz_nav_menu()`, `biz_href()`, `biz_safe_headline()` (erlaubtes Inline-HTML in Überschriften).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S1 | `$bodyCls`, `$bizSiteTitle`, `$siteTitle`, `$copyText` wirklich vorher escaped? | `header.php:47/63/70`, `footer.php:79/121` |
| S1 | `biz_safe_headline()`: erlaubte Tags, Attribute entfernt? (Customizer-HTML → XSS durch Admin-Konto/kompromittiertes Konto) | `functions.php` |
| S2 | `biz_href()`/`biz_safe_url()`: Schemata, `//host` | `functions.php` |
| S3 | `biz_css_color/_number`, eigenes CSS (`echo $custom`, Z. 191) | `functions.php` |
| S8 | `blog.php`: `array_filter($_GET, 'is_scalar')` → werden fremde Parameter in Links übernommen (Parameter-Pollution, Cache-Poisoning)? `page` als Array | `blog.php:43–45` |
| Q2 | `biz_sanitize_content_html()` noch benutzt? Sonst entfernen | `functions.php` |
| B3 | `page.php` ohne Titel, `id = 0`; `error.php`-Fragment innerhalb Header-`<main>` | `page.php`, `error.php` |
| B5 | `blog.php` Paginierung | `blog.php:88` |
| P1/P2 | `biz_config()`: theme.json je Request einmal lesen (statischer Cache) | `functions.php:250–260` |
| P3/P4 | Logo mit Maßen (`header.php:63`), Bilder im Blog-Raster lazy | |
| Q3 | Drawer `inert`, Escape, Sticky-Header-Fokus, Zielgrößen | `js/navigation.js`, `style.css` |
| K2 | Kontolinks für `moderator` | `header.php` |
| K3 | Footer-/Fallback-Navigation `/forum` | `footer.php`, `functions.php` |
| K5 | Header-`<main>`: Kit-Wrapper für `community` greift (ID `THEME_V4_MAIN_ID`), dokumentieren | `header.php` |
| K8/K9 | POST-Formulare, Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- Statische Demo-Kennzahlen („200+ Projekte“) – als Designentscheidung belassen, prüfen ob im Customizer abschaltbar.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 4.1.0, Vertragstest und `php -l` grün.

## 6. Befunde

_Wird nach dem Audit ausgefüllt._
