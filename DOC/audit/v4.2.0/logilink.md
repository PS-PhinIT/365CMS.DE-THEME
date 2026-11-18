# Audit-Plan logilink (365CMS 4.2.0)

> **PR:** 2 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Logistik (Sendungsverfolgung, Partnernetz, Kapazitätsanfrage) |
| Dateien | 24 (2.853 Zeilen PHP, 157 Zeilen JS) |
| Header öffnet `<main>` | nein |
| Dark Mode | nein |
| Customizer | Core-Customizer über `theme.json` (11 Bereiche, u. a. `logistics_tracking`) |
| Besonderheit | Tracking-Schnellsuche im Header (GET), Kapazitätsformular (GET) auf der Startseite, separater Mobil-Drawer, Flash-Meldungen in der Session |
| PHP-Dateien ohne `strict_types` | `home.php`, `index.php`, `footer.php`, `404.php`, `page.php`, `header.php` |

## 2. Angriffsfläche

- Formulare: Tracking (`header.php:92`), Suche (`header.php:161`), Kapazität (`home.php:353`) – Zielrouten und Parameter.
- Rohe URL-/Text-Ausgaben: `home.php:140/144/353/409`, `header.php:52–161`, `footer.php:69–100`, `404.php:27–30`, `page.php:37`.
- Flash: `ll_get_flash()/ll_set_flash()` (`functions.php:510–525`).
- Customizer → CSS (`ll_css_color/_number`, Fix aus 4.0 verifizieren), eigenes CSS (`echo $custom`, Z. 251).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S1/S2 | Jede vorab berechnete URL-Variable (`$heroCtaUrl`, `$regUrl`, `$llTrackUrl` …) über `ll_href()` + Escaping erzeugt? | `home.php`, `header.php`, `404.php` |
| S1 | Flash-Meldungen escaped, Typ als Allowlist (CSS-Klasse) | `functions.php:505–530` |
| S1 | `$llBodyClass`, `$llCopyright` | `header.php:52`, `footer.php:100` |
| S3 | eigenes CSS `</style>`-Ausbruch | `functions.php:240–255` |
| S8 | Formular-Ziele: Kapazitätsformular (GET) überträgt personenbezogene Daten in die URL (Logs, Referer)? | `home.php:353–400` |
| S10 | `navigation.js`: Drawer, Fokusfalle, keine Senken | `js/navigation.js` |
| Q1 | `declare(strict_types=1)` in den 6 Dateien ergänzen, danach Typfehler prüfen | s. o. |
| B2/B3 | `page.php:37` (leere Seite), Fehlerseite, 404 | |
| P1/P3/P4 | Abfragen je Request, Bildmaße, `defer` | |
| Q3 | Drawer-Fokus, Sticky-Header, Zielgrößen, AA-Palette (4.0) | `style.css` |
| K2 | Header „Dashboard“ für alle angemeldeten Rollen inkl. `moderator`, keine Admin-Links | `header.php:105–151` |
| K3 | Footer-Spalten/Fallback `/forum` | `footer.php` |
| K4/K5 | Token-Brücke, Header ohne `<main>` | |
| K8 | Tracking/Kapazität als GET (kein `form_guard` nötig) bestätigen | |
| K9 | Vertragstest, `php -l` | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- keine theme-spezifischen.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde

_Wird nach dem Audit ausgefüllt._
