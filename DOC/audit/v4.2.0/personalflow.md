# Audit-Plan personalflow (365CMS 4.2.0)

> **PR:** 2 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Personalvermittlung/Recruiting (Jobs, Kandidaten, Pipeline) |
| Dateien | 25 (2.897 Zeilen PHP, 157 Zeilen JS) |
| Header öffnet `<main>` | nein |
| Dark Mode | nein |
| Eigene Vorlagen | `partials/candidate-card.php` |
| Customizer | Core-Customizer (11 Bereiche, u. a. `pipeline`, `hr_hero`) |
| PHP-Dateien ohne `strict_types` | `partials/candidate-card.php`, `home.php`, `index.php`, `footer.php`, `404.php`, `page.php`, `header.php` |

## 2. Angriffsfläche

- **GET-Aktionslinks** `?action=contact` (`candidate-card.php:116`) und `?action=save` (`home.php:280`) – zustandsändernde Aktion per GET (CSRF) bzw. tote Links.
- Rohe Ausgaben: `candidate-card.php:113–116`, `home.php:254/279/280`, `header.php:48–147`, `footer.php:69–101`.
- Flash (`functions.php:530–550`), eigenes CSS (`echo $custom`, Z. 283).
- `.pf-reveal` blendet Inhalte ohne JS aus (offen aus 4.0).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S4 | `?action=save`/`?action=contact`: wer verarbeitet das? Falls niemand → Links entfernen/auf echte Ziele; falls Zustand ändert → POST + CSRF | `home.php:280`, `partials/candidate-card.php:116` |
| S1/S2 | `$jHref`, `$profileHref` aus Inhalten/Customizer → `pf_href()` + Escaping, Anhängen von `?action=` an URLs mit vorhandenem Query | `home.php`, `partials/candidate-card.php` |
| S1 | Kandidatenkarte: Name, Skills, Avatar (nur lokale Uploads, 4.0-Fix) | `partials/candidate-card.php` |
| S1 | Flash, `$pfBodyCls`, `$pfCopyright` | `functions.php`, `header.php:48`, `footer.php:101` |
| S3 | `pf_css_color/_number`, eigenes CSS | `functions.php` |
| S11 | Kandidaten-Demo-Daten: echte personenbezogene Daten? (DSGVO) | `home.php`, `theme.json` |
| Q1 | `strict_types` in 7 Dateien | s. o. |
| B6 | `.pf-reveal` ohne JS sichtbar (nur mit `.pf-js` verstecken) | `style.css`, `js/navigation.js` |
| B2/B3/B5 | leere Jobs/Kandidaten, Fehlerseite, Paginierung | |
| P1/P4 | Abfragen, Avatare mit Maßen | |
| Q3 | Fokusring (4.0), Drawer, Zielgrößen, Pipeline-Stufen nicht nur über Farbe (1.4.1) | `style.css` |
| K2 | „Mein Bereich“ für `moderator` | `header.php:94` |
| K3 | `/forum` | |
| K4/K5 | Token-Brücke, Header ohne `<main>` | |
| K8/K9 | Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- `.pf-reveal` blendet Startseiteninhalte ohne JavaScript aus.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde (Audit 2026-11-18, umgesetzt in 2.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `theme_nav_menu()`: `//evil.example` und `/\evil.example` als Link durchgelassen; `mailto:`/`tel:` → `#` | `pf_menu_url()` |
| S4 | mittel | GET-Aktionslinks `?action=save` („Merken“) und `?action=contact` („Kontakt aufnehmen“) ohne Handler – täuschten eine Funktion vor; eine echte Merkfunktion per GET wäre CSRF-anfällig; zudem an URLs mit vorhandenem Query falsch angehängt | entfernt |
| S1 | – | Kandidatenkarte und Stellen: alle Ausgaben escaped, Avatare nur lokale Uploads mit Maßen (4.0), Links nur bei vorhandenem Ziel | kein Befund |
| S1 | – | Flash mit Typ-Allowlist, `$pfBodyCls`/`$pfCopyright`/`$pfTitleEsc` escaped | kein Befund |
| S3 | – | `pf_css_color/_number`, eigenes CSS bereinigt | kein Befund |
| S11 | akzeptiert | Demo-Kandidat:innen und -Stellen mit erfundenen Namen, ohne Fotos | dokumentiert |
| B6 | **mittel** | `.pf-reveal` ohne JS unsichtbar (offen seit 2.0.0) | nur mit `html.pf-js` |
| Q1 | niedrig | `strict_types` fehlte in 7 Dateien | ergänzt |
| Q3 | mittel | WCAG 2.2 2.4.11: fester Header | `scroll-padding-top` |
| Q3 | – | Pipeline-Stufen mit Text (nicht nur Farbe), Fokusring (4.0) | kein Befund |
| K2 | – | „Mein Bereich“ für jede angemeldete Rolle | kein Befund |
| K3 | – | Fußzeile „Wissen & Kontakt“ → „Community“ nur bei aktivem Modul | umgesetzt |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test | grün |
