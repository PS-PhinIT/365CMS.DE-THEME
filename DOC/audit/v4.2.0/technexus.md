# Audit-Plan technexus (365CMS 4.2.0)

> **PR:** 3 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | IT-Netzwerk (Experten, Unternehmen, Events, Jobs) |
| Dateien | 24 (2.715 Zeilen PHP, 174 Zeilen JS) |
| Header öffnet `<main>` | nein |
| Dark Mode | **ja** (`data-theme`, Umschalter mit `aria-pressed`, `localStorage`) |
| Customizer | Core-Customizer (11 Bereiche, u. a. `tech_expert_cards`) |
| Besonderheit | Mega-Menü, Code-Blöcke, `theme_logout_url()` (4.0: auf POST umgestellt – Restnutzung?), `LandingPageService` |
| PHP-Dateien ohne `strict_types` | `home.php`, `index.php`, `footer.php`, `404.php`, `page.php`, `header.php` |

## 2. Angriffsfläche

- Rohe Ausgaben: `index.php:39–49` (`$pDate`, `$pUrl`, `$pTitle`, `$pExcerpt`), `header.php:52–179`, `footer.php:69–118`.
- `theme_logout_url()` (`functions.php`) – noch referenziert? Token in URL wäre ein Leck.
- Flash (`functions.php:535–551`), eigenes CSS (Z. 312), `tn_html_attr()`.
- JS: `navigation.js` (Farbschema, Mega-Menü, Mobilmenü).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S1 | Index-Karten: Variablen vorher escaped? | `index.php:30–50` |
| S1 | `$tnBodyClass`, `$copyrightText`, URL-Variablen im Header | `header.php`, `footer.php:111` |
| S4 | `theme_logout_url()` entfernen/unbenutzt; Logout nur POST | `functions.php`, Templates |
| S1 | Flash escaped, Typ-Allowlist | `functions.php` |
| S3 | `tn_css_color/_number`, eigenes CSS, Dark-Mode-Selektor | `functions.php` |
| S10 | `navigation.js`: Storage nur `light`/`dark` | `js/navigation.js:25–40` |
| Q1 | `strict_types` in 6 Dateien | s. o. |
| B6 | Kein Aufblitzen des falschen Farbschemas; `prefers-color-scheme` ohne gespeicherte Wahl (offen aus 4.0) | `header.php`, `js/navigation.js` |
| B2/B3 | `LandingPageService`-Felder, `page.php` ohne Titel, Fehlerseite | `home.php`, `page.php` |
| P1/P3/P4 | Abfragen, `defer`, Bildmaße | |
| Q3 | Dark-Mode-Kontraste (4.0), Mega-Menü per Tastatur, Escape, Zielgrößen | `style.css`, `js/` |
| K2 | Kontolinks/Moderator | `header.php:140–155` |
| K3 | `/forum` (Netzwerk-Theme: Forum-Link passt fachlich – nur bei aktivem Modul anbieten) | `functions.php`, `footer.php` |
| K4 | Token-Brücke inkl. Dark Mode (`:root[data-theme="dark"]`) | `style.css`, `css/cms-v4.css` |
| K5/K8/K9 | Header ohne `<main>`, Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- `prefers-color-scheme` ohne gespeicherte Wahl nicht ausgewertet; kurzes Aufblitzen möglich.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde

_Wird nach dem Audit ausgefüllt._
