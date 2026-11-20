# Audit-Plan buildbase (365CMS 4.2.0)

> **PR:** 1 | **Version vor Audit:** 4.0.0 (Kit 1.0.1) | **Ziel:** 4.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Handwerk/Bau (Leistungen, Referenzen, Handwerkersuche) |
| Dateien | 24 (2.394 Zeilen PHP, 82 Zeilen JS) |
| Header öffnet `<main>` | nein |
| Dark Mode | nein |
| Customizer | Core-Customizer über `theme.json` (10 Bereiche, u. a. `build_hero`, `build_content`) |
| Besonderheit | Footer-Menüs `footer-nav`/`footer-legal` über `theme_nav_menu()`, Scroll-Reveal `.bb-card` nur mit `.bb-js` |

## 2. Angriffsfläche

- Customizer → `<style>`: `buildbase_css_color/_number/_choice`, `sanitize`-Klasse, Ausgabe `echo $safeCustom` (`functions.php:233`).
- Customizer-URLs: `buildbase_safe_url()`; Hero-CTAs und Referenzen in `home.php`.
- Menü-HTML: `$bbFooterNavHtml`, `$bbFooterLegalHtml` (`footer.php:101/110`), `$coreItems` (`functions.php:401`).
- `require $path` in `get_header()/get_footer()` (`functions.php:418/435`).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S1 | Rohe Ausgaben `$postContent`, `$bbPage/$bbPages`, Menü-HTML, `$contactFormHtml`, `$safeCustom`, `$coreItems` | `blog-single.php:61`, `index.php:56`, `footer.php:101/110`, `contact.php:73`, `functions.php:233/401` |
| S2 | `buildbase_safe_url()` (Schemata, `//host`, Steuerzeichen), CTA-Ziele | `functions.php`, `home.php` |
| S3 | Eigenes CSS (`</style>`-Ausbruch), Font-Stacks (nach 4.0-Fix nicht mehr HTML-escaped – jetzt CSS-sicher?) | `functions.php` |
| S8 | Paginierung `?page[]=`, Suche `?q[]=` (Core liefert `$query` – Typ prüfen) | `index.php`, `search.php` |
| S10 | `navigation.js`: Escape-Handling Menü/Suche, keine HTML-Senken | `js/navigation.js` |
| B2/B3 | Fehlerseite, 404, Wartungsseite ohne JS (`.bb-card` sichtbar) | `error.php`, `style.css` |
| B5 | Paginierung Grenzen | `index.php` |
| P1 | `theme_nav_menu()` je Menü eine Abfrage? Request-Cache | `functions.php` |
| P3/P4 | Bilder in Referenzen/Hero mit Maßen, `defer` | `home.php`, `header.php` |
| Q3 | Sticky-Header + Fokus (2.4.11), Zielgrößen, Kontraste der Alt-Sektion (4.0-Fix verifizieren) | `style.css` |
| Q2 | Tote Funktionen/Klassen nach dem 4.0-Umbau | `functions.php` |
| K2 | Kontobereich für `moderator` (nur Mitgliederlinks) | `header.php` |
| K3 | Footer-Spalte „Service“ und Fallback-Links: `/forum` nur bei Modul | `footer.php` |
| K4/K5 | Token-Brücke ohne Kollision (`--accent-color` ≠ `--accent`), Header ohne `<main>` | |
| K8/K9 | Keine eigenen POST-Formulare; Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- Browser-/axe-Prüfung ausstehend; Kontraste nur Standardpalette. → Browserprüfung erledigt in 4.1.1 (Abschnitt 8); Kontraste weiterhin nur für die Standardpalette

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 4.1.0, Vertragstest und `php -l` grün.

## 6. Befunde (Audit 2026-11-18, umgesetzt in 4.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `buildbase_safe_url()` (Menüs, Footer, Customizer-URLs) ließ `/\evil.example` als lokalen Pfad durch; Browser navigieren zu `evil.example` (Render-Test: Menüeintrag erschien als `href="/\evil.example"`) | behoben |
| S2 | niedrig | `theme_route_url()` fiel bei ungültiger URL auf die URL selbst zurück | behoben |
| S2 | – | `javascript:`/`data:` in Menüs: bereits durch `buildbase_safe_url()` + mailto/tel-Allowlist abgewiesen | kein Befund |
| S1 | – | Rohe Ausgaben (`$postContent` Core-HTML, `$bbPage/$bbPages` int, Menü-HTML escaped, `$contactFormHtml`, `$safeCustom` bereinigt, `$coreItems`) | kein Befund |
| S3 | – | Farben/Zahlen/Choices validiert, Font-Stacks fest (seit 4.0 nicht mehr escaped), eigenes CSS bereinigt | kein Befund |
| S8/B5 | – | Keine Superglobals im Theme; Fuzz `?p[]=`, `?q[]=` ohne Warnungen | kein Befund |
| S10/B6 | – | `navigation.js`: nur DOM-APIs, Escape schließt nur offene Elemente, `.bb-card` ohne JS sichtbar (`.bb-js`) | kein Befund |
| B3 | – | `error.php` (Fragment/eigenständig), 404, `page.php` mit `id = 0`, Entwurf mit Vorschau-Hinweis | kein Befund |
| P1–P5 | – | keine DB-Abfragen, OptionStore, `defer`, `?v=`, Logo-Maße, keine externen Schriften | kein Befund |
| Q3 | mittel | WCAG 2.2 2.4.11: fester Header (`position: fixed`) konnte fokussierte Elemente verdecken | behoben (`scroll-padding-top`) |
| K2 | – | „Dashboard“ für jede angemeldete Rolle, kein Admin-Link | kein Befund |
| K3 | – | Fußzeile „Service“ → „Community“ nur bei aktivem Modul | umgesetzt |
| K4/K5 | – | Theme-Variablen `--accent-color` (keine Kollision), Header ohne `<main>` | geprüft |
| K6–K8 | – | keine Slug-Erzeugung, keine Benachrichtigungen, keine eigenen POST-Formulare | kein Befund |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test | grün |

## 7. Folgeaudit (2026-11-18, umgesetzt in 4.1.1, Kit 1.2.0)

Das Erstaudit (Abschnitt 6) war statisch und per Render-Test. Offen waren die Browser-/axe-Prüfung, die Kostenmessung der Kit-Linkprüfung und die Seitensprache. Werkzeug: [`tools/`](tools/README.md).

### Messung vor den Korrekturen

| Prüfung | Ergebnis am Stand 4.1.0 |
|---|---|
| Datenbankabfragen für Standardlinks (Attrappe zählt `PageManager::getPageBySlug()`) | Startseite, 404 und Archiv je 5, je `SELECT *` mit Seiteninhalt, ungepuffert |
| Chromium + axe-core (WCAG 2.0–2.2 A/AA, Best Practice), 13 Seitenvarianten × 1280/375 px | axe ohne Befund; 2 Läufe mit Befunden (horizontaler Überlauf Wartungsseite 375 px, Menüknopf angemeldet 375 px teilweise außerhalb des Bildes) |
| Sichtprüfung Kopfzeile bei 320 px | Logo dreizeilig, „Profil anlegen“ verdrängt den Menüknopf vollständig aus dem Bild |

Die erste Fassung der Fokusprüfung im Werkzeug erkannte nur vertikal verdeckte Elemente; sie meldet jetzt auch seitlich aus dem Bild liegende und prüft zusätzlich 320 px.

### Befunde

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| Q3/B | **hoch** | Unter 768 px blieb die Hauptaktion im Header und verdrängte den Menüknopf teilweise (375 px) oder ganz (320 px) aus dem Bild. „Anmelden“ (`bb-btn-ghost`) war mobil per CSS ausgeblendet und sonst nirgends erreichbar. Mobil war die Navigation unerreichbar bzw. kein Login möglich | Konto-Aktionen im Menüpanel (`.bb-nav-auth`), Titel mit Auslassung; 45 Läufe inkl. 320 px ohne Befund |
| S11/Demo | **mittel** | `theme.json`: `show_hero_stats` = an, Werte „800+“, „2.400+“, „4,8★“ mit Beschriftungen „Handwerker im Netzwerk“, „Abgeschlossene Projekte“, „Ø Kundenbewertung“ → auf jeder Installation ohne eigene Werte erfundene Leistungs- und **Bewertungsangaben** öffentlich sichtbar | Schalter aus, Werte leer, Kennzahl nur mit Wert und Beschriftung; gespeicherte Werte bleiben |
| P1 | mittel | `theme_v4_link_available()`: 5 `SELECT *` samt Inhalt je Seitenaufruf (Betrifft alle Themes) | Kit 1.2.0: 1 Abfrage je Request (Render-Test: 5 → 1) |
| B | niedrig | Wartungs-/Fehlerseite: „WARTUNGSARBEITEN“ (Versalien) 34 px breiter als die Karte bei 375 px | `overflow-wrap`, kleinere Schrift und Innenabstand unter 480 px |
| B4 | mittel | Seitensprache: Header (Suche, Menüknopf, Konto-Buttons), Archiv, Beitrag, Seite, Suche, Fußzeile (Rechtslinks, Standard-Copyright), Startseite, Menüknopf-Beschriftung (fest in `navigation.js`) und Datum nur deutsch | `theme_v4_t()` bzw. `data-label-*`, `buildbase_format_date()` |
| Q2 | niedrig | Zwei Kopien des URL-Prüfers (`$safeUrl`) in `blog-single.php`/`search.php`, ungenutzte `$formatDate`-Closure in `search.php` | `buildbase_safe_url()`, Closure entfernt |
| Q2 | – | Ungenutzte Funktionen in `functions.php` | keine (`theme_use_local_fonts`, `theme_csp_nonce_attr` sind dokumentierte Core-Laufzeithelfer) |
| Q3 | – | axe: Kontraste, Zielgrößen (2.5.8), Links im Fließtext, Fokus unter dem festen Header, Alt-Sektion (Befund aus 4.0) | kein Befund |

### Ergebnis nach den Korrekturen

- Chromium/axe: 45 Läufe (13 Seitenvarianten × 1280/375/320 px, teils mit/ohne reduzierte Bewegung, inkl. angemeldet, Beispiel-Kennzahlen, englisch) ohne Befund; Menüpanel bei 320 px per Screenshot geprüft; Kennzahlenleiste in 4 Konfigurationen (Standard, eigene Werte, teilweise Werte, englisch) im Render-Test.
- `php -l`, `TESTS/theme-contract` des Cores (13 Themes), Render-/Fuzz-Test grün.
- Standardlinks: 1 Abfrage je Seitenaufruf (vorher 5).

### Offen / Grenzen

- Die Prüfung läuft gegen Attrappen der Core-Klassen, ohne echte Installation (CSP-Header, Core-Stylesheets, Plugins). Kontraste gelten für die Standardpalette; eigene Customizer-Farben sind nicht geprüft.
- Die Standardlinks der Fußzeile und des Heros zeigen auf englischen Seiten auf die Standardrouten (ohne `/en/`); nur die Beschriftungen sind übersetzt.
- Der Menüpanel-Test (`menu.js`) im Werkzeug nutzt academy365-Selektoren; für buildbase wurde das Panel per Screenshot und über den allgemeinen Browserlauf geprüft.
