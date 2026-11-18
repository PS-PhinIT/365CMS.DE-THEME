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

- Demo-Kurse/Kennzahlen statisch (bleibt Designentscheidung; prüfen, ob als Demo erkennbar).
- Browser-/axe-Prüfung stand aus → hier statisch und per Render-Test nachholen, soweit ohne laufende Installation möglich.

## 6. Abnahme

Alle Zeilen in Abschnitt 3 und 4 mit Ergebnis, Kit 1.1.0 in academy365, Metadaten 4.1.0 (`theme.json`, `update.json`, `style.css`, Konstante, CHANGELOG, README), Vertragstest und `php -l` grün.

## 7. Befunde

_Wird nach dem Audit ausgefüllt._
