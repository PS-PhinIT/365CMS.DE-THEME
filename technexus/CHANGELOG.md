# Changelog

## 2.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `requires_php`, `tested_up_to: 4.1.0`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus. 403-, 429- und 503-Seiten antworten mit ihrem Status statt mit HTTP 500; eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet (ohne Header, Hooks und Datenbank), bei 429/503 mit `Retry-After`. Bisher rief `error.php` selbst `get_header()`/`get_footer()` auf.
- Die Hauptnavigation löst den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Downloads, Knowledgebase, Newsletter), auch bei leerem Menü. Desktop und Mobil teilen sich eine Navigation, deshalb einmal mit dem Kontext `desktop`.
- Menüeinträge zeigen die Beschriftung aus `title` (der Core liefert `title`, das Theme las nur `label`), der aktive Eintrag trägt `aria-current="page"`; Menü-URLs mit fremdem Schema werden verworfen.
- `registerNavMenus()` mit dem nicht existierenden `ThemeManager::registerMenuLocation()` entfernt; Menüpositionen nur über den Filter `register_menu_locations`.
- Abmelden im Footer als POST-Formular mit CSRF-Token (`theme_v4_logout_form()`) statt GET-Link mit Token in der URL; `theme_logout_url()` ist veraltet.
- `home.php`: `LandingPageService` gibt es im Core 4.0.0 weiterhin; die Feature-Texte stehen im Feld `description` (das Theme las `text`, die Karten blieben ohne Text). Der Abschnitt hat jetzt immer eine `h2` (der Service liefert keinen `features_title`).
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den `OptionStore` statt per SQL.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Kontaktdaten aus dem neuen Customizer-Bereich „Kontakt“ aus.
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` binden die Core-Views ein und setzen sie in den Theme-Rahmen (Skip-Link-Ziel `#main`, kein doppeltes `<main>`).
- Neu: `css/cms-v4.css` (gemeinsam mit allen Public Themes) und die Token-Brücke `--tv4-*` in `style.css`, im Dark Mode mit eigener Palette.
- Neu: `includes/cms-v4-compat.php` mit den Helfern `theme_v4_*`.
- `page.php` gibt Inhalte auch ohne Titel aus (Downloads/Feeds rendert der Core mit `$page['id'] = 0`; bisher erschien „Diese Seite wurde nicht gefunden.“).
- Footer, Hero, Experten-Teaser und 404-Seite zeigen `/it-experts`, `/companies`, `/events`, `/jobs` (und Downloads, Wissensdatenbank, Newsletter, Kontakt) nur, wenn die Seite bzw. Route existiert. Logo-Fallback `site_logo`, Favicon `site_favicon`; Logo und Site-Titel verlinkten auf `…//`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Theme-Palette über `member_head` (`--member-*`, hell und dunkel); im Admin geänderte Member-Farben haben Vorrang. Stylesheet, Custom-Vars und Navigation-Script werden in der Member-Shell nicht mehr geladen.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite.
- Beitragsbild mit Fokus-Punkt (`object-position`), `width`/`height` und `fetchpriority="high"` als LCP-Bild statt `loading="lazy"`.
- Vorschau-Links und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr (auch ohne „Schriften lokal einbinden“); Schriften kommen aus der Core-Schriftverwaltung, sonst System-Font-Stack (`system-ui` ergänzt).
- Core-SEO-Ausgabe (`theme_v4_head_tags()`) statt der festen Tags aus `outputMetaTags()` (`robots index,follow` auch für Vorschauen, `og:url` der Startseite auf jeder Seite); Dokumenttitel aus Seitentitel/Core-SEO statt immer Site-Titel.
- `cms_csp_runtime_tags(true)` als erstes Script direkt nach `<meta charset>`; Navigation weiter per `defer`.
- Customizer-Farben und -Zahlen werden vor der Ausgabe im `<style>`-Block validiert (bisher nur HTML-escaped); `button_transform` und `card_columns` nur aus den erlaubten Werten. Copyright-Text wird escaped (Entities bleiben erhalten).
- `localStorage`-Zugriffe des Dark-Mode-Schalters abgesichert (privater Modus).

#### Dark Mode und Layout
- Dark Mode wirkte bisher kaum: Die Customizer-Variablen (`:root` in `#tn-custom-vars`) standen nach `style.css` und überschrieben `[data-theme="dark"]`. Jetzt `:root[data-theme="dark"]` mit `dark_bg_color`/`dark_surface_color` aus dem Customizer.
- Kartenraster: Die Customizer-Regel `.tech-grid{grid-template-columns:repeat(3…)}` überschrieb die Responsive-Regeln, und die 1024px-Regel stand nach der 768px-Regel – auf dem Smartphone blieben zwei bzw. drei Spalten. Spaltenzahl jetzt über `--tn-card-columns`.
- Generische Suchformular-Stile auf `.tn-search-form` begrenzt.

#### Barrierefreiheit
- Customizer-Standardfarben (`theme.json`, `style.css`, `functions.php` angeglichen): Primär `#0891b2` (Weiß darauf 3,7:1) → `#0e7490` (5,4:1), Link `#2563eb` (4,4:1 auf `--bg-secondary`) → `#1d4ed8` (5,7:1), Muted `#64748b` (4,0:1 auf `--bg-secondary`) → `#5c6b7a` (4,6:1), Code `#10b981` (2,2:1) → `#047857` (4,7:1); im Dark Mode Links/Badges `#22d3ee` (8,1:1), Code `#34d399` (7,6:1), Muted `#94a3b8` (5,7:1).
- Fokusrahmen: Petrol `#0e7490` auf hellen Flächen (Cyan 1,7:1 → 5,1:1), Cyan auf Header, Hero, CTA, Footer und im Dark Mode (9,9:1); Formularfelder mit vollem Ring statt 35-%-Schatten.
- Fehlercode, Tech-Badges und Kontakt-Badge nicht mehr Cyan auf Weiß (1,8:1); Hero-Statistik-Labels 6,6:1 statt 4,5:1 knapp darunter; „Alle Beiträge“-Button war unsichtbar (weiße Schrift auf weißer Karte).
- Dark-Mode-Schalter mit `aria-pressed` und fester Beschriftung; Mobil-Menü: Fokus beim Öffnen auf den ersten Link, Escape gibt den Fokus an den Schalter zurück.
- Fußzeilen-Überschriften `h2` statt `h3`, Fußzeilen-Spalten als benannte `nav`, `time datetime` im ISO-Format, „Weiterlesen“ mit Beitragstitel für Screenreader, `html lang` nach Inhaltssprache, redundante `role`-Attribute entfernt.

## 1.1.0 – 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- Kein doppelter Seitenrahmen mehr: Templates binden `header.php`/`footer.php` nicht mehr selbst ein – der Core rendert beide in `ThemeManager::render()`. `header.php`/`footer.php` sind zusätzlich gegen Doppel-Einbindung geschützt.
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 1.0.1 – 2026-05-19 Nachtrag

### Abschlussdokumentation v3/PHP 8.4

- `README.md`, `CHANGELOG.md`, `functions.php`, Templates, `style.css`, `theme.json`, `update.json` und `js/navigation.js` sind als finaler TechNexus-Hardening- und Design-Pass dokumentiert.
- Nachgetragen sind Helper-Set, `tn_body_class()`, Customizer-CSS-Variablen, Dark-Mode-/Search-/Mobile-Menü-Verhalten, Reduced-Motion-Fallbacks und die Charcoal/Cyan-Designidentität.
- Der Update-Feed trägt das Nachtragsdatum vom 19.05.2026, ohne die Version `1.0.1` erneut zu erhöhen.

## 1.0.1 – 2026-05-18

### 365CMS-v3-Härtung & Design-Pass

Teilweise vorhandenes TechNexus-Theme auf vollständige v3-Konventionen
gebracht (Referenz: `business`, `logilink`, `cms-newspaper`).

### functions.php

- Singleton `TechNexus_Theme` (final), Konstanten `TECHNEXUS_THEME_VERSION`
  / `TECHNEXUS_THEME_SLUG`.
- `enqueueStyles()` mit Preload; versionierte Assets statt `filemtime`.
- Dynamische Google Fonts + Preconnect; Skip bei System-Stacks.
- `outputCustomStyles()` mappt alle `theme.json`-Keys auf CSS-Variablen
  (`--tn-*` + Legacy-Aliase).
- Menü-Registrierung auf `init` + `cms_init`.
- Helper-Set: `tn_get_setting`, `tn_href`, `tn_body_class`, Flash, usw.

### Templates

- Alle `style=""` entfernt; semantische Utility-Klassen in `style.css`.
- Inline-`<script>` aus `footer.php` entfernt (Logik nur in `navigation.js`).
- `header.php`: `tn_body_class()`, `aria-controls`, SVG-Icons, kein hardcoded
  Stylesheet.
- `home.php`, `index.php`, `page.php`, `404.php`, `error.php` bereinigt.

### Design

- Palette: Charcoal (`#0c1220`) + Electric Cyan (`#0891b2` / `#22d3ee`), kein
  generisches Indigo-Gradient-UI.
- Hero: dezentes Netz-Mesh statt Partikel/Glassmorphism.
- Card-Hover-Standard: farbiger Rahmen (`border`), nicht Lift+Shadow.
- `prefers-reduced-motion` in CSS und JS.

### Dokumentation

- `README.md`, `DOC/technexus/README.md` ergänzt.

## 1.0.0 – 2026-02-21

Erstveröffentlichung mit `theme.json`, Basis-Templates und Tech-Styling.
