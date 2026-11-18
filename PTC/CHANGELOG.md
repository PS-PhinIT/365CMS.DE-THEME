# PTC Theme – Changelog

## 4.1.0 - 2026-11-18

### Audit 365CMS 4.2.0 (Plan und Befunde: `DOC/audit/v4.2.0/PTC.md`)

#### Sicherheit
- **Geplante Beiträge vorzeitig sichtbar:** „Verwandte Beiträge“ unter einem Artikel sowie Kategorien und Schlagwörter in der Blog-Seitenleiste filterten nur `status = 'published'`. Beiträge mit Veröffentlichungsdatum in der Zukunft erschienen dadurch vorab mit Titel, Auszug und Bild. Alle drei Abfragen nutzen jetzt `cms_post_publication_where()` des Cores.
- **Custom Head/Footer Code** wurde roh ausgegeben (Stored XSS, sobald die CSP abgeschaltet ist). Head-Code: nur noch `<meta>`- und `<link>`-Tags ohne `http-equiv` und Event-Handler. Footer-Code: Scripts, iframes, `object`/`embed`, Formulare, Event-Handler und `javascript:`/`data:`-Ziele (auch Entity-kodiert) werden entfernt. Unter der 365CMS-CSP hätten diese Inhalte ohnehin nicht funktioniert.
- `ptc_safe_url()` lehnt `/\host` ab und verwirft Pfade mit Steuerzeichen statt sie zu bereinigen.
- `login.php`: Das Weiterleitungsziel wird zusätzlich auf interne Pfade geprüft.
- Customizer-Upload: Mehrfach-Uploads (`feld[]`) werden abgewiesen statt mit „Array to string conversion“ verarbeitet.

#### 365CMS 4.2.0
- Gemeinsame Schicht **Kit 1.1.0** (`includes/cms-v4-compat.php`, `css/cms-v4.css`): Modulprüfung, Community-Helfer (nur bei aktivem Modul und vorhandenen Core-Vorlagen), `theme_v4_user_can()` (Rolle `moderator` ohne Admin-Zugang; der Admin-Link im Profilmenü hängt weiter an `isAdmin()`), `theme_v4_slug()`, Token-Brücke `--community-*`; Härtungen bei Backslash-Adressen und Unterverzeichnis-Erkennung.
- Standard-Footermenü: Eintrag „Community“, sobald das Modul aktiv ist.
- WCAG 2.2 (2.4.11): `scroll-padding-top` hält Fokus und Sprungziele unter dem festen Header sichtbar.
- `tested_up_to` 4.2.0; `requires_cms` bleibt 4.0.0.

## 4.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus (bisher nur `$GLOBALS['error_code']`, Status immer 500 bei fehlender Variable). 403-, 429- und 503-Seiten antworten mit ihrem Status; eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet und genau einem `<main>` (ohne Header, Hooks, Datenbank, Customizer); im normalen Fall kein zweites `<main>`, weil `header.php` es öffnet. Der Fatal-Handler lädt nur noch `includes/cms-v4-compat.php` statt der kompletten `functions.php`.
- Desktop-Navigation und Mobil-Drawer lösen den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Kontexte `desktop` und `mobile`), auch bei leerem Menü.
- Menüeinträge zeigen die Beschriftung aus `title` (der Core liefert `title`, das Theme las nur `label`); aktive Einträge tragen `aria-current="page"`. Anker-Einträge (`/#…`) gelten nicht mehr alle gleichzeitig als aktiv.
- `ThemeManager::registerMenuLocation()` (entfernt) wird nicht mehr aufgerufen; Menüpositionen nur über `register_menu_locations`. Die Standardmenüs wurden bisher bei jedem Request (`cms_init`) in die Datenbank geschrieben; sie gelten jetzt nur zur Laufzeit, solange kein Menü gepflegt ist, und verlinken nur vorhandene Seiten und Routen.
- Abmelden überall als POST-Formular mit CSRF-Token (`theme_v4_logout_form()`); `theme_logout_url()` mit Token in der Adresse ist entfernt.
- `login.php`/`register.php` senden weiter an den Core (`Auth::login()`/`Auth::register()` über `PublicRouter`); Meldungen kommen neutral aus dem Core. Neu: Altwerte aus `$_SESSION['auth_form_old']` (bisher `$_POST`, nach dem Redirect leer), Weiterleitungsziel `$login_redirect`, „Angemeldet bleiben“, „Passwort vergessen?“, Mindestlänge und Zeichenklassen aus der Passwort-Richtlinie (bisher fest 12 Zeichen), Hinweis bei deaktivierter Registrierung, Rechtstext-Links nur zu veröffentlichten Seiten, doppeltes `class`-Attribut am Absende-Button entfernt.
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den OptionStore statt per SQL.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit den vorhandenen Kontaktfeldern des Footer-Customizers aus (E-Mail-Fallback `ADMIN_EMAIL`).
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` binden die Core-Views im Theme-Rahmen ein (kein zweites `<main>`, Skip-Link-Ziel `#main-content`).
- Neu: `css/cms-v4.css` und `includes/cms-v4-compat.php` (gemeinsam mit allen Public Themes) sowie die Token-Brücke `--tv4-*` in `style.css`.
- `page.php` und `blog-single.php` geben das gerenderte Core-HTML unverändert aus; `strip_tags()` entfernte bisher `<picture>`, `<form>`, `<button>`, `<input>` und `<svg>` (Editor.js-Bilder mit WebP/AVIF, Formular-, Download- und Feed-Blöcke). `page.php` läuft auch für Downloads und Feeds (`id` 0, `content_updated_at`) ohne Warnungen und in voller Inhaltsbreite; ungültige Datumswerte führen nicht mehr zu einem TypeError.
- `blog-single.php` ohne Beitrag: 404 statt `Location`-Header nach bereits gesendetem Header.
- Blog, Kategorie- und Schlagwort-Archive: Titel aus `$category`/`$tag` des Cores, Beitrags-Links über den PermalinkService, Kategorie-/Tag-Links auf die kanonischen Archiv-URLs (statt `/blog?category=…` mit 301), Blätter-Links auf der aktuellen Archiv-URL.
- Startseite: Termine aus `cms-events` mit gültigem `LIMIT` (bisher als String gebunden) und ohne Exception bei ungültigem Datum.
- Fußzeile, 404-Seite und Header-CTA verlinken nur vorhandene Ziele; leere Footer-Spalten entfallen. 404-CTA „Kontakt“ auf `/contact`, zusätzlich „Suche öffnen“. `ptc_href('#anker')` verlor das `#` und führte auf `/<anker>`.
- Logo-Fallback `site_logo`, Favicon `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die PTC-Palette über `member_head` (`--member-*`); in der Member-Shell werden weder Theme-Stylesheet noch Scripts oder eigener Kopf-/Fußcode geladen.
- Login: `login_page.colors` in `theme.json`; Login-Showcase im Legacy-Modus (`CmsAuthPageService::renderShowcase()`) unter der Karte.
- Hero-, Seiten- und Beitragsbilder mit Fokus-Punkt (`object-position`) und `width`/`height`.
- Vorschau-Links (`$isPreview`) und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr (auch nicht bei ausgeschalteter lokaler Schrifteinbindung); Schriften aus der Core-Schriftverwaltung, sonst System-Stack. Die Customizer-Schriftwahl wirkt jetzt tatsächlich (`--font-body`/`--font-heading` waren im Stylesheet nicht verdrahtet und wurden HTML-escaped in den `<style>`-Block geschrieben).
- Core-SEO-Ausgabe (`theme_v4_head_tags()`) statt eigener `description`, `og:url` und `robots` (bisher fest `index,follow`, auch in der Vorschau); Seitentitel aus Modulen (`$title`) statt immer Site-Titel – `header.php` überschrieb `$title`.
- `cms_csp_runtime_tags(true)` als erstes Script nach `<meta charset>`; Theme-Script per `defer`.
- Customizer-Auswahlwerte (Schriftgewichte, Button-Transform) nur aus festen Listen ins CSS; Customizer-URLs (Hero, Karten, CTAs, Termine, Social, Network-Bar) werden vor der Ausgabe validiert; Copyright-Vorlage escaped.
- LCP-Bilder (Hero, Beitrags- und Seitenbild) mit `fetchpriority="high"` statt `loading="lazy"`; übrige Bilder lazy mit `width`/`height`.
- `after_header` wird nicht mehr doppelt ausgelöst (Core und `header.php`).

#### Barrierefreiheit
- Kontraste (Standardpalette): Akzent-Buttons 2,4:1 → 5,2:1 (weiße Schrift), Primär-Button-Hover 1,1:1 → 12,2:1, Muted-Farbe 2,6:1 → 5,5:1 (`#5b6b78`), Sekundärfarbe 4,4:1 → 5,4:1 (`#546e7a`), Footer-Unterzeile/Rechtliches 3,6:1 → 7,8:1, Network-Bar-Links 3,0:1 → 8,7:1, Hero-Overline 3,2:1 → 6,0:1, Blog-Hero-Meta 3,6:1 → 6,0:1, Untermenü-Links im Mobil-Drawer 1,0:1 → 7,6:1, Hover auf dunklem Footer 2,8:1 → 8,4:1.
- Fokusrahmen auf dunklen Flächen (Footer, CTA, Seiten-/Blog-Hero) in Mint `#8fd3b8` (5,4–8,4:1 statt 1,8–2,8:1).
- Untermenüs öffnen auch per Tastatur (`:focus-within`), Escape schließt sie; Escape außerhalb des Menüs verschob bisher den Fokus auf den Menüschalter. Geschlossener Mobil-Drawer ist `inert` (Links waren unsichtbar per Tab erreichbar); beim Öffnen erhält der erste Link den Fokus. Profil-Dropdown als Disclosure (`aria-controls`, schließt bei Fokusverlust und Escape) statt unvollständigem `role="menu"`.
- Überschriften ohne Sprünge: Footer `h4` → `h2`, Termin-Karten `h4` → `h3`, Blog-Sidebar `h3` → `h2`; Login und Registrierung haben eine `h1`.
- `html lang` nach Inhaltssprache, Skip-Link-Ziel `#main-content` auf allen Seiten, Dekor-Emojis `aria-hidden`, Hinweis „öffnet in neuem Fenster“ bei externen Links.

## 3.1.0 – 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- Kein doppelter Seitenrahmen mehr: Templates binden `header.php`/`footer.php` nicht mehr selbst ein – der Core rendert beide in `ThemeManager::render()`. `header.php`/`footer.php` sind zusätzlich gegen Doppel-Einbindung geschützt.
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Theme-Customizer: Aufruf der entfernten Legacy-Funktionen `renderAdminSidebar()`/`renderAdminSidebarStyles()` beseitigt (Fatal Error im Theme Editor); der Customizer bettet sich jetzt in das Admin-Layout ein.
- Theme-Customizer: Inline-Handler (`onclick`/`onchange`/`oninput`) und Inline-`<script>`/`<style>` nach `js/customizer-admin.js` und `css/customizer-admin.css` ausgelagert; Bildvorschauen per DOM-API statt `innerHTML`.
- Theme-Customizer (Sicherheit): SVG-Uploads entfernt (Stored-XSS), MIME-Prüfung per `finfo`, `is_uploaded_file()`, 2-MB-Limit und zufällige Dateinamen; Bild-URLs nur noch als http(s) oder wurzelrelativer Pfad; CSRF-Prüfung kompatibel mit der Section-Shell des Theme Editors.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 3.0.0 Nachtrag (2026-05-19)

### Abschlussdokumentation der Generisierung

- `PTC-Corporate.color-theme.json` ist bewusst entfernt; das Theme nutzt nur noch neutrale Customizer-/CSS-Tokens statt firmenspezifischer CI-Artefakte.
- `admin/customizer.php`, Templates, `js/navigation.js` und `style.css` sind als generisches Branchen-Theme dokumentiert: Personalvermittlung, Arbeitnehmerüberlassung und Weiterbildung ohne Betreiber-Festverdrahtung.
- `README.md`, `theme.json`, `update.json`, `style.css` und `functions.php` bilden den finalen v3/PHP-8.4-Stand `3.0.0` ab.

## 3.0.0 (2026-05-18)

### Generalisierung (Breaking für alte CI)

- Entfernt: firmenspezifische Namen, Slogans, Adressen, Logos und Navy/Gold-CI-Bezüge
- Neutrale Platzhaltertexte für Personalvermittlung & Weiterbildung
- CSS-Variablen: `--color-primary`, `--color-accent`, `--color-*` statt `--ptc-navy` / `--ptc-gold`
- Gelöscht: `PTC-Corporate.color-theme.json`
- Network-Bar-Defaults leer (optional)

### v3 / PHP 8.4

- `PTC_THEME_VERSION` 3.0.0, sync mit `theme.json`, `style.css`, `update.json`
- Hooks: `head`, `before_footer`, `init`, `cms_init`
- Styles: preload + versioniert; `navigation.js` defer
- `mapFontChoice()`, Google Fonts mit preconnect
- `outputCustomStyles()` für alle Customizer-Farben/Layout-Tokens
- `filter_var`, `htmlspecialchars`, `sanitizeCustomCss`
- `theme_nav_menu`, `get_header`, `get_footer`, `ptc_get_setting`, `ptc_href`, `ptc_body_class`, `ptc_safe_headline`
- Events-Query: prepared `LIMIT`
- `--focus-ring`, `:focus-visible`, `prefers-reduced-motion`
- Mobile-Menü: Fokus-Wiederherstellung nach Schließen

### Design

- Pipeline-Sektion (Vermittlungsprozess)
- Dual-CTA-Karten (Kandidaten / Arbeitgeber)
- Keine generischen SaaS-Gradienten; vertrauensorientierte Farbpalette

## 1.1.0 (2026-03-01)

- Services 12 Karten, MS Booking, Network Bar, CTA-/Seitenrand-Farben

## 1.0.0 (2026-02-21)

- Erstveröffentlichung
