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

## 6. Befunde (Audit 2026-11-18, umgesetzt in 2.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `kontor_safe_url()` (Menüs, Buttons, Social) und die Customizer-URL-Normalisierung ließen `/\evil.example` als lokalen Pfad durch (Render-Test) | behoben |
| S4/S5/S6/S8 | niedrig | Customizer (gleiche Basis wie feder): `isAdmin()`, CSRF, Feldvalidierung, Upload in Ordnung; `u_x[]` → „Array to string conversion“ | Array-Fall abgewiesen |
| S7 | – | `kontor_get_posts()` mit Platzhaltern und geklemmtem `LIMIT/OFFSET`, Publikationsfilter; Kategoriezählung mit vollständigem `GROUP BY`, Request-Cache | kein Befund |
| S1 | – | `kontor_highlight()`: erst escapen, dann `*…*` → `<em>` (keine Injektion); `$content` Core-HTML; `$serviceNav` escaped; `$headingTag` aus Code | kein Befund |
| S2 | – | `kontor_button()` prüft Ziele über `theme_v4_link_available()`, externe Links mit `rel="noopener noreferrer"`; Teilen-Links mit `rawurlencode` | kein Befund |
| S2 | niedrig | Paginierung und Archiv-Formular spiegelten `REQUEST_URI` | behoben |
| B | mittel | Aktiver Menüeintrag fehlte bei Installation im Unterverzeichnis | behoben |
| B | niedrig | `mailto:`/`tel:` im Menü galten als extern (`target="_blank"`) | behoben |
| B/K6 | mittel | Tag-Slugs ohne Akzente (`Café` → `caf`, 404) | `theme_v4_slug()` |
| P1 | akzeptiert | Listen laden `p.content` für die Lesezeit (≤ 50 Zeilen) | dokumentiert |
| Q3 | – | Fokusrahmen (Tinte 14,4:1, 4.0-Audit), `scroll-padding-top: 6rem` für den Sticky-Header bereits vorhanden | kein Befund |
| K3 | – | Standard-Footermenü „Community“ nur bei aktivem Modul | umgesetzt |
| K5 | – | Header öffnet `<main>`: Kit wandelt `<main>` künftiger Community-Overrides in `<div>` | vorbereitet |
| K2/K7/K8 | – | keine rollenabhängigen Links, keine Benachrichtigungen, keine POST-Formulare | kein Befund |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test inkl. Archive | grün |
