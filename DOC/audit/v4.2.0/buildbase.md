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

- Browser-/axe-Prüfung ausstehend; Kontraste nur Standardpalette.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 4.1.0, Vertragstest und `php -l` grün.

## 6. Befunde

_Wird nach dem Audit ausgefüllt._
