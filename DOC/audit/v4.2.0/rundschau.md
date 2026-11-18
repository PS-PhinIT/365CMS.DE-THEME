# Audit-Plan rundschau (365CMS 4.2.0)

> **PR:** 3 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Regionale Nachrichten (Ressorts mit Farben, Ticker, „Meistgelesen“) |
| Dateien | 31 (4.123 Zeilen PHP, 287 Zeilen JS) |
| Header öffnet `<main>` | ja |
| Dark Mode | nein |
| Eigene Vorlagen | `blog.php`, `author.php`, `category.php`, `tag.php` |
| Customizer | eigener Admin-Customizer `admin/customizer.php` (456 Z.) |
| Besonderheit | `rundschau_popular_posts()`, `rundschau_head_tags()`, Ressort-Blöcke (eine Abfrage je Block), Teilen (WhatsApp, LinkedIn) |

## 2. Angriffsfläche

- Admin-Customizer (Basis wie feder): `admin/customizer.php:230–280`.
- SQL `functions.php:688–870`: Kategorien mit Zählung, `IN ({$ph})` mit Platzhaltern, Tag-Lookup, Beitragslisten, „Meistgelesen“ (Quelle?).
- Request-Daten: `blog.php:61`, `functions.php:462/1093–1095`.
- JS: `js/theme.js` (Ticker, Mobilpanel).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S4/S5/S6/S8 | Customizer: CSRF, `isAdmin()`, Upload, Arrays, Feldvalidierung | `admin/customizer.php` |
| S7 | `IN ({$ph})`: Anzahl Platzhalter = Anzahl Werte, Integer-Cast; Tag-Slug gebunden; Publikationsfilter in allen Zweigen; „Meistgelesen“ ohne Entwürfe | `functions.php:680–880` |
| S1 | `$content`, `$count`, `$rsFooterNav`, `$rsLegalNav`, `$rsServiceNav`, `$headingTag` | `blog-single.php:124`, `search.php:29`, `footer.php:65/75`, `header.php:104`, `functions.php:1186` |
| S2 | `rundschau_safe_url()`, Teilen-URLs (`wa.me`), Social | `functions.php`, `blog-single.php:57–58/108` |
| S3 | Ressortfarben aus Customizer → CSS, eigenes CSS | `functions.php` |
| S10 | `theme.js` Ticker ohne Senken, `prefers-reduced-motion`, Pausieren (2.2.2) | `js/theme.js` |
| B1 | `rundschau_timestamp()/_time_label()` (relative Zeiten, Zeitzone) | `functions.php` |
| B5 | Archive/Paginierung, unbekannter Tag/Autor | `blog.php`, `tag.php`, `author.php` |
| P1 | Startseite: Ressort-Blöcke (≤ 8 Abfragen, offen aus 4.0) → eine gruppierte Abfrage möglich?; Kategorien gecacht | `home.php`, `functions.php` |
| P4 | Aufmacher LCP, Teaser lazy | |
| Q3 | Ressort-Badges auf Flächen (offen aus 4.0), Ticker pausierbar, Zielgrößen | `style.css` |
| K2/K3 | Kontolinks, `/forum` in Ressort-Leiste/Footer | |
| K4/K5 | Token-Brücke (Ressortfarben nicht in Community), Header-`<main>` | |
| K6 | Slug-Erzeugung (Ressortklassen aus Namen?) → `theme_v4_slug()` | `rundschau_ressort_class()` |
| K8/K9 | Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- Eine Abfrage je Ressort-Block.
- Ressort-Badges auf Flächen nicht im Kontrast-Audit.
- Alte gespeicherte Orange-Werte.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde (Audit 2026-11-18, umgesetzt in 2.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `rundschau_safe_url()` und die Customizer-URL-Normalisierung ließen `/\evil.example` als lokalen Pfad durch (Render-Test) | behoben |
| S7 | niedrig | Schlagwort-Archiv: Rückfall-Suche per `LIKE` maskierte `%`/`_` nicht (kein SQL-Injection-Risiko, Platzhalter waren gebunden, aber beliebige Treffer) | `addcslashes()` |
| S7 | – | `IN ({$ph})` mit gleicher Platzhalterzahl und doppelt übergebenen IDs, Tag-Slug gebunden, `LIMIT/OFFSET` geklemmt, Publikationsfilter in allen Abfragen, „Meistgelesen“ über `rundschau_get_posts()` (veröffentlicht, Zeitraum) | kein Befund |
| S4/S5/S6/S8 | niedrig | Customizer (gleiche Basis wie feder) in Ordnung; `u_x[]` → „Array to string conversion“ | Array-Fall abgewiesen |
| S2 | niedrig | Paginierung und Archiv-Formular spiegelten `REQUEST_URI` | behoben |
| S1 | – | `$content` Core-HTML, `$count` int, Menü-HTML escaped, `$headingTag` aus Code, Teilen-URLs (`wa.me`, LinkedIn) mit `rawurlencode` | kein Befund |
| B | niedrig | `mailto:`/`tel:` im Menü galten als extern (`target="_blank"`); Unterverzeichnis-Erkennung ohne Pfadgrenze | behoben (`theme_v4_is_current_path()`) |
| B/K6 | mittel | Tag-Slugs ohne Akzente (`Café` → `caf`, 404) | `theme_v4_slug()` |
| K6 | – | Ressortklassen per `crc32` aus dem Slug (keine Slug-Erzeugung) | kein Befund |
| P1 | akzeptiert | eine Abfrage je Ressort-Block (höchstens 8, offen aus 4.0) | dokumentiert |
| Q3 | – | Ticker mit Pause-Schalter (`aria-pressed`, 2.2.2) und `prefers-reduced-motion`; `scroll-padding-top` vorhanden | kein Befund |
| Q3 | offen | Ressort-Badges auf Flächen ohne vollständiges Kontrast-Audit (aus 4.0) | dokumentiert |
| K3 | – | Standard-Footermenü „Leserforum“ nur bei aktivem Modul | umgesetzt |
| K5 | – | Header öffnet `<main>`: Kit wandelt `<main>` künftiger Community-Overrides in `<div>` | vorbereitet |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test inkl. Archive | grün |
