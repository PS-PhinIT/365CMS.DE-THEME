# Audit-Plan newspaper (365CMS 4.2.0)

> **PR:** 2 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Nachrichtenportal (Index-Slug `cms-newspaper`, Ordner `newspaper`) |
| Dateien | 29 (3.707 Zeilen PHP, 318 Zeilen JS) |
| Header öffnet `<main>` | ja |
| Dark Mode | nein |
| Eigene Vorlagen | `blog.php`, `images/placeholder.svg` |
| Customizer | **eigener Admin-Customizer** `admin/customizer.php` (646 Z.) mit Logo-Upload, `js/customizer-admin.js` (161 Z.) |
| PHP-Dateien ohne `strict_types` | `error.php`, `home.php`, `index.php`, `footer.php`, `404.php`, `page.php`, `header.php` |

## 2. Angriffsfläche

- **Logo-Upload** `admin/customizer.php:410–450` (Endung, `finfo`, Größe, `is_uploaded_file`, Zielpfad/Name).
- Speichern: `$value = $_POST[$inputName] ?? ''` (Z. 482) ohne Typprüfung → Array-Eingaben; Erfolgsmeldung roh (`echo $success`, Z. 561).
- `blog.php:43–45`: `$_GET` → Links.
- `home.php`: dynamische Tags `<$tag>` (Z. 223/290/320/335) – nur `a`/`div`?
- Statische Partner-Links im Footer (`footer.php:46–52`, sieben fremde Domains) und Social-Links im Header.
- `theme.json`/`home.php:21`: GitHub-URLs.

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S5 | `isAdmin()` vor jeder Aktion (Z. 353) + `exit` nach Redirect | `admin/customizer.php:350–356` |
| S6 | Upload: MIME per `finfo`, SVG ausgeschlossen, Größe, zufälliger Dateiname, Zielverzeichnis innerhalb `uploads/` | `admin/customizer.php:410–455` |
| S8 | Array-Eingaben beim Speichern (Z. 482) und `active_section`; Feldvalidierung je Typ | `admin/customizer.php:455–490` |
| S1 | `echo $success` (Z. 561), `$bodyCls`, `$homeHref`, `$searchUrl`, `$cls` (Flash) | `admin/customizer.php`, `header.php:69/100/146/196` |
| S1 | `$tag`/`$tTag`/`$fTag` nur aus fester Menge | `home.php:220–340` |
| S2 | `news_safe_url()`, `news_href()`, Hero-CTA extern | `functions.php`, `home.php:197` |
| S3 | `news_css_color/_number`, eigenes CSS (`echo $custom`, Z. 220) | `functions.php` |
| S8 | `blog.php`: fremde Query-Parameter in Paginierung | `blog.php:43–45` |
| S10 | `customizer-admin.js`: Vorschau ohne `innerHTML` | `js/customizer-admin.js` |
| S11 | Partner-Links (Werbelinks fest im Code) → Customizer-Feld oder nur bei Pflege | `footer.php:46–52` |
| Q1 | `strict_types` in 7 Dateien | s. o. |
| B2 | Demo-Artikel auf der Startseite (offen aus 4.0) → echte Beiträge, Demo nur ohne Beiträge | `home.php` |
| B3/B5 | Fehlerseite, Paginierung | |
| P1/P2 | `news_config()` (theme.json je Request einmal), Startseiten-Abfragen | `functions.php:255–265` |
| P3 | Admin-Customizer-Scripts ohne `defer` (offen aus 4.0) | `admin/customizer.php:505–512` |
| P4 | Bilder/Platzhalter mit Maßen | `home.php`, `images/placeholder.svg` |
| Q3 | Drawer `hidden` + Fokus (4.0-Fix), Ticker/Animationen `prefers-reduced-motion` | `js/`, `style.css` |
| K2/K3 | Kontolinks, `/forum` in Ressort-/Footer-Navigation | |
| K4/K5 | Token-Brücke, Header-`<main>` | |
| K8/K9 | Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- Startseite zeigt statische Demo-Artikel.
- Admin-Customizer ohne `defer`.
- Kontrast-Audit nur Pflichtpunkte.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde

_Wird nach dem Audit ausgefüllt._
