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

- Statische Demo-Kennzahlen („200+ Projekte“) – als Designentscheidung belassen, prüfen ob im Customizer abschaltbar. → erledigt in 4.1.1 (Abschnitt 7): Kennzahlen, Leistungen und „Über uns“ sind im Customizer pflegbar, ohne Eintrag entfallen die Abschnitte

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 4.1.0, Vertragstest und `php -l` grün.

## 6. Befunde (Audit 2026-11-18, umgesetzt in 4.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `biz_safe_url()` ließ `/\evil.example` als lokalen Pfad durch (Menüs, Logo, Hero-CTAs über `biz_href()`); Pfade mit Steuerzeichen wurden „bereinigt“ statt verworfen | behoben |
| S2 | niedrig | `blog.php` spiegelte `REQUEST_URI` als Link-Basis der Paginierung | behoben |
| S1 | – | `$bodyCls` (`biz_body_class()` escaped), `$bizSiteTitle`/`$siteTitle` (`biz_site_title()` escaped), `$copyText` (escaped), Menü-HTML, `$pageContent`/`$postContent` (Core-HTML), Blog-Zahlen int | kein Befund |
| S1 | – | `biz_safe_headline()`: erst escapen, dann nur `<span class="highlight">` zurückwandeln | kein Befund |
| S2 | – | Hero-/CTA-Ziele über `biz_href()`, Kontakt-E-Mail per `FILTER_VALIDATE_EMAIL`, Telefon auf `[0-9+]` reduziert | kein Befund |
| S3 | – | `biz_css_color/_number`, eigenes CSS bereinigt | kein Befund |
| S8 | – | `blog.php`: `array_filter($_GET, 'is_scalar')`, Fuzz ohne Warnungen | kein Befund |
| Q2 | niedrig | `biz_sanitize_content_html()` seit 4.0.0 ungenutzt (toter, irreführender Filter) | entfernt |
| P2 | – | `getConfig()` liest `theme.json` einmal je Request (statischer Cache) | kein Befund |
| B3 | – | `error.php` als Fragment im Header-`<main>` und eigenständig mit eigenem `<main>`; `page.php` ohne Titel | kein Befund |
| Q3 | mittel | WCAG 2.2 2.4.11: Sticky-Header verdeckte Fokus und Sprungziele `#leistungen`/`#kontakt` | behoben (`scroll-padding-top`) |
| Q3 | – | Drawer `inert`, Escape nur bei offenem Menü, Fokus zurück | kein Befund |
| K2 | – | Kontolinks für alle angemeldeten Rollen, kein Admin-Link | kein Befund |
| K3 | – | Standard-Footermenü „Community“ (Basispfad aus den Community-Einstellungen, nur bei aktivem Modul) | umgesetzt |
| K5 | – | Header öffnet `<main id="main-content">` (`THEME_V4_HEADER_HAS_MAIN`): Kit wandelt `<main>` künftiger `community/`-Overrides in `<div>` | vorbereitet |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test | grün |
| Demo | akzeptiert | Kennzahlen „98 %“, „50+“ sind statische Designbeispiele | dokumentiert (in 4.1.1 durch pflegbare Felder ersetzt, s. Abschnitt 7) |

## 7. Folgeaudit (2026-11-20, umgesetzt in 4.1.1, Kit 1.2.0)

Das Erstaudit (Abschnitt 6) war statisch und per Render-Test. Offen waren die Browser-/axe-Prüfung, die Kostenmessung der Kit-Linkprüfung, die Seitensprache und die als „akzeptiert“ geführten Demo-Kennzahlen. Werkzeug: [`tools/`](tools/README.md).

### Messung vor den Korrekturen

| Prüfung | Ergebnis am Stand 4.1.0 |
|---|---|
| Datenbankabfragen für Standardlinks (Attrappe zählt `PageManager::getPageBySlug()`) | Startseite, 404 und Archiv je mehrere `SELECT *` mit Seiteninhalt, ungepuffert (je Link eine) |
| Startseite ohne Customizer-Eintrag | feste Texte: sechs Leistungen, „Über uns“ mit Checkliste („Über 200 Projekte“, „ISO 27001-konforme Sicherheitsstandards“), Kennzahlen „10+ Jahre“, „200+ Projekte“, „98 % Kundenzufriedenheit“, „50+ zertifizierte Experten“ – nicht im Customizer änderbar oder abschaltbar |
| Menü-/Footer-Anker | `#leistungen` im Standardmenü und in vier Fußzeilenlinks |

### Befunde

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S11/Demo | **mittel** | Nicht belegbare Leistungs- und Zertifizierungsangaben (ISO 27001, Projekt-/Expertenzahlen, Zufriedenheitswert) waren fest im Quelltext und erschienen auf jeder Installation | Customizer-Felder `services_list`, `about_text`, `about_checklist`, `facts_list`; Checkliste und Kennzahlen standardmäßig leer, Abschnitte nur mit Inhalt |
| B | niedrig | Standardmenü und Fußzeile verlinkten auf `#leistungen`, auch wenn der Abschnitt nicht ausgegeben wird | Links entfallen ohne Leistungen (`biz_has_services()`), Hero-Button ebenso |
| P1 | mittel | `theme_v4_link_available()`: mehrere `SELECT *` samt Inhalt je Seitenaufruf (betrifft alle Themes) | Kit 1.2.0: 1 Abfrage je Request (Render-Test: Startseite, 404, Archiv je 1) |
| B4 | mittel | Seitensprache: Header, Startseite, Archiv, Beitrag, Seite, Suche, 404, Fußzeile, Standardmenüs, Menüknopf-Beschriftung (fest in `navigation.js`) und Datum nur deutsch | `theme_v4_t()`, `data-label-*`, `biz_format_date()` |
| Q2 | niedrig | Zwei Kopien des URL-Prüfers und des Datumsformats in `blog.php`/`blog-single.php`/`search.php` | `biz_safe_url()`, `biz_format_date()` |
| Q3 | – | axe: Kontraste, Zielgrößen (2.5.8), Links im Fließtext, Fokus unter dem Sticky-Header, Kopfzeile bei 320 px (nur „Kontakt“ als Aktion, Menüknopf im Bild) | kein Befund |

### Ergebnis nach den Korrekturen

- Chromium/axe: 45 Läufe (Seitenvarianten × 1280/375/320 px, teils mit/ohne reduzierte Bewegung, inkl. angemeldet, ohne Beispielinhalte, englisch) ohne Befund.
- `php -l`, `TESTS/theme-contract` des Cores (13 Themes), Render-/Fuzz-Test, Kit-Tests grün.
- Standardlinks: 1 Abfrage je Seitenaufruf.

### Offen / Grenzen

- Die Prüfung läuft gegen Attrappen der Core-Klassen, ohne echte Installation (CSP-Header, Core-Stylesheets, Plugins). Kontraste gelten für die Standardpalette.
- Die Beispielleistungen (Standardwert von `services_list`) sind allgemein formulierte Platzhalter; Betreiber sollten sie an ihr Angebot anpassen.
- Standardlinks auf englischen Seiten zeigen auf die Standardrouten (ohne `/en/`); nur die Beschriftungen sind übersetzt.
- Die Texte aus dem Customizer sind einsprachig; sie werden nicht übersetzt.
