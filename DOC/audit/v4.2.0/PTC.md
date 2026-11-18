# Audit-Plan PTC (365CMS 4.2.0)

> **PR:** 3 | **Version vor Audit:** 4.0.0 (Kit 1.0.1) | **Ziel:** 4.1.0, `tested_up_to` 4.2.0 | **Katalog:** [README.md](README.md#3-prüfkatalog)

## 1. Steckbrief

| Merkmal | Wert |
|---|---|
| Zweck | PTC GmbH – Personal & Bildung (Leistungen, Events, FAQ, Blog) |
| Dateien | 30 (6.835 Zeilen PHP, 486 Zeilen JS) – größtes Theme |
| Header öffnet `<main>` | ja |
| Dark Mode | nein |
| Eigene Vorlagen | `blog.php`, **`login.php`, `register.php`** |
| Customizer | **eigener Admin-Customizer** `admin/customizer.php` (2.772 Z.) mit Bild-Uploads, `js/customizer-admin.js` (256 Z.) |
| Besonderheit | Events aus Tabelle `events`, Tag-Wolke, Profil-Dropdown mit Admin-Link (`isAdmin()`), Netzwerk-Leiste, `custom_head_code`/`custom_footer_code` |

## 2. Angriffsfläche

- **Admin-Customizer**: Uploads (`$_FILES`, Z. 55), Speichern/Zurücksetzen (Z. 2126–2210), Bild-URLs (`ptc_customizer_safe_image_url`), rohe `$previewUrl` (Z. 2440/2454/2576/2590).
- **SQL**: `home.php:345–346` (`LIMIT {$limit}` interpoliert), `blog.php:64–75` (Kategorien, Tags **nur `status = 'published'`** – geplante Beiträge?), `blog-single.php:72–73` (verwandte Beiträge).
- **Auth-Vorlagen**: `login.php`, `register.php` (Session-Altwerte, Rechtslinks).
- **Rohe Ausgaben** in `blog-single.php` (≈15), `blog.php` (≈12), `search.php:40/80/91`, `footer.php`.
- `custom_head_code`/`custom_footer_code` roh (offen aus 4.0, CSP blockiert Inline-Scripts).
- `theme_csrf_token()/theme_csrf_field()`, `theme_get_flash()` (`functions.php:715–725`).

## 3. Prüfplan

| ID | Konkrete Prüfung | Stellen |
|---|---|---|
| S5 | `isAdmin()` + `exit` vor jeder Ausgabe, Direktaufruf | `admin/customizer.php:20–30` |
| S6 | Upload-Funktion (Z. ~40–110): Endung + `finfo`, SVG, Größe, Name, Ziel | `admin/customizer.php` |
| S8 | Speichern: Feldtypen, Arrays, `active_section` | `admin/customizer.php:2150–2215` |
| S1/S2 | `$previewUrl` roh in `src`/`value` | `admin/customizer.php:2440–2590` |
| S7 | `LIMIT {$limit}` (Integer-Herkunft), `status = ?` + `cms_post_publication_where()` für Events/Kategorien/Tags/verwandte Beiträge, `CURDATE()` vs. UTC | `home.php:340–350`, `blog.php:60–80`, `blog-single.php:70–80` |
| S1 | Jede rohe Ausgabe in `blog-single.php`, `blog.php`, `search.php`, `footer.php` auf vorheriges Escaping zurückverfolgen | s. Scan |
| S1/S9 | `custom_head_code`/`custom_footer_code`: Admin-Feld, aber CSP-Bruch → dokumentieren oder Nonce-Hinweis | `header.php`, `footer.php` |
| S4 | Login/Registrierung: Core-Token, Logout-POST (4.0) | `login.php`, `register.php`, `header.php` |
| S8 | `blog.php:227` `$_GET['q']` (Typ geprüft), Paginierung | `blog.php` |
| S10 | `customizer-admin.js`, `navigation.js` Senken | `js/` |
| S11 | Feste Netzwerklinks (`365network.de`), Demo-Daten | `footer.php`, `admin/customizer.php:758–770` |
| B1 | `ptc_format_date()`, Event-Datum | |
| B5 | Blog-Paginierung, Tag-/Kategoriefilter | `blog.php` |
| P1 | Tag-Wolke: 500 Zeilen `tags` je Blog-Aufruf parsen → Cache/Begrenzung | `blog.php:74–80` |
| P2 | `ptc_config()` (theme.json einmal je Request) | `functions.php:315–325` |
| P3 | Admin-Customizer lädt `admin.js` ohne `defer` (offen aus 4.0) | `admin/customizer.php:2228–2232` |
| Q3 | Profil-Dropdown (Disclosure), Drawer `inert`, Sticky-Header, Zielgrößen | |
| K2 | Profil-Dropdown: Admin-Link nur `isAdmin()` (Moderator sieht keinen) – bestätigt; Mitgliederlinks für `moderator` | `header.php:26–130` |
| K3 | `/forum` | |
| K4/K5 | Token-Brücke, Header-`<main>` | |
| K8 | Eigene POST-Formulare (`theme_csrf_field()`) gegen `form_guard`? | `functions.php` |
| K9 | Vertragstest | |

## 4. Offene Punkte aus dem 4.0/4.1-Audit

- `custom_head_code`/`custom_footer_code` roh, Inline-Scripts von der CSP blockiert.
- Admin-Customizer `cms_csp_runtime_tags()` ohne `true` (wegen `admin.js` ohne `defer`).
- Events-SQL nur gegen Stubs geprüft.

## 5. Abnahme

Alle Zeilen in Abschnitt 3 mit Ergebnis, Kit 1.1.0, Metadaten 4.1.0, Vertragstest und `php -l` grün.

## 6. Befunde

_Wird nach dem Audit ausgefüllt._
