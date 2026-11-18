# Audit-Plan kompass (365CMS 4.2.0)

> **PR:** 1 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Behörden-/Service-Portal (Barrierefreiheit, FAQ, Inhaltsverzeichnis, Hinweisbanner) |
| Dateien | 31 (4.477 Zeilen PHP, 305 Zeilen JS) |
| Header öffnet `<main>` | ja |
| Dark Mode | nein, **Hochkontrastmodus** (`data-contrast="high"`) und Schriftgröße (`localStorage`) |
| Eigene Vorlagen | `blog.php` (A–Z-Index), `author.php`, `category.php`, `tag.php` |
| Customizer | eigener Admin-Customizer `admin/customizer.php` (456 Z.) |
| Besonderheit | `kompass_slugify()` + `kompass_toc()` (Anker aus Überschriften), Seiten-Werkzeuge (PDF, Drucken), Feedback-Box |

## 2. Angriffsfläche

- Admin-Customizer (wie feder): `admin/customizer.php:230–280`.
- SQL: `functions.php:764–820` (`kompass_get_posts`, `kompass_categories` mit `UNION`).
- Request-Daten: `index.php:26`, `blog.php:74`, `functions.php:512/526/1044/1301–1303/1506`.
- `kompass_toc()`: verarbeitet Core-HTML per Regex/DOM und setzt IDs → HTML-Manipulation am Inhalt.
- `kompass_page_tools()`: Druck-/PDF-Link (`kompass_pdf_available()`), `$_SERVER['REQUEST_URI']` (Z. 1506) in Links.
- JS: `js/theme.js` (Schriftgröße, Kontrast, TOC, Feedback).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S4/S5/S6/S8 | Customizer wie feder: CSRF, `isAdmin()`, Upload, Array-Eingaben, Feldvalidierung | `admin/customizer.php` |
| S7 | Prepared Statements, Publikationsfilter auch im `UNION`-Zweig `post_category_rel`, Integer-Grenzen | `functions.php:760–830` |
| S1 | `$readingTime`, `$content`, `$count`, `$serviceNav`, `$headingTag` (nur `h2`/`h3`?) | `blog-single.php:83/109`, `search.php:39`, `header.php:93`, `functions.php:1199/1406` |
| S1/S2 | `REQUEST_URI` (Z. 1506) in `href`/Teilen/Drucken escaped, kein Header-Splitting, kein `//host` | `functions.php:1500–1515` |
| S1 | `kompass_toc()`/`kompass_render_toc()`: IDs aus Überschriftentext escaped, keine doppelten IDs, kein Zerstören von Core-HTML (`<picture>`, Downloads, Formulare) | `functions.php` |
| S10 | `theme.js`: Storage-Werte nur Allowlist, Feedback ohne Senken | `js/theme.js` |
| B1/B2 | `kompass_format_date()`, leere FAQ/Quick-Links, `kompass_lines()` | `functions.php` |
| B5 | `blog.php` A–Z/Filter (`?q`, `?buchstabe`), Paginierung | `blog.php` |
| P1 | `kompass_categories()` gecacht, Startseite (News, Quick-Links, FAQ) ohne Mehrfachabfragen | `functions.php` |
| P2 | TOC-Erzeugung nur einmal je Inhalt | `page.php`, `blog-single.php` |
| Q3 | Hochkontrast + Fokus, Schriftgröße A++ ohne Überlappung (1.4.4/1.4.10), Sticky-Fokus | `style.css` |
| K3 | Schnellzugriffe/Service-Navigation `/forum` | `kompass_quick_links()`, `kompass_default_menu()` |
| K4 | Hochkontrast-Werte in die Community-Brücke (wie bei `--tv4-*`) | `style.css` |
| K5 | Header-`<main>` | |
| **K6** | `kompass_slugify()` → `theme_v4_slug()` (Umlaute `ae/oe/ue/ss` wie der Core) | `functions.php` |
| K8/K9 | Feedback-Formular: POST? → `form_guard`; Vertragstest | `kompass_feedback()` |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- Demo-Kontaktdaten `+49 123 456 789` (Customizer-Standard) – prüfen, ob ohne Pflege öffentlich erscheinen.
- SQL nur gegen Stubs geprüft.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde (Audit 2026-11-18, umgesetzt in 2.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `kompass_safe_url()` und `kompass_customizer_normalize_url()` ließen `/\evil.example` als lokalen Pfad durch (Render-Test) | behoben |
| S4/S5/S6/S8 | niedrig | Customizer (gleiche Basis wie feder): `isAdmin()`, CSRF, Feldvalidierung und Upload (Endung + MIME, Zufallsname) in Ordnung; `u_x[]` führte zu „Array to string conversion“ | Array-Fall abgewiesen |
| S7 | – | `kompass_get_posts()` mit Platzhaltern und geklemmtem `LIMIT/OFFSET`; `kompass_categories()`: Publikationsfilter in **beiden** `UNION`-Zweigen, `COUNT(DISTINCT)` verhindert Doppelzählung, ein Request-Cache | kein Befund |
| S1 | – | `$readingTime` (int), `$content` (Core-HTML, nur Anker ergänzt), `$count` (int), `$serviceNav` (escaped), `$headingTag` (nur `h2`/`h3` aus Code) | kein Befund |
| S1/S2 | – | Feedback-Box: `mailto:` mit validierter Service-Adresse, Betreff/Text `rawurlencode`, Seitenadresse über `kompass_url()` (verwirft `//host`) | kein Befund |
| S2 | niedrig | Paginierung und Archiv-Formular spiegelten `REQUEST_URI` | behoben |
| S10 | – | Schriftgröße/Kontrast: Pre-Paint-Script mit Nonce und Allowlist, `theme.js` ohne HTML-Senken | kein Befund |
| B | mittel | Aktiver Menüeintrag fehlte bei Installation im Unterverzeichnis | behoben (`theme_v4_is_current_path()`) |
| B | mittel | Inhaltsverzeichnis: `\bid=` traf auch `data-id=` → Überschrift ohne `id`, TOC-Link ins Leere | behoben (`(?<![\w-])id=`) |
| K6 | mittel | `kompass_slugify()` verlor Akzente („Café“ → `caf`), wich vom Core ab | über `theme_v4_slug()` (Render-Test: `groesse-cafe`) |
| P1/P2 | – | Kategorien einmal je Request, TOC einmal je Inhalt | kein Befund |
| Q3 | – | Hochkontrast (19,6–21:1, 4.0-Audit), Schriftgröße A++, `scroll-padding-top`, Header nicht fixiert (nur die Seitenleiste `sticky`) | kein Befund |
| K3 | – | Standard-Footermenü „Community“ nur bei aktivem Modul | umgesetzt |
| K4 | – | `--tv4-*` auf `--kp-*`, im Hochkontrastmodus eigene Werte → `--community-*` folgt | geprüft |
| K5 | – | Header öffnet `<main>`: Kit wandelt `<main>` künftiger Community-Overrides in `<div>` | vorbereitet |
| K8 | – | Feedback per `mailto:`, kein POST | kein Befund |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test inkl. TOC | grün |
| S11 | **mittel** | Demo-Kontaktdaten als `theme.json`-Standard (`Marktplatz 1, 12345 Musterstadt`, `+49 123 456 789`, `service@example.org`, Sprechzeiten): Der Core gibt ungepflegte Werte mit diesem Standard aus, frische Installationen zeigten also erfundene Kontaktdaten öffentlich an, und „War diese Seite hilfreich?“ schickte Mails an `example.org` | Standards geleert; Angaben und Feedback-Box erscheinen nur mit gepflegten Daten, die Faktenliste entfällt ganz, wenn nichts gepflegt ist |
