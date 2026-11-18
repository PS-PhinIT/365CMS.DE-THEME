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

## 6. Befunde (Audit 2026-11-18, umgesetzt in 2.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `theme_nav_menu()`: `//evil.example` und `/\evil.example` als Link durchgelassen; `mailto:`/`tel:` → `#` | `tn_menu_url()` |
| S4/S11 | mittel | `theme_logout_url()` hängte ein frisches CSRF-Token an `/logout?csrf_token=…` (Leck über Logs, Verlauf, Referer); seit 2.0.0 nirgends aufgerufen, aber als globaler Helfer für Overrides/Plugins erreichbar | nur noch Adresse, Logout per `theme_v4_logout_form()` |
| S1 | – | Index-Karten (`$pTitle`, `$pExcerpt`, `$pUrl` escaped, `$pDate` aus `gmdate`), Header-/Footer-Variablen über `tn_html_attr()`, Flash mit Typ-Allowlist | kein Befund |
| S10 | – | `navigation.js`: Storage nur `dark`/`light`, keine HTML-Senken; neuer Bootstrap mit Nonce und Allowlist | kein Befund |
| B6 | **mittel** | Dark Mode nach Systemeinstellung griff nie (`data-theme` immer serverseitig gesetzt, JS-Bedingung nie wahr); gespeicherte Wahl erst im Footer-Script (Aufblitzen) – offen aus 4.0 | Head-Bootstrap + `data-theme-default` |
| Q1 | niedrig | `strict_types` fehlte in 6 Vorlagen | ergänzt |
| Q3 | mittel | WCAG 2.2 2.4.11: Sticky-Header | `scroll-padding-top` |
| Q3 | – | Dark-Mode-Kontraste (4.0), `aria-pressed` am Umschalter, Mobilmenü mit Fokusführung | kein Befund |
| K2 | – | Kontolinks für jede angemeldete Rolle, Logout als POST | kein Befund |
| K3 | – | Fußzeile „Netzwerk“ → „Community-Forum“ nur bei aktivem Modul | umgesetzt |
| K4 | – | `--tv4-*` auch unter `:root[data-theme="dark"]` → `--community-*` folgt dem Dark Mode | geprüft |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test | grün |
