# Audit-Plan kontor (365CMS 4.2.0)

> **PR:** 2 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Kanzlei/Beratung/Dienstleister (Leistungen, Prozess, Testimonials, FAQ, CTA) |
| Dateien | 31 (4.020 Zeilen PHP, 176 Zeilen JS) |
| Header öffnet `<main>` | ja |
| Dark Mode | nein |
| Eigene Vorlagen | `blog.php`, `author.php`, `category.php`, `tag.php` |
| Customizer | eigener Admin-Customizer `admin/customizer.php` (456 Z., gleiche Basis wie feder/kompass/rundschau) mit 17 Bereichen |
| Besonderheit | zentrale Linkprüfung `kontor_button()`, `kontor_highlight()` (Hervorhebung in Überschriften), Teilen (LinkedIn, XING) |

## 2. Angriffsfläche

- Admin-Customizer: `admin/customizer.php:230–280` (CSRF, `isAdmin()`, `$_POST`, `$_FILES`).
- SQL: `functions.php:707–750` (`kontor_get_posts`, `kontor_get_categories`).
- Request-Daten: `blog.php:50`, `functions.php:494/830/958/988–990`.
- `kontor_highlight()`: erzeugt HTML aus Customizer-Text (Markierungssyntax) → XSS-Prüfung.
- Social-Links in Header/Footer (`header.php:76`, `footer.php:63`).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S4/S5/S6/S8 | Customizer: CSRF, `isAdmin()`, Upload, Array-Eingaben, Feldvalidierung | `admin/customizer.php` |
| S7 | Prepared Statements, Publikationsfilter, Integer-Grenzen, Kategorie-Zählung | `functions.php:700–760` |
| S1 | `kontor_highlight()`: zuerst escapen, dann markieren? | `functions.php` |
| S1 | `$content`, `$count`, `$serviceNav`, `$headingTag` (Allowlist?) | `blog-single.php:79`, `search.php:28`, `footer.php:83`, `functions.php:1083` |
| S2 | `kontor_safe_url()`, `kontor_button()` (extern → `rel`), Social-URLs | `functions.php:960–980` |
| S3 | `kontor_css_color/_number`, eigenes CSS | `functions.php` |
| S10 | `theme.js` (79 Z.), `customizer-admin.js` | `js/` |
| B1/B2 | `kontor_format_date()`, leere Leistungen/FAQ/Testimonials, `kontor_lines()` | |
| B5 | Archive, Paginierung, unbekannte Kategorie/Tag/Autor | `blog.php`, `*.php` |
| P1 | Startseite: News + Kategorien ohne Mehrfachabfragen | `home.php`, `functions.php` |
| P3/P4 | Bilder/Logo mit Maßen, `defer` | |
| Q3 | Fokusrahmen (4.0-Fix Tinte) verifizieren, Sticky-Header, Zielgrößen | `style.css` |
| K2/K3 | Kopf-CTA/Kontolinks, `/forum` in Fallback-Menüs/Buttons | `header.php`, `kontor_default_menu()` |
| K4/K5 | Token-Brücke, Header-`<main>` | |
| K8/K9 | POST-Formulare, Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- SQL nur gegen Stubs geprüft.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde

_Wird nach dem Audit ausgefüllt._
