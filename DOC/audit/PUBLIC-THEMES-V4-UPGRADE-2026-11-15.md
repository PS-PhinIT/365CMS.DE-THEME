# Audit- & Upgrade-Protokoll: Public Themes auf 365CMS 4.0.0 / 4.1.0

> **Stand:** 2026-11-15 | **Core-Referenz:** `PS-PhinIT/365CMS.DE` (`CMS\Version::CURRENT` 4.0.0, 4.1.0 in Entwicklung, `MIN_PHP` 8.4.1) | **Repository:** `PS-PhinIT/365CMS.DE-THEME`
> **Ausgeschlossen:** das private Theme `cms-phinit` (weder geprüft noch verändert, nicht in öffentliche Manifeste aufgenommen).

## Inhalt

1. [Inventar](#1-inventar)
2. [Prüfkatalog](#2-prüfkatalog-5-phasen)
3. [Gemeinsame 4.x-Schicht (Kit)](#3-gemeinsame-4x-schicht-kit-101)
4. [Befunde und Umsetzung je Theme](#4-befunde-und-umsetzung-je-theme)
5. [cms-default (Core-Repository)](#5-cms-default--meridian-cms-default-core-repository)
6. [Marketplace-Index](#6-marketplace-index-indexjson)
7. [Prüfungen](#7-prüfungen)
8. [Offene Punkte](#8-offene-punkte)

## 1. Inventar

| Theme (Ordner) | Name | Version alt → neu | Header öffnet `<main>` | Dark Mode | Login-Vorlagen | Status |
|---|---|---|---|---|---|---|
| `academy365` | Academy365 | 3.1.0 → 4.0.0 | nein | nein (`supports` bereinigt) | – | [x] (gemergt, [PS-PhinIT/365CMS.DE-THEME#3](https://github.com/PS-PhinIT/365CMS.DE-THEME/pull/3)) |
| `buildbase` | BuildBase | 3.1.0 → 4.0.0 | nein | nein (`supports` bereinigt) | – | [x] |
| `business` | Business Presentation | 3.1.0 → 4.0.0 | ja | nein | – | [x] |
| `feder` | Feder | 1.0.0 → 2.0.0 | ja | ja (`data-theme`, `prefers-color-scheme`) | – | [x] |
| `kompass` | Kompass | 1.0.0 → 2.0.0 | ja | nein (Hochkontrastmodus) | – | [x] |
| `kontor` | Kontor | 1.0.0 → 2.0.0 | ja | nein | – | [x] |
| `logilink` | LogiLink | 1.1.0 → 2.0.0 | nein | nein (`supports` bereinigt) | – | [x] |
| `medcarepro` | MedCare Pro | 1.1.0 → 2.0.0 | nein | ja | `login.php` | <!--C-medcarepro--> |
| `newspaper` | CMS Newspaper (Index-Slug `cms-newspaper`) | 1.1.0 → 2.0.0 | ja | ja | – | <!--C-newspaper--> |
| `personalflow` | PersonalFlow | 1.1.0 → 2.0.0 | nein | nein | – | [x] |
| `PTC` | PTC – Personalvermittlung & Schulungen | 3.1.0 → 4.0.0 | ja | nein | `login.php`, `register.php` | [x] |
| `rundschau` | Rundschau | 1.0.0 → 2.0.0 | ja | ja | – | <!--C-rundschau--> |
| `technexus` | TechNexus | 1.1.0 → 2.0.0 | nein | ja (`data-theme`) | – | [x] |
| `cms-default` | Meridian CMS Default | 1.1.0 (unverändert) | ja | nein | `login.php`, `register.php`, `forgot-password.php` | [ ] nur Audit, liegt im Core-Repository (siehe Abschnitt 5) |

Ein Theme ist erst dann abgehakt (`[x]`), wenn alle fünf Phasen umgesetzt sind, `php -l` für alle PHP-Dateien fehlerfrei ist und alle Metadaten nachgezogen sind: `theme.json` (`requires_cms` 4.0.0, `tested_up_to` 4.1.0, `requires_php` 8.4.1), `style.css`-Header, die Versionskonstante in `functions.php`, `update.json`, `CHANGELOG.md` und `README.md`.

Versionsschema: Weil die Mindestversion des Cores auf 4.0.0 steigt, bekommt jedes Theme eine neue Major-Version (3.x → 4.0.0, 1.x → 2.0.0).

## 2. Prüfkatalog (5 Phasen)

1. **Core-Verträge 4.0.0:** `error.php`/`<status>.php` mit `$error_code` (Fallback `$errorCode`), `$error_standalone`, `$error_retry_after`; Hook `main_nav` (`desktop`/`mobile`) nach den Menüeinträgen; Auth-Helfer nur über `Auth::login()/register()` mit neutralen Meldungen; Logout als CSRF-POST (`Security::generateToken('logout')`); entfernte 3.5.x-APIs; PHP 8.4.1.
2. **Templates & Core-Module:** `contact.php`; `newsletter/*`, `knowledgebase/*`; Downloads/Feeds (über `page.php`) inkl. Block-Layout `button`; Medien-Credits, Editor.js-Klassen, Hub-Sites, `show_title_toc`; Standardlinks nur zu vorhandenen Zielen, `site_logo`/`site_favicon`.
3. **4.1.0:** Memberdashboard im Theme-Designsystem (inkl. Newsletter-Widget), Login-Showcase, Fokus-Punkt (`focal_x`/`focal_y`), wiederverwendbare Blöcke, Vorschau ohne Cache-/Schema-Leakage.
4. **Sicherheit & CSP:** `cms_csp_runtime_tags(true)` als erstes Script, keine Inline-Handler/`javascript:`, Nonces, keine HTML-Sinks, Escaping, keine externen Ressourcen.
5. **Performance & WCAG 2.1 AA:** OptionStore, keine N+1-Queries, `width`/`height`, `loading="lazy"`/`fetchpriority`, `defer`; Skip-Link, ein `<main>`, `:focus-visible`, `aria-current`, Tastaturbedienung, Überschriftenhierarchie, Kontraste in Light/Dark.

## 3. Gemeinsame 4.x-Schicht (Kit 1.0.1)

In allen 13 Public Themes liegen dieselben Dateien, byte-gleich (Prüfung: `cmp`):

| Datei | Zweck |
|---|---|
| `includes/cms-v4-compat.php` | Helfer `theme_v4_*`, alle über `function_exists()` geschützt: `error_context()`/`error_texts()`/`standalone_open()` (Fehlerseiten ohne DB), `main_nav()` (Core-Einträge, bei Listenmenüs in `<li>`), `link_available()`/`filter_links()` (Routen, Feed-Aggregator, veröffentlichte Seiten), `option()`/`options()` (OptionStore), `head_tags()`/`document_title()`/`is_preview()` (Core-SEO, Vorschau mit `noindex` und ohne JSON-LD), `logout_form()`, `render_module_view()` (`<main>` der Core-Views), `img()`/`image_dimensions()`/`focal_position()` (CLS, LCP, `object-position`), `is_member_area()`/`member_tokens_style()` (`--member-*`; Admin-Farben haben Vorrang), `site_logo()`/`favicon_tag()`. |
| `css/cms-v4.css` | Token-Brücke `--tv4-*` → `--kb-*`, `--fd-*` (nur `.fd-aggregator`/`.fd-body`), `--cms-hub-*`, `--cms-form-*`, `--color-*` an `.cms-dl*`/`.cms-newsletter*`; Stile für `cms-media-credit`, Editor.js (Bild, Medien-Text, Zitat, Button), `.cms-toc`/`.cms-page-title-toc`, `.cms-reusable-block`, Kontaktseite, `.cms-auth-showcase`, Vorschau-Hinweis, eigenständige Fehlerseiten, Fokusrahmen. |
| `knowledgebase/{archive,single}.php`, `newsletter/{page,action,issue}.php` | Binden die Core-View ein und lassen Daten, CSRF, Honeypot und `show_category_sidebar` unverändert. Angepasst wird nur `<main>` an den Theme-Rahmen (`THEME_V4_HEADER_HAS_MAIN`, `THEME_V4_MAIN_ID`). |

Theme-spezifisch: Abschnitt „365CMS 4.x Token-Brücke“ in `style.css`, `contact.php`, `login_page.colors` in `theme.json`, `member_head`-Callback in `functions.php`.

Kit 1.0.1 (gegenüber 1.0.0 aus PR #3, auf alle Themes verteilt), nach Befunden aus dem Rollout:
- `--fd-*` wird nicht mehr global auf `body` gesetzt, sondern auf `.fd-aggregator`/`.fd-body`. Sonst wären Themes mit eigenem `fd-`-Präfix (feder) auf allen Seiten umgefärbt worden.
- Der Schlüssel `focus` erzeugt einen gültigen `box-shadow`-Wert für `--member-focus-ring`. Bisher stand dort eine Farbe, der Core erwartet aber einen Schatten.
- Keine dunkle Member-Palette mehr: Die Tabler-Shell hat keinen Dunkelmodus, sodass `.text-secondary` auf dunklen Karten bei ca. 3,6:1 läge.

## 4. Befunde und Umsetzung je Theme

Für alle Themes umgesetzt (Details unten nur, wo abweichend oder auffällig):
- **1:** `error.php` nach Router-Vertrag; `main_nav`; `privacy_use_local_fonts` per OptionStore statt SQL.
- **2:** `contact.php`; Kit-Overrides; `cms-v4.css` und Token-Brücke; Standardlinks gefiltert; Logo-/Favicon-Fallback.
- **3:** `member_head` statt Frontend-Assets in der Member-Shell; `login_page.colors`; LCP-Bild über `theme_v4_img()`; `theme_v4_head_tags()` mit Vorschaufall.
- **4:** `<meta charset>` zuerst, dann `cms_csp_runtime_tags(true)`; Google-Fonts-Requests vollständig entfernt (System-Fallbacks).
- **5:** `html lang` nach Inhaltssprache; Kontraste der Standardpalette geprüft (`contrast.py`).

### academy365 — 3.1.0 → 4.0.0 ([PS-PhinIT/365CMS.DE-THEME#3](https://github.com/PS-PhinIT/365CMS.DE-THEME/pull/3), gemergt; Referenzumsetzung)
- [x] **1:** `error.php` las nur `$errorCode` und rief `get_header()` (im Fatal-Handler mit DB) → Vertrag. Menüs lasen nur `label` (Core liefert `title`), kein `aria-current` → behoben. `main_nav` einmal (`desktop`, eine responsive Navigation).
- [x] **2:** Footer verlinkte fest `/courses`, `/certificates`, `/tutor-*`, `/about` u. a. → nur bei vorhandenem Ziel; Customizer-Bereich `contact` neu.
- [x] **3:** Beitragsbild `loading="lazy"` → LCP mit `width`/`height`/`fetchpriority`.
- [x] **4:** Nur Site-Titel/-Beschreibung im Head, keine Core-SEO-Tags → `theme_v4_head_tags()`.
- [x] **5:** Badges 2,0–3,4:1 → 5,2–6,8:1; Muted `#9ca3af` 2,4:1 → `#6b7280` 4,5:1; Fokus Gold 2,2:1 → Indigo 9,0:1 (dunkle Flächen Gold 7,4:1); Footer `h4` → `h2`; `supports: dark-mode` ohne Dark Mode entfernt.

### buildbase — 3.1.0 → 4.0.0
- [x] **1:** `error.php` mit eigenem `get_header()/get_footer()` (doppelter Rahmen, DB im Fatal-Handler), nur `$errorCode`. Aktive Menüeinträge hatten keinen Stil.
- [x] **2:** Footer-Ziele (`/handwerker`, `/pricing` …) gefiltert, neue Spalte „Service“; die registrierten Menüs `footer-nav`/`footer-legal` wurden nie ausgegeben; Copyright zeigte `&amp;copy;`; `index.php` ohne Paginierung → ergänzt; Hero-CTAs/Referenzen nur bei Ziel.
- [x] **4:** 7 Google-Font-Familien je Seite; Font-Stacks HTML-escaped ins CSS geschrieben (`--font-heading` ungültig).
- [x] **5:** Mapping-Fehler `section_dark_bg` → `--bg-secondary`: helle Alt-Sektion dunkel bei dunkler Schrift (1,15:1 / 2,14:1 → 16,0 / 6,5). Muted 4,40 → 5,33; Hero-Outline 2,05 → 4,78; Fokus 3,05 → 6,79 (dunkel 4,78–8,14). `.bb-card` war ohne JS `opacity: 0` (leere Wartungsseite) → nur mit `.bb-js`. `role="alert"` am statischen Banner entfernt; Escape schließt Menü/Suche; Footer `h3` → `h2`; `supports: dark-mode` entfernt.

### business — 3.1.0 → 4.0.0
- [x] **1:** `error.php` las nur `$GLOBALS` (alles HTTP 500). `registerMenuLocation()` und `seedDefaultMenus()` (DB-Schreibzugriff pro Request) entfernt; `getConfig()` hing an `THEME_PATH . 'business/'`.
- [x] **2:** `page.php` filterte das Core-HTML (`biz_sanitize_content_html()`) → unverändert ausgegeben; CTA fest `mailto:info@example.com`/`tel:+491234567890` → `/contact` + Customizer; Social-Icons mit `href="#"` entfernt; Blätter-Buttons weiß auf weiß; `header.php` überschrieb `$title`.
- [x] **5:** Links/Eyebrows `#c08a2e` 3,04 / 2,79 → `#8a5f17` 5,63 / 5,17; Primär-Hover 4,37 → 5,63; Outline-Rand 2,89 → 5,31; Fokus 2,79 → 5,17 (dunkel 6,12 / 5,11); Drawer `inert`, Escape-Fehler, Footer `h4` → `h2`.

### feder — 1.0.0 → 2.0.0
- [x] **1:** `error.php` las `$GLOBALS['error_code']`, ohne Standalone/Retry/`<main>`-Fall. Kategoriezählung als Unterabfrage je Kategorie (N+1) → `JOIN` + `GROUP BY` mit Request-Cache.
- [x] **2:** **Breaking:** Das Präfix `fd-` (Klassen und Custom Properties) kollidierte mit dem Core-Feed-Aggregator (`.fd-header`, `.fd-card`, `.fd-main`, `--fd-*`) → komplett auf `fdr-` umbenannt (eigenes Customizer-CSS anpassen, steht im CHANGELOG). Newsletter-Box zielte auf `/kontakt` → `/newsletter`; `/ueber-uns` nur bei vorhandener Seite.
- [x] **3:** Vorschaufall war nicht behandelt (`renderCurrentHeadTags()` direkt) → `theme_v4_head_tags()`. Member-Shell bewusst nur hell.
- [x] **5:** Kontraste bestanden bereits (Text 13,1 / 14,8, Muted 5,6 / 7,0, Akzent 7,7 / 7,9 hell/dunkel), unverändert; neu `border-strong` ≥ 3,4:1.

### kompass — 1.0.0 → 2.0.0
- [x] **1:** wie feder (Fehlerseite, N+1-Zählung inkl. `post_category_rel` → vorab gruppierte `UNION`).
- [x] **2:** Schnellzugriffe (`/hilfe-und-faq`, `/ueber-uns`, `/datenschutz`), FAQ, Service-Button, `/barrierefreiheit` und PDF-Link nur bei vorhandenem Ziel; Kontaktdaten aus `kp_service`.
- [x] **5:** Kontraste bestanden bereits (Text 15,7, Muted 7,3, Link 8,4); Hochkontrastmodus (`data-contrast="high"`) erhält eigene Brückenwerte (19,6–21:1).

### kontor — 1.0.0 → 2.0.0
- [x] **1:** wie feder.
- [x] **2:** Linkprüfung zentral in `kontor_button()` (Header, Hero, `/ueber-uns`, CTA-Band, Kontaktkarte, 404); Kontaktdaten aus `kt_contact`.
- [x] **5:** Fokusrahmen auf hellen Flächen Mint 1,80:1 → Tinte 14,42:1; übrige Paare bestanden (Text 14,7, Muted 5,6, Primär 6,5).

### logilink — 1.1.0 → 2.0.0
- [x] **1:** Fehlerseite (`$errorCode`, eigenes `get_header()`), Menüs nur `label`, `registerMenuLocation()` entfernt; `main_nav` für Desktop und den separaten Drawer.
- [x] **2:** `/tracking`, `/partners`, `/routes` (Footer, 404, Hero, Schnellsuche) nur bei Ziel; leere Footer-Spalten entfallen.
- [x] **3:** Feste Tags `robots index,follow`, `og:url` (immer Startseite) und `description` entfernt.
- [x] **4:** Customizer-Farben/-Zahlen nur HTML-escaped (Ausbruch aus dem Style-Block mit `;}` möglich) → `ll_css_color()`/`ll_css_number()`; Copyright roh → escaped.
- [x] **5:** `theme.json`-Defaults wichen von der AA-Palette in `style.css` ab → angeglichen: Weiß/Primär 4,1 → 5,7; Muted 4,46 → 5,0; Badges 1,9–4,0 → 4,5–6,2; Fokus 2,0 → 7,2 (dunkel 8,1); Eyebrow 3,0 → 6,0. „Alle Beiträge“ weiß auf weiß, Drawer-CTA dunkel auf dunkel; geschlossener Drawer per Tab erreichbar → `visibility: hidden` + Fokusführung; Footer `h3` → `h2`; `supports: dark-mode` entfernt.

### personalflow — 1.1.0 → 2.0.0
- [x] **1:** wie logilink; `pf-reveal` machte die eigenständige Fehlerkarte unsichtbar.
- [x] **2:** `/jobs`, `/kandidaten`, `/arbeitgeber`, `/pipeline`, `/preise` nur bei Ziel; Avatare nur aus lokalen Uploads mit `width`/`height`; Stellentitel `div` → `h3`.
- [x] **5:** Fokus (Schatten 22 %, 1,4:1) → Ring `#92400e` 6,9:1 (CTA weiß 5,0, Footer 14); Muted 4,47 → 5,4; Formularrand 1,5 → 3,4; unsichtbarer Button und fokussierbarer Drawer wie logilink.

### PTC — 3.1.0 → 4.0.0
- [x] **1:** `error.php` las nur `$GLOBALS['error_code']` (jede Fehlerseite HTTP 500) und lud im Fatal-Handler `functions.php`. `seedDefaultMenus()` schrieb bei jedem `cms_init` in die DB → Laufzeit-Defaults. Logout per GET-Link mit Token in der URL → POST-Formular. `login.php`/`register.php`: Altwerte aus leerem `$_POST` → `$_SESSION['auth_form_old']`; Passwort-Hinweise aus `PasswordPolicyService` statt fest 12 Zeichen; Rechtsseiten nur bei Existenz. Events-Abfrage band `LIMIT ?` als String → geprüfter Integer + `published`.
- [x] **2:** `page.php`/`blog-single.php` filterten das Core-HTML mit `strip_tags()` (Editor.js-`<picture>`, Download-/Formular-Blöcke, SVG gingen verloren) → unverändert. Ungültiges Datum → `TypeError`; fehlende Inhalte → `404.php` statt verschachteltem `render()`/`Location` nach Ausgabe. `ptc_href('#x')` → `/x`; `after_header` doppelt; Copyright unescaped.
- [x] **3:** Login-Showcase im Legacy-Modus nach der Karte.
- [x] **4:** Customizer-Schrift HTML-escaped im `<style>`; Schriftgewicht/Transform nur aus Allowlist.
- [x] **5:** Akzent-Button 2,36 → 5,17; Primär-Hover ~1,1 → 12,2; Muted 2,56 → 5,50; Drawer-Untermenü weiß auf weiß → 7,58; Akzent auf Dunkel 2,8 / 1,8 → Mint 8,4 / 5,4; Untermenüs per `:focus-within`, Escape, Drawer `inert`, Profil-Dropdown als Disclosure; Footer `h4` → `h2`, Events `h4` → `h3`.

### technexus — 1.1.0 → 2.0.0
- [x] **1:** Fehlerseite, Menüs, `registerMenuLocation()`; Logout-GET-Link (`theme_logout_url()`) → POST-Formular. `LandingPageService` las `text` statt `description` (leere Feature-Karten), `h1` → `h3`-Sprung → `h2`.
- [x] **2:** `page.php` zeigte Inhalte ohne Titel nicht (Downloads/Feeds als „Seite nicht gefunden“); Logo-Link `…//`; `/it-experts`, `/companies`, `/events`, `/jobs` nur bei Ziel.
- [x] **5:** Dark Mode war wirkungslos (Customizer-`:root` überschrieb `[data-theme="dark"]`) → `:root[data-theme="dark"]`. Mobiles Kartenraster mit 2–3 Spalten → einspaltig. Hell: Weiß/Primär 3,7 → 5,4, Code 2,2 → 4,7, Fokus 1,7 → 5,1; Dunkel: Link 8,1, Muted 5,7, Fokus 9,9 (alter Link 2,8). `aria-pressed` am Farbschalter, Fokusführung im Mobilmenü, ISO-`datetime`.

<!--C-SECTIONS-->

## 5. cms-default / Meridian CMS Default (Core-Repository)

`cms-default` wird mit dem Core ausgeliefert (`PS-PhinIT/365CMS.DE`, `CMS/themes/cms-default`). Laut Auftrag bleibt der Core unverändert, und diese Sitzung hatte dort nur Lesezugriff. Das Theme ist deshalb nur geprüft. Version 1.1.0 erfüllt bereits die 4.0.0-Kernverträge: `error_code`/`errorCode`, `main_nav` für Desktop und Mobil, neutrale Auth-Helfer, Logout per POST, `contact.php` und Standardlinks nur zu vorhandenen Seiten.

Befunde für einen Folge-PR im Core-Repository:

| Phase | Befund |
|---|---|
| 1 | `error.php` wertet `error_retry_after` nicht aus und gibt im eigenständigen Fall nur ein Fragment aus (der Router bettet es ein). |
| 2 | Keine Overrides für `knowledgebase/`/`newsletter/`: Die Core-Views bringen eigenes `<main>` mit, während `header.php` bereits `<main>` öffnet → verschachtelte Landmarks. |
| 3 | Im Memberbereich wird `style.css` über `head` in die Tabler-Shell geladen; kein `member_head`-Token-Mapping. |
| 4 | Google Fonts (`fonts.googleapis.com`/`fonts.gstatic.com`) werden geladen, wenn `privacy_use_local_fonts` aus ist. `cms_csp_runtime_tags()` wird ohne `true` aufgerufen. `js/theme.js:218–222` setzt `innerHTML` (statischer Wert, unter Trusted Types dennoch ein Sink). Inline-`style`-Attribute im mobilen Menü. |
| Metadaten | In `theme.json` fehlen `requires_cms`, `tested_up_to` und `requires_php`; in `update.json` fehlen `requires_php` und `tested_up_to`. |

## 6. Marketplace-Index (`index.json`)

Nicht geändert, nur festgehalten:
- `cms-showcase` ist noch eingetragen, obwohl der Ordner mit Commit `a22fdc2` entfernt wurde.
- `cms-newspaper` verweist auf `THEMES/cms-newspaper/update.json`; der Ordner heißt seit `a22fdc2` `newspaper`.
- `cms-default` ist eingetragen, liegt aber im Core-Repository.

## 7. Prüfungen

Für jedes Theme auf seinem Branch und abschließend auf dem Sammel-Branch:
- `php -l` für alle PHP-Dateien: fehlerfrei (lokal PHP 8.3; keine 8.4-only-Syntax verwendet; keine implizit nullable Parameter, kein `E_STRICT`/`utf8_encode`/`strftime`).
- `php TESTS/theme-contract/run.php <repo>` (Core): `Theme contract tests passed (13 themes)`.
- `smoke.php`: Laufzeit-Test der Kit-Helfer gegen `CMS\Hooks` (`main_nav`-Wrapping, `is_current_path`, `link_available`, Modul-`<main>`, Fokus-Punkt, Fehlerkontext snake/camel/ungültig, Vorschau-Head) und `error.php` eigenständig (genau ein `<main>`, vollständiges Dokument) bzw. als Fragment (403).
- Render-Tests der Agents mit Core-Stubs (home, page, page mit `id=0`, Entwurf, single, blog, search, 404, contact, error 403): je ein `<main>`, Skip-Link-Ziel vorhanden, keine PHP-Warnungen, Inline-Tags nur mit Nonce, Vorschau mit `noindex`.
- Token-Scan von `functions.php` wie `ThemeManager::hasUnsafeCode()`: sauber.
- Grep auf `fonts.googleapis`, `fonts.gstatic`, CDN, `innerHTML`, `onclick=`, `javascript:`: Treffer nur noch in Kommentaren und alten Changelog-Einträgen.
- JSON valide.

## 8. Offene Punkte

- **Browser-, axe- und Lighthouse-Prüfung** gegen eine laufende 4.0.0/4.1.0-Installation steht aus. Tastaturbedienung, Fokusreihenfolge und Kontraste sind statisch und per gerendertem HTML geprüft, nur für die Standardpaletten. Eigene Customizer-Farben wurden nicht geprüft.
- **Neue SQL-Abfragen** (Kategoriezählung feder/kompass/kontor, Events PTC) sind nur gegen Stubs gelaufen; `ONLY_FULL_GROUP_BY` ist berücksichtigt.
- **Wartungsmodus** (`error_standalone`) ist nur im Smoke-Test geprüft.
- **Fokus-Punkt:** Der Core 4.1.0 speichert `focal_x`/`focal_y` noch nicht (die Medien-Normalisierung verwirft unbekannte Felder). Die Themes lesen den Wert vorwärtskompatibel aus dem Inhalt (`featured_image_focal_x` …) oder über den Filter `cms_media_focal_point`.
- **PTC:** `custom_head_code`/`custom_footer_code` werden roh ausgegeben, Inline-Scripts darin blockiert die CSP. Der Admin-Customizer lädt `cms_csp_runtime_tags()` ohne `true`, weil `admin.js` ohne `defer` geladen wird.
- **technexus:** Ohne gespeicherte Wahl wird `prefers-color-scheme` nicht ausgewertet. Vor dem `defer`-Script ist ein kurzes Aufblitzen des falschen Farbschemas möglich.
- **personalflow:** `.pf-reveal` blendet Startseiteninhalte ohne JavaScript aus (bestehendes Design).
- **Demo-Inhalte:** Statische Kennzahlen, Demo-Kontaktdaten und Platzhalter-Kurse bleiben unverändert, etwa business „200+ Projekte“, kompass `+49 123 456 789` und die academy365-Kurse.
- **cms-default:** Folge-PR im Core-Repository (Abschnitt 5).
- **index.json:** Bereinigung `cms-showcase`/`cms-newspaper` (Abschnitt 6).
