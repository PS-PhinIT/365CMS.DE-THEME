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

- Neue SQL-Abfragen nur gegen Stubs geprüft (`ONLY_FULL_GROUP_BY`) → Abfragen erneut lesen. → erneut gelesen in 4.1.1-Folgeaudit (Abschnitt 7): keine Änderung nötig, Befunde unten.
- Member-Shell bewusst nur hell.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde (Audit 2026-11-18, umgesetzt in 2.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `feder_safe_url()` (Menüs, Social-Links, Customizer-Ziele) und `feder_customizer_normalize_url()` ließen `/\evil.example` als lokalen Pfad durch (Render-Test) | behoben |
| S4/S5 | – | Customizer: `isAdmin()` vor jeder Aktion (Redirect + `exit`), CSRF (`theme_customizer` bzw. Admin-Shell) für Speichern und Zurücksetzen, unbekannte Bereiche abgewiesen | kein Befund |
| S6 | niedrig | Upload: Endung **und** `finfo`-MIME (JPG/PNG/GIF/WebP, kein SVG), 2 MB, `is_uploaded_file`, Zufallsname unter `uploads/theme-assets/`; aber `u_x[]` (Array-Struktur) führte zu „Array to string conversion“ | Array-Fall abgewiesen |
| S8 | – | `$_POST`-Felder: Nicht-Skalare → leer, Feldtypen (Farbe `#rrggbb`, Zahl mit Grenzen, Auswahl aus Optionen, URL, Text ohne Tags) | kein Befund |
| S7 | – | `feder_get_posts()`: Platzhalter für Ausschlüsse/Kategorie/Datum, `LIMIT/OFFSET` als geklemmte Integer, `ORDER BY` aus `match`; `cms_post_publication_where()` in allen Abfragen; Kategoriezählung mit `GROUP BY` über alle Nicht-Aggregatspalten (`ONLY_FULL_GROUP_BY`) | kein Befund |
| S2 | niedrig | Paginierung und Archiv-Formular spiegelten `REQUEST_URI` | behoben |
| S1 | – | `$content` (Core-HTML), `$count`/`$total`/`$currentPage` (int), `$coreItems` (Core), Teilen-URLs mit `rawurlencode` und Escaping | kein Befund |
| S3 | – | `feder_css_color/_number`, `feder_sanitize_custom_css()` | kein Befund |
| S10 | – | Farbschema: Pre-Paint-Script mit Nonce und Allowlist (`dark`/`light`), `theme.js` ohne HTML-Senken | kein Befund |
| B | mittel | Aktiver Menüeintrag fehlte bei Installation im Unterverzeichnis (`/cms/blog` gegen `/blog` verglichen) | behoben (`theme_v4_is_current_path()`) |
| B/K6 | mittel | Schlagwort-Slugs aus Namen ohne Slug: Akzente gingen verloren (`Café` → `caf`, 404) | behoben (`theme_v4_slug()`) |
| P1 | niedrig | Vor/Zurück-Links luden je eine Abfrage **mit** `p.content` | ohne Inhalt |
| P1 | akzeptiert | Listen laden `p.content` für die Lesezeit (begrenzt auf ≤ 50 Zeilen); Kategorien einmal je Request | dokumentiert |
| B1/B5 | – | `feder_format_date()` mit ungültigem Datum, Paginierung `?p[]=` ohne Warnungen | kein Befund |
| Q3 | – | Dark-Mode-Kontraste unverändert bestanden (4.0-Audit), `aria-pressed` am Schalter; Header nicht fixiert | kein Befund |
| K2 | – | keine rollenabhängigen Links | kein Befund |
| K3 | – | Standard-Footermenü „Community“ nur bei aktivem Modul | umgesetzt |
| K4 | – | `--tv4-*` zeigt auf `--fdr-*` und folgt dem Dark Mode; Präfix `fdr-` kollidiert nicht | geprüft |
| K5 | – | Header öffnet `<main>`: Kit wandelt `<main>` künftiger Community-Overrides in `<div>` | vorbereitet |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test inkl. `blog`, `category`, `tag`, `author` | grün |

## 7. Folgeaudit (2026-11-20, umgesetzt in 2.1.1, Kit 1.2.0)

Das Erstaudit (Abschnitt 6) war statisch und per Render-Test. Offen waren die Browser-/axe-Prüfung, die Kostenmessung der Kit-Linkprüfung, die Seitensprache und die Beispieltexte in `theme.json`. Werkzeug: [`tools/`](tools/README.md).

### Messung vor den Korrekturen

| Prüfung | Ergebnis am Stand 2.1.0 |
|---|---|
| Chromium + axe-core (WCAG 2.0–2.2 A/AA, Best Practice), 13 Seitenvarianten × 1280/375/320 px (hell) | 16 von 45 Läufen mit Befunden: `button-name` (kritisch, 6 Läufe), `color-contrast` (9 Läufe, „404“ 1,17:1), `landmark-complementary-is-top-level` (3 Läufe, Kontaktseite) |
| Datenbankabfragen für Standardlinks (Attrappe zählt `PageManager::getPageBySlug()`) | Startseite 1 Aufruf (`SELECT *` samt Inhalt), 404/Archiv/Beitrag 0 |
| Texte ohne eigene Werte | Startseite und Autorenbox mit Rolle „Autorin & Redakteurin“, Kurzbiografie, Intro mit „etwa zweimal im Monat“, Newsletter-Zusage „Einmal im Monat … Kein Spam“ |
| Sprache `/en/…` | nur Skip-Link und Vorschauhinweis übersetzt |

### Befunde

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| Q3 | **hoch** | Suchknopf (Suchseite, 404, Kopfzeile) ohne zugänglichen Namen unter 640 px: das CSS blendet den Text aus (axe `button-name`) | `aria-label` |
| Q3 | mittel | „404“/Fehlercode als helle Füllung mit Kontur: 1,2:1 zum Hintergrund | Vollfarbe in Akzentfarbe |
| Q3 | **hoch** | Dunkelmodus: Absende-Button der Core-Formulare (`.cms-form__submit`, Kontaktseite) mit fest weißer Schrift auf dem hellen Akzent (#e8909b): 2,4:1 (axe `color-contrast`, nur im Dunkelmodus-Lauf sichtbar) | Schriftfarbe über `--tv4-on-primary` |
| Q3 | niedrig | Kontaktseite: `<aside>` in beschriftetem `<section>` (Landmark in Landmark) | `<div>` |
| S11/Demo | **mittel** | Beispieltexte als Standardwerte (`theme.json`, `settings` und Customizer): Rolle „Autorin & Redakteurin“, Kurzbiografie, Intro mit Veröffentlichungsrhythmus, Newsletter-Zusage „Kein Spam, jederzeit abbestellbar“, Fußzeilen-Kurzbeschreibung – erscheinen auf jeder Installation | Standardwerte leer (Intro-Überschrift: Seitentitel), Beschreibungen nennen den früheren Text; gespeicherte Werte bleiben |
| P1 | mittel | `theme_v4_link_available()`: je Link eine `SELECT *`-Abfrage samt Inhalt (betrifft alle Themes) | Kit 1.2.0: 1 Abfrage je Request (bei feder 1 → 1, ohne Inhalte) |
| B4 | mittel | Seitensprache: Header (Suche, Menü, Farbschema), Startseite, Archive, Beitrag, Seite, Suche, 404, Fußzeile, Suchformular, Paginierung, Standardmenüs, Dokumenttitel, Datum und Rückmeldung „Link kopiert“ (fest in `theme.js`) nur deutsch | `feder_t()`, `feder_text_t()`, `data-fdr-copy-*`, englisches Datumsformat |
| S6 | – | Customizer-Upload, Array-Fall (`u_x[]`) aus 2.1.0 erneut geprüft | kein Befund |
| S7 | – | Abfragen erneut gelesen (`ONLY_FULL_GROUP_BY`, Platzhalter, `LIMIT/OFFSET`) | kein Befund |
| Q3 | – | axe (hell und mit `prefers-color-scheme: dark`): Kontraste, Zielgrößen (2.5.8), Links im Fließtext, Fokus, Kopfzeile bei 320 px | kein Befund |

### Ergebnis nach den Korrekturen

- Chromium/axe: 45 Läufe (13 Seitenvarianten × 1280/375/320 px, teils mit/ohne reduzierte Bewegung, inkl. angemeldet, ohne Beispielinhalte, englisch) ohne Befund; zusätzlich 27 Läufe im Dunkelmodus (`SCHEME=dark`: Startseite, Beitrag, Seite, Suche, 404, Kontakt, Fehlerseite) ohne Befund.
- `php -l`, `TESTS/theme-contract` des Cores (13 Themes), Render-/Fuzz-Test inkl. `blog`, `category`, `tag`, `author`, Kit-Tests grün.
- Englische Startseite im Render-Test: keine deutschen Theme-Texte außer Customizer-Eingaben und Menüs des Betreibers.

### Offen / Grenzen

- Die Prüfung läuft gegen Attrappen der Core-Klassen, ohne echte Installation (CSP-Header, Core-Stylesheets, Plugins). Kontraste gelten für die Standardpalette; eigene Customizer-Farben sind nicht geprüft.
- Customizer-Texte, die der Betreiber geändert hat, sind einsprachig; nur unveränderte Standardtexte werden übersetzt.
- Die Standardlinks (Fußzeile, Autorenlink) zeigen auf englischen Seiten auf die Standardrouten (ohne `/en/`); nur die Beschriftungen sind übersetzt.
- Der Dokumenttitel nutzt die Seitensprache aus dem Kontext (`contentLocale`), sonst aus dem Pfad.
