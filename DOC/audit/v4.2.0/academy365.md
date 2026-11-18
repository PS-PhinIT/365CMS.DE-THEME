# Audit-Plan academy365 (365CMS 4.2.0)

> **PR:** 1 | **Version vor Audit:** 4.0.1 (Kit 1.0.1) | **Ziel:** 4.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Bildung/E-Learning (Kurse, Zertifikate, Lernpfade) |
| Dateien | 24 (2.382 Zeilen PHP, 191 Zeilen JS in `js/navigation.js`) |
| Header öffnet `<main>` | nein |
| Dark Mode | nein |
| Eigene Vorlagen über den Standard hinaus | keine |
| Customizer | Core-Customizer über `theme.json` (11 Bereiche, u. a. `learning_courses`, `learning_hero`, `advanced`) |
| Besonderheit | Referenzumsetzung des Kits; Träger der Kit-Prüfung (Abschnitt 4) |

## 2. Angriffsfläche

- Customizer-Werte → `<style>` in `functions.php` (`academy365_css_color/_number/_choice`, `academy365_sanitize_custom_css`, Ausgabe `echo $custom` ~Z. 198).
- Customizer-URLs → `href` (`academy365_safe_url`), Menüs (`theme_nav_menu`, `target="_blank"` ~Z. 362).
- Request-Daten: `index.php` Z. 23–25 (`$_SERVER['REQUEST_URI']`, `$_GET` für Paginierung).
- Includes mit Variablen: `functions.php` Z. 389/406 (`require $path` in `get_header()/get_footer()`), Kit-Overrides `knowledgebase/*`, `newsletter/*`.
- JS: `js/navigation.js` (Menü, Suche).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S1 | Alle rohen Ausgaben belegen: `$postContent`, `$pageContent` (Core-HTML), `$currentPage/$totalPages` (int?), `$year`, `$contactFormHtml`, `$custom`, `$coreItems` | `blog-single.php:60`, `page.php:25`, `index.php:67`, `footer.php:89`, `contact.php:60`, `functions.php:198/372` |
| S2 | `academy365_safe_url()`: Schemata, protokollrelative URLs, Steuerzeichen, Leerzeichen-Tricks (`java\tscript:`) | `functions.php` |
| S3 | `academy365_sanitize_custom_css()`: `</style`, `<!--`, `@import`, `url(javascript:)`, `expression(`; Schriften/Choices nur aus Allowlist | `functions.php` |
| S8 | `index.php`: `$_GET` als Array (`?page[]=1`), negative/riesige Seiten, Weitergabe fremder Query-Parameter in Paginierungslinks | `index.php:23–25` |
| S9/S10 | `navigation.js` ohne HTML-Senken, keine Inline-Styles per JS, die die CSP brechen | `js/navigation.js` |
| S12 | `get_header()/get_footer()`: `$path` nur aus festen Dateinamen? Pfad-Traversal ausgeschlossen | `functions.php:380–410` |
| B1/B2 | Datum/Zahlen unter `strict_types`, leere Beitragsbilder, fehlende Autoren | `blog-single.php`, `search.php`, `index.php` |
| B3 | `error.php` als Fragment und eigenständig, `404.php`, `page.php` mit `id = 0` | |
| B5 | Paginierung `page=0`, `page=99999`, nicht numerisch | `index.php` |
| P1 | Abfragen je Request (Optionen über `theme_v4_options`), keine Abfragen in Schleifen | `functions.php`, `home.php` |
| P3/P4 | `defer`, `?v=`, LCP-Bild mit `fetchpriority`, Lazy-Loading darunter | `header.php`, `home.php` |
| Q1/Q2 | Ungenutzte Funktionen (`theme_use_local_fonts`, `theme_csp_nonce_attr`) und Dopplungen zum Kit | `functions.php` |
| Q3 | WCAG 2.2: Sticky-Header verdeckt Fokus (2.4.11, `scroll-padding-top`), Zielgrößen ≥ 24 px (Menüknopf, Footer-Links) | `style.css` |
| K2 | Kopfbereich „Anmelden/Mein Bereich“ für `moderator` korrekt, kein Admin-Link per Rolle | `header.php` |
| K3 | Fallback-/Footer-Links: `/forum` nur bei aktivem Modul | `footer.php`, `functions.php` |
| K4 | Kit-CSS `--community-*` gegen Theme-Variablen `--accent-color` u. a. (keine Kollision, da andere Namen) | `style.css`, `css/cms-v4.css` |
| K5 | Header ohne `<main>` → Core-Vorlagen dürfen eigenes `<main>` setzen (dokumentieren) | |
| K8 | Öffentliche POST-Formulare vorhanden? (erwartet: nur Kontakt/Newsletter über Core) | alle Templates |
| K9 | Theme-Vertragstest, `php -l` | |

## 4. Kit-Prüfung (gilt für alle 13 Themes, wird hier einmal durchgeführt)

| Punkt | Prüfung |
|---|---|
| `theme_v4_link_available()` | Pfadnormalisierung (`..`, `//`, Query, Fragment), Kosten je Aufruf (Routen-/Seitenabfrage gecacht?) |
| `theme_v4_safe_back_url()` | nur gleiche Herkunft, kein `//evil`, kein `\\evil` |
| `theme_v4_image_dimensions()` | nur lokale Uploads, kein `getimagesize()` auf entfernte URLs, Pfad bleibt unter `uploads/` |
| `theme_v4_local_asset_url()` | keine Pfadumgehung |
| `theme_v4_member_tokens_style()` | Farbwerte validiert, bevor sie in `<style>` landen |
| `theme_v4_head_tags()` | Vorschau: `noindex`, kein JSON-LD |
| `theme_v4_error_context()` | Codes nur 400–599, Retry-After als Integer |
| 4.2.0-Erweiterungen | siehe README Abschnitt 4 (Modulprüfung, `/forum`, `theme_v4_slug()`, `community` im Modul-Wrapper, Capability-Helfer, Token-Brücke) |

## 5. Offene Punkte aus dem 4.0/4.1-Audit

- Demo-Kurse/Kennzahlen statisch (bleibt Designentscheidung; prüfen, ob als Demo erkennbar). → erledigt in 4.1.1 (Abschnitt 8)
- Browser-/axe-Prüfung stand aus → hier statisch und per Render-Test nachholen, soweit ohne laufende Installation möglich. → erledigt in 4.1.1 (Abschnitt 8)

## 6. Abnahme

Alle Zeilen in Abschnitt 3 und 4 mit Ergebnis, Kit 1.1.0 in academy365, Metadaten 4.1.0 (`theme.json`, `update.json`, `style.css`, Konstante, CHANGELOG, README), Vertragstest und `php -l` grün.

## 7. Befunde (Audit 2026-11-18, umgesetzt in 4.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **hoch** | `theme_nav_menu()` prüfte Menü-URLs mit `FILTER_VALIDATE_URL`; das akzeptiert `javascript://x%0aalert(1)` → Script-Link im Menü (im Render-Test am Originalstand nachgewiesen). Ebenso `/\evil.example` als „lokaler“ Pfad | behoben: `academy365_menu_url()` (http(s), Pfad, Anker, mailto/tel) |
| S2 | mittel | `academy365_safe_url()` ließ `/\host` durch; `theme_route_url()` fiel bei ungültiger URL auf die URL selbst zurück | behoben |
| S2 | niedrig | Archiv-Paginierung spiegelte `REQUEST_URI` als Link-Basis | behoben (`theme_v4_request_path()`) |
| S1/S3 | – | Rohe Ausgaben (`$postContent`, `$pageContent` = Core-HTML; `$currentPage/$totalPages` int; `$year` aus `date()`; `$contactFormHtml` Core; `$custom` bereinigt; `$coreItems` Core) | kein Befund |
| S3 | – | `academy365_sanitize_custom_css()` (`</` → `<\/`, `@import`, `expression`, `behavior`), Farben/Zahlen/Choices validiert | kein Befund |
| S8 | – | `?p[]=1`, `?q[]=a`: Arrays werden verworfen (`is_scalar`), keine Warnungen | kein Befund |
| S9/S10/S12 | – | Nonces, keine Inline-Handler, keine HTML-Senken im JS, `ABSPATH` überall, `get_header()` nur feste Dateinamen | kein Befund |
| B | **mittel** | Font-Stacks HTML-escaped im `<style>` → `font-family` ungültig, Schriftwahl wirkungslos | behoben |
| B | mittel | Standard-Headline zeigte `<br>` als Text | behoben |
| B/K3 | mittel | Startseite verlinkte `/courses`, `/courses/<slug>`, `/tutors`, `/tutor-register`, `/pro` ohne Prüfung (404) | behoben (`theme_v4_link_available()`) |
| B6 | mittel | Kennzahlen-Animation: „185.000“ → „185.0“, „2.400+ Kurse“ → „2.4 + Kurse“ | behoben |
| Q3 | mittel | Escape fokussierte mobil immer den Menüknopf; Fortschrittsbalken ohne Namen | behoben |
| Q3 | mittel | WCAG 2.2 2.4.11: fester Header konnte fokussierte Elemente verdecken | behoben (`scroll-padding-top`) |
| B | niedrig | `number_format()` mit englischem Tausendertrenner | behoben |
| P1–P4 | – | keine DB-Abfragen im Theme, Optionen über OptionStore, `defer`, `?v=`, LCP-Bild mit `fetchpriority` | kein Befund |
| K2 | – | „Meine Kurse“ für jede angemeldete Rolle (auch `moderator`), kein Admin-Link | kein Befund |
| K3 | – | Fußzeile „Plattform“ → „Community“ nur bei aktivem Modul | umgesetzt |
| K4/K5 | – | Theme nutzt `--accent-color` (keine Kollision mit `--accent`); Header ohne `<main>`: Core-Vorlagen dürfen `<main>` setzen | geprüft |
| K6 | – | Kurs-Slugs über `theme_v4_slug()` | umgesetzt |
| K7/K8 | – | keine Benachrichtigungszähler, keine eigenen POST-Formulare | kein Befund |
| K9 | – | `php -l`, `TESTS/theme-contract` (13 Themes), Render-/Fuzz-Test (13 Fälle) | grün |
| Demo | akzeptiert | statische Demo-Kurse, „Tutor 1–4“ und Kennzahlen bleiben Designbeispiel (Customizer) | dokumentiert |

### Kit-Prüfung (Kit 1.1.0, gilt für alle Themes)

| Punkt | Befund | Ergebnis |
|---|---|---|
| `theme_v4_local_asset_url()` | `/\evil.example/x.png` galt als lokal (Logo-/Favicon-Quelle) | behoben |
| `theme_v4_safe_back_url()` | Referer mit `\` wurde nicht verworfen | behoben |
| `theme_v4_is_current_path()` | Unterverzeichnis `/cms` schnitt auch `/cmsblog` ab | behoben |
| `CoreModuleService::isModuleEnabled()` | meldet **unbekannte** Module als aktiv – ein naiver Community-Check wäre auf 4.0/4.1 immer „an“ | `theme_v4_module_enabled()` prüft zuerst `getModuleDefinition()` |
| `theme_v4_image_dimensions()`, `theme_v4_member_tokens_style()`, `theme_v4_head_tags()`, `theme_v4_error_context()` | nur lokale Uploads ohne `..`, Farben validiert, Vorschau ohne JSON-LD, Status 400–599 | kein Befund |
| 4.2.0 | Modulprüfung, Community-Helfer (fail-closed bis die Core-Vorlagen `CMS/views/community/` existieren), `theme_v4_user_can()`, `theme_v4_slug()` (identisch zu `CMS\Slug::fromText()` für Umlaute/Akzente, geprüft), `--community-*`-Brücke | umgesetzt |

## 8. Folgeaudit (2026-11-18, umgesetzt in 4.1.1, Kit 1.2.0)

Das erste Audit (Abschnitt 7) war statisch und per Render-Test. Offen waren die im Plan genannte Zielgröße (2.5.8), die Browser-/axe-Prüfung, „Demo als Demo erkennbar“ und die in der Kit-Prüfung vorgesehene Kostenmessung von `theme_v4_link_available()`. Dieses Folgeaudit holt sie nach (Werkzeug: [`tools/`](tools/README.md)).

### Messung vor den Korrekturen

| Prüfung | Ergebnis am Stand 4.1.0 |
|---|---|
| Datenbankabfragen für Standardlinks (Attrappe zählt `PageManager::getPageBySlug()`) | Startseite 16, 404 11, Archiv 11, je `SELECT *` mit Seiteninhalt, ungepuffert |
| Chromium + axe-core (WCAG 2.0–2.2 A/AA, Best Practice), 11 Seiten × Desktop/Mobil | 24 von 26 Läufen mit Befunden (7 Gruppen, s. u.) |

### Befunde

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| Q3/B | **hoch** | Unter 640 px (abgemeldet) verdrängten „Anmelden“ und „Kostenlos lernen“ den Menüknopf aus dem Bild; das Logo (Testtitel mit 18 Zeichen) brach auf drei Zeilen um und ragte aus dem Header. Mobil war die Navigation nicht erreichbar (Screenshot 375 px; axe/Fokusprüfung: Knopf von Button überdeckt) | Konto-Aktionen im Menüpanel (`.ac-nav-auth`), Titel mit Auslassung; geprüft bei 320/375/600/700/1024 px |
| P1 | **mittel** | `theme_v4_link_available()` fragte jede Seitenadresse einzeln ab (16 `SELECT *` samt Inhalt auf der Startseite, ungepuffert, aus der Fußzeile und den Hero-Links). Betrifft alle 13 Themes | Kit 1.2.0: eine Abfrage je Request (`theme_v4_page_index()`), bei > 500 Seiten gebündelt; berücksichtigt `published_at`, englischen Inhalt, Groß-/Kleinschreibung, Prozent-Kodierung, `..` |
| Q3 | mittel | axe `color-contrast`: Muted `#6b7280` auf `#f5f3ff`/`#ede9fe` nur 4,41/4,07:1 (Kontaktdaten, Karten); Bewertungszahl Gold auf Weiß 2,15:1 | Muted `#5b6472` (≥ 5,04:1), Zahl dunkel, Sterne `--accent-dark` (3,19:1) |
| Q3 | mittel | axe `link-in-text-block`: Links im Fließtext nur durch Farbe (2,8:1 zum Text) abgesetzt (1.4.1) | unterstrichen |
| Q3 | mittel | axe `target-size` (2.5.8): Fußzeilenlinks 17 px hoch, Abstand 23,6 px; Rechtslinks; Fußzeilen-Logo 20 px | mindestens 24 px |
| Q3 | mittel | Beim Abwärtsscrollen ausgeblendeter Header blieb ausgeblendet, wenn der Fokus hineinwanderte (2.4.11) | `.ac-header-hidden:focus-within` |
| B6 | niedrig | Menüknopf: Escape/Außenklick setzten `aria-expanded`, aber nicht die Beschriftung zurück | `setMenu()` |
| S11/Demo | mittel | Vier Beispielkurse mit erfundenen Bewertungen/Teilnehmerzahlen und „Tutor 1–4“ waren nicht abschaltbar und nicht als Beispiel erkennbar; Rückfallwerte „Die #1 Lernplattform“, „185.000 Lernende“ | Schalter `show_demo_courses`/`show_demo_tutors` (Standard an), Kennzeichnung „Beispiel“, neutrale Rückfalltexte, Kennzahlenleiste nur mit Inhalt |
| B4 | mittel | Seitensprache: Header, Suche, Archiv, Beitrag, 404, Fußzeile, Menüknopf (fest in `navigation.js`) und Datum nur deutsch | `theme_v4_t()` bzw. `data-label-*`, `academy365_format_date()` |
| Q2 | niedrig | Zwei Kopien des URL-Prüfers (`$safeUrl`) und des Datumshelfers in `blog-single.php`/`search.php` | `academy365_safe_url()`, `academy365_format_date()` |
| Test | – | Ein Teil der ersten Treffer waren Artefakte des Prüfaufbaus (Glätten beim Scrollen, Core-Formular-CSS fehlte, Fehlerfragment ohne Seitenrahmen); sie wurden im Werkzeug behoben, nicht im Theme | – |

### Ergebnis nach den Korrekturen

- Chromium/axe: 30 Läufe (13 Seitenvarianten, Desktop/Mobil, mit und ohne reduzierte Bewegung, Beispiele aus, englische Seite) ohne Befund; Mobilmenü-Prüfung bei 5 Breiten ohne Befund; Kit-Tests (`tools/kit-tests/`) und `TESTS/theme-contract` grün.
- Standardlinks: 1 Abfrage je Seitenaufruf (vorher 11–16).

### Offen / Grenzen

- Die Prüfung läuft gegen Attrappen der Core-Klassen, ohne echte Installation (CSP-Header, Core-Stylesheets, Plugins). Kontraste gelten für die Standardpalette.
- Die Standardlinks der Fußzeile zeigen auch auf englischen Seiten auf die Standardrouten (ohne `/en/`); nur die Beschriftungen sind übersetzt.
- Beispieldaten der Startseite (Titel, Stufen) bleiben deutsch.
- Kit 1.2.0 liegt zunächst nur in academy365; die übrigen Themes übernehmen es in ihren Folge-PRs (der Kit-Stand 1.1.0 dort bleibt funktionsfähig, nur ohne die Abfrageersparnis).
