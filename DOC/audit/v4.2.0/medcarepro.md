# Audit-Plan medcarepro (365CMS 4.2.0)

> **PR:** 2 | **Version vor Audit:** 2.0.0 (Kit 1.0.1) | **Ziel:** 2.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | Arztpraxis/Gesundheitsportal (Arztsuche, Termine, Ratgeber, DSGVO-Medizin) |
| Dateien | 28 (3.633 Zeilen PHP, 202 Zeilen JS) |
| Header öffnet `<main>` | nein |
| Dark Mode | nein, Großschrift- und Hochkontrastmodus (`localStorage`) |
| Eigene Vorlagen | `blog.php`, **`login.php`, `register.php`** (Legacy-Auth-Vorlagen), `partials/doctor-card.php` |
| Customizer | Core-Customizer (12 Bereiche, u. a. `dsgvo_medical`, `accessibility`) |
| PHP-Dateien ohne `strict_types` | `partials/doctor-card.php`, `error.php`, `home.php`, `index.php`, `footer.php`, `404.php`, `page.php`, `header.php` |

## 2. Angriffsfläche

- **Auth-Vorlagen**: `login.php`/`register.php` (Session-Fehler/-Erfolg, `auth_form_old`, `?type=doctor`, Redirect `header('Location: …')` Z. 21/22, `redirect`-Feld → offene Weiterleitung?).
- `search.php:19–23`: `$_GET['q']`, `$_GET['page']` (Array-Eingaben), Paginierungslinks Z. 128/132.
- `partials/doctor-card.php`: 10+ rohe Ausgaben (`$id`, `$avatarUrl`, `$name`, `$specSlug`, `$reviews`, `$profileUrl`, `$bookingUrl` …).
- `blog-single.php`: rohe Ausgaben `$title`, `$category`, `$author`, URLs.
- `navigation.js`: Pre-Paint-Script (Position/Nonce), Storage.
- Medizinischer Kontext: keine Gesundheitsdaten an Dritte, keine externen Ressourcen, Hinweise statt Alarm.

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S4 | Login/Registrierung: Formular postet an Core-Route mit Core-Token (`/login`, `/register` stehen in der `form_guard`-Bypass-Liste – prüft der Core-Handler selbst?) | `login.php`, `register.php` |
| S2 | `redirect`-Feld: nur relative Pfade (offene Weiterleitung über `?redirect=//evil`) | `login.php` |
| S2 | `header('Location: ' . theme_route_url('member'))` + `exit` vorhanden? | `login.php:21`, `register.php:22` |
| S8 | `?type[]=doctor`, `?q[]=x`, `?page[]=1`, Überlängen | `register.php:26`, `search.php:19–23` |
| S1 | doctor-card: alle Variablen vorher escaped? `$specSlug` als Klasse (Allowlist), `$id` als Integer | `partials/doctor-card.php` |
| S1 | blog-single/search: vorab escapte Variablen nachverfolgen | `blog-single.php`, `search.php` |
| S2 | `mc_safe_url()` (4.0-Fix), `mc_tel_sanitize()` | `functions.php` |
| S1 | Flash (`mc_flash`) escaped, Typ-Allowlist | `functions.php:565–585` |
| S10 | `navigation.js`: Storage nur `'1'/'0'`, Pre-Paint ohne Inline-Script ohne Nonce | `js/navigation.js` |
| S11 | DSGVO: keine Drittanbieter, Kontakt-/Notfallnummern nur aus Customizer | |
| Q1 | `strict_types` in 8 Dateien, Typfehler danach prüfen | s. o. |
| B1 | `mc_format_date()` (4.0-Fix) | |
| B5 | Suche: `page` > Trefferzahl, `page=0` | `search.php` |
| P1/P4 | Arztkarten-Avatare (Maße, lazy), Abfragen | |
| Q3 | Großschrift/Hochkontrast (offen aus 4.0: Hochkontrast-Kontraste nicht vollständig), Formularfehler mit `aria-describedby` (3.3.1), Autocomplete-Attribute (1.3.5), 3.3.8 | `login.php`, `register.php`, `style.css` |
| K2 | Kontolinks/Logout-POST für `moderator`; Registrierung: Rollenauswahl `doctor` darf keine privilegierte Rolle setzen (Core verbietet `moderator`) | `register.php` |
| K3 | `/forum` in Menüs | |
| K4/K5 | Token-Brücke inkl. Hochkontrast, Header ohne `<main>` | |
| K8/K9 | Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- Hochkontrastmodus ohne vollständiges Kontrast-Audit.
- Alte gespeicherte Sky-Blau-Werte bleiben (Hinweis im CHANGELOG).

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 2.1.0, Vertragstest und `php -l` grün.

## 6. Befunde (Audit 2026-11-18, umgesetzt in 2.1.0)

| ID | Schwere | Befund | Ergebnis |
|---|---|---|---|
| S2 | **mittel** | `mc_safe_url()` ließ `/\evil.example` als lokalen Pfad durch (Menüs, Suche, Arztkarte; Render-Test) | behoben |
| S2 | niedrig | `login.php`: Weiterleitungsziel `/\evil.example` wurde ins versteckte Feld übernommen (der Core prüft beim Login zusätzlich) | Backslash/Steuerzeichen verworfen |
| S4 | – | Login/Registrierung posten an die Core-Handler mit Core-Token (`login`/`register`), Honeypot, keine eigene Passwortprüfung, neutrale Meldungen escaped, `auth_form_old` ohne Passwörter, `header('Location')` + `exit` bei angemeldeten Konten | kein Befund |
| S8 | **mittel** | Suche: `?q[]=x` → „Array to string conversion“ und Suchbegriff „Array“; `?page[]=` | behoben (Core-`$query` zuerst, Typprüfung) |
| S1 | niedrig | Arztkarte: Fachgebietsklasse aus Rohtext (Leerzeichen → zusätzliche Klassen) | `theme_v4_slug()` |
| S1 | – | Übrige vorab escapte Variablen in `blog-single.php`, `search.php`, `home.php`, Flash mit Typ-Allowlist | kein Befund |
| S11 | – | keine Drittanbieter, keine externen Schriften (4.0) | kein Befund |
| B | **mittel** | Registrierung: `minlength="8"`/„Mindestens 8 Zeichen“ gegen Core-Standard 12 → serverseitige Ablehnung scheinbar gültiger Passwörter | aus `PasswordPolicyService` (Render-Test: `minlength="12"`) |
| B/K3 | niedrig | Arztkarte verlinkte `/termin` ohne Prüfung | nur bei vorhandenem Ziel |
| Q1 | niedrig | `strict_types` fehlte in 8 Dateien | ergänzt |
| Q2 | akzeptiert | `partials/doctor-card.php` wird von keiner Vorlage eingebunden (Baustein für eigene Seiten) | dokumentiert |
| Q3 | mittel | WCAG 2.2 2.4.11: fester Header | `scroll-padding-top` |
| Q3 | offen | Großschrift/Hochkontrast werden erst nach dem `defer`-Script gesetzt (Aufblitzen, obwohl der Kommentar „pre-paint“ sagt); die Regeln hängen an `body`-Klassen | dokumentiert (Folgearbeit: `html`-Klassen + Inline-Bootstrap) |
| UX | akzeptiert | „Als Arzt registrieren“ ändert nur Texte, die Rolle vergibt der Core | dokumentiert (Kommentar im Code) |
| K2 | – | Kontolinks/Logout-POST für jede angemeldete Rolle; keine Rollenwahl in der Registrierung | kein Befund |
| K3 | – | Fußzeile „Für Patienten“ → „Patienten-Community“ nur bei aktivem Modul | umgesetzt |
| K9 | – | `php -l`, `TESTS/theme-contract`, Render-/Fuzz-Test inkl. `login`, `register` | grün |
