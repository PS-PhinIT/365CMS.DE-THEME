# Changelog

## 4.1.0 - 2026-11-18

### Audit 365CMS 4.2.0 (Plan und Befunde: `DOC/audit/v4.2.0/business.md`)

#### Sicherheit
- `biz_safe_url()` lehnt `/\host` ab: Ein Menüeintrag `/\evil.example` wurde als lokaler Pfad ausgegeben, Browser lesen ihn als `//evil.example` (fremde Adresse). Pfade mit Steuerzeichen werden verworfen statt bereinigt.
- `blog.php`: Paginierungslinks bauen auf dem normalisierten Anfragepfad statt auf `REQUEST_URI` auf.

#### Wartung
- Entfernt: `biz_sanitize_content_html()` (seit 4.0.0 ungenutzt; der Filter zerstörte Core-HTML wie `<picture>`, Formulare und SVG).

#### 365CMS 4.2.0
- Gemeinsame Schicht **Kit 1.1.0** (`includes/cms-v4-compat.php`, `css/cms-v4.css`): Modulprüfung, Community-Helfer (nur bei aktivem Modul und vorhandenen Core-Vorlagen), `theme_v4_user_can()` (Rolle `moderator` ohne Admin-Zugang), `theme_v4_slug()`, Token-Brücke `--community-*`; Härtungen bei Backslash-Adressen und Unterverzeichnis-Erkennung. Die Community-Vorlagen des Cores erscheinen ohne verschachteltes `<main>` (Header öffnet `<main id="main-content">`).
- Standard-Footermenü: Eintrag „Community“, sobald das Modul aktiv ist.
- WCAG 2.2 (2.4.11): `scroll-padding-top` hält Fokus und Sprungziele (`#leistungen`, `#kontakt`) unter dem Sticky-Header sichtbar.
- `tested_up_to` 4.2.0; `requires_cms` bleibt 4.0.0.

## 4.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus (bisher nur `$GLOBALS['error_code']`). 403-, 429- und 503-Seiten antworten mit ihrem Status; eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet und genau einem `<main>` (ohne Header, Hooks, Datenbank, Customizer); im normalen Fall kein zweites `<main>`, weil `header.php` es öffnet. Der Fatal-Handler lädt nur noch `includes/cms-v4-compat.php`.
- Desktop-Navigation und Mobil-Drawer lösen den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Kontexte `desktop` und `mobile`), auch bei leerem Menü.
- Menüeinträge zeigen die Beschriftung aus `title` (der Core liefert `title`, das Theme las nur `label`); aktive Einträge tragen `aria-current="page"`. Anker-Einträge (`/#…`) gelten nicht mehr gleichzeitig als aktiv.
- `ThemeManager::registerMenuLocation()` (entfernt) wird nicht mehr aufgerufen; Menüpositionen nur über `register_menu_locations`. Die Standardmenüs wurden bisher bei jedem Request (`cms_init`) in die Datenbank geschrieben; sie gelten jetzt nur zur Laufzeit, solange kein Menü gepflegt ist, und verlinken nur vorhandene Seiten und Routen (z. B. Impressum, Datenschutz, AGB).
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den OptionStore statt per SQL; `getConfig()` liest `theme.json` relativ zum Theme-Ordner statt über `THEME_PATH . 'business/'`.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Kontaktdaten aus dem neuen Customizer-Bereich „Kontakt“ aus (E-Mail-Fallback `ADMIN_EMAIL`).
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` binden die Core-Views im Theme-Rahmen ein (kein zweites `<main>`, Skip-Link-Ziel `#main-content`).
- Neu: `css/cms-v4.css` und `includes/cms-v4-compat.php` (gemeinsam mit allen Public Themes) sowie die Token-Brücke `--tv4-*` in `style.css`.
- `page.php` gibt das gerenderte Core-HTML unverändert aus; `biz_sanitize_content_html()` entfernte bisher `<picture>`, `<form>`, `<button>`, `<input>`, `<svg>` und `style`-Attribute (Editor.js-Bilder mit WebP/AVIF, Formular-, Download- und Feed-Blöcke). `page.php` läuft auch für Downloads und Feeds (`id` 0) in voller Inhaltsbreite; eine fehlende Seite bindet `404.php` ein, statt den kompletten Seitenrahmen erneut zu rendern.
- Kontakt: Header-, Drawer- und 404-Buttons führen auf `/contact`; die Startseiten-CTA nutzt die Customizer-Kontaktdaten statt der Platzhalter `mailto:info@example.com` und `tel:+491234567890`. Die Social-Icons im Footer verlinkten fest auf `#` und sind entfernt.
- Fußzeile: Standardlinks nur zu vorhandenen Zielen, leere Spalten entfallen, neue Links Newsletter und Downloads (nur wenn aktiv).
- Blog: Blätter-Buttons nutzten `btn-biz-outline` (weiße Schrift) auf weißem Grund und waren unsichtbar.
- Logo-Fallback `site_logo` (mit Bildmaßen), Favicon `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Business-Palette über `member_head` (`--member-*`, Primär dunkles Kupfer); Theme-Stylesheet und Scripts werden in der Member-Shell nicht mehr geladen.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite.
- Beitragsbild mit Fokus-Punkt (`object-position`).
- Vorschau-Links (`$isPreview`) und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr (Inter, Source Sans 3, Source Serif 4); Schriften aus der Core-Schriftverwaltung, sonst System-Stack. Die Font-Stacks wurden HTML-escaped (`&quot;`) in den `<style>`-Block geschrieben und waren damit ungültig.
- Core-SEO-Ausgabe (`theme_v4_head_tags()`) statt eigener `description`, `og:url` und `robots` (bisher fest `index,follow`, auch in der Vorschau); Seitentitel aus Modulen (`$title`) statt immer Site-Titel – `header.php` überschrieb `$title`.
- `cms_csp_runtime_tags(true)` als erstes Script nach `<meta charset>`; Theme-Script per `defer`.
- Beitragsbild als LCP-Bild mit `width`/`height` und `fetchpriority="high"` statt `loading="lazy"`; Über-uns-Bild lazy mit Maßen.

#### Barrierefreiheit
- Kontraste (Standardpalette): Links und Eyebrows auf hellen Flächen im dunklen Kupfer `#8a5f17` (helles Kupfer `#c08a2e`: 3,0:1 auf Weiß, 2,8:1 auf Papier → 5,6:1 / 5,2:1); Primär-Button-Hover weiße Schrift auf `#a16f1e` 4,4:1 → auf `#8a5f17` 5,6:1 (`primary_hover`-Standard angepasst); Rahmen des Outline-Buttons 2,9:1 → 5,3:1.
- Fokusrahmen: auf hellen Flächen dunkles Kupfer (5,2–5,6:1 statt 2,8–3,0:1), auf Header, Hero, dunklen Sektionen, CTA und Footer helles Kupfer (5,1–6,1:1).
- Fußzeilen-Überschriften `h2` statt `h4` (kein Sprung unter `h1`); Seiten ohne Titel und „Beitrag nicht gefunden“ mit `h1`.
- Geschlossener Mobil-Drawer ist `inert` (Links waren unsichtbar per Tab erreichbar), beim Öffnen erhält der erste Link den Fokus; Escape schließt nur ein offenes Menü und gibt den Fokus an den Schalter zurück (bisher bei jedem Escape ausgelöst). Anker-Sprünge übergeben den Fokus an das Ziel.
- `html lang` nach Inhaltssprache, Skip-Link-Ziel `#main-content` auf allen Seiten, Dekor-Pfeile `aria-hidden`.

## 3.1.0 – 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- `header.php`/`footer.php` sind gegen Doppel-Einbindung geschützt (der Core rendert beide in `ThemeManager::render()`).
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 3.0.2 – 2026-05-19 Nachtrag

- Abschlussdokumentation für den B2B-Hardening-Pass: `biz_safe_url()`, `biz_href()`, `biz_nav_menu()`, `biz_sanitize_content_html()`, Mobile-Body-Scroll-Lock und die strict_types/ABSPATH-Template-Guards sind als gemeinsamer Release-Stand nachgetragen.
- README, `theme.json`, `update.json`, `style.css`, `functions.php` und `CHANGELOG.md` bleiben auf `3.0.2` synchron; der Update-Feed trägt das Nachtragsdatum vom 19.05.2026.

## 3.0.2 – 2026-05-18

### Security-/Robustheits-Pass (365CMS v3.x.x / PHP 8.4)

- Customizer-CSS-Werte werden vor der Ausgabe als CSS-Variablen über Farb- und Zahlen-Helper validiert bzw. auf die `theme.json`-Grenzen begrenzt.
- `advanced.custom_css` entfernt jetzt zusätzlich zu `</style>` auch `<style>`-Tags, `@import`, `expression()`, `javascript:`-URLs sowie Legacy-Sinks wie `behavior`/`binding`.
- `biz_safe_url()` schützt interne, externe, Anchor-, `mailto:`- und `tel:`-URLs fail-closed; protocol-relative URLs werden verworfen.
- `biz_href()` und `biz_nav_menu()` verwenden die gehärtete URL-Normalisierung, inklusive sicherer Fallbacks für Menüeinträge.
- CMS-Seitencontent läuft über `biz_sanitize_content_html()` und entfernt Event-Handler-/Style-Attribute sowie gefährliche `href`-/`src`-Protokolle in erlaubtem HTML.
- Header-Logo und Über-uns-Bild validieren ihre Customizer-URLs vor der Ausgabe.
- Alle PHP-Templates besitzen jetzt `declare(strict_types=1)` plus `ABSPATH`-Guard.
- Das Mobile-Menü sperrt Scroll über `body.mobile-menu-open` statt per direktem `document.body.style.overflow`; Anchor-Scrolling nutzt sichere ID-Lookups statt ungeprüfter CSS-Selector-Strings.
- `BUSINESS_THEME_VERSION`, `style.css`, `theme.json`, `update.json` und README auf `3.0.2` synchronisiert; Manifest-Anforderungen deklarieren CMS `3.0.0` und PHP `8.4`.

## 3.0.1 – 2026-05-17

### v3 / PHP 8.4 / Hardening

- `BUSINESS_THEME_VERSION`-Konstante eingeführt; synchron mit `theme.json`,
  `style.css`-Header und `update.json`.
- `style.css`-Header trägt `Requires PHP: 8.4`, `License: GPL-2.0-or-later`
  und `Text Domain: biz-theme`.
- Menü-Registrierung läuft jetzt auf BEIDEN Hooks `init` und `cms_init` für
  v3-Bootstrap-Kompatibilität.
- `getThemeUrl('business')` mit defensivem Fallback auf `getThemeUrl()` –
  konsistent mit den anderen v3-Themes.
- `theme.json` um vollständiges `customization`-Schema erweitert
  (`colors`, `typography`, `layout`, `header`, `footer`, `biz_hero`,
  `biz_content`, `advanced`). Der bisherige `settings`-Block bleibt als
  Default-Fallback für `biz_config()` erhalten.
- `IT_Business_Theme::outputCustomStyles()` schreibt Customizer-getriebene
  CSS-Custom-Properties (Farben, Typografie, Layout, Focus-Ring) und filtert
  `custom_css` defensiv gegen `</style>`-Injection.
- `enable_sticky_header` wird über `filter_var(..., FILTER_VALIDATE_BOOLEAN)`
  gelesen.
- Google-Fonts-Output mit `preconnect` zu googleapis.com und gstatic.com
  (crossorigin), wird nur ausgegeben, wenn eine nicht-System-Schrift
  gewählt ist – inklusive aller via Customizer wählbaren Familien.
- `biz_href()` unterstützt zusätzlich `mailto:` und `tel:` (vorher als
  relativer Pfad behandelt).
- `biz_nav_menu()` cast Item-Label sicher zu String und behandelt fehlende
  URLs als `#`.
- `biz_body_class()`-Helper rendert `has-sticky-header` serverseitig in das
  `<body class="…">` statt per JS-Mutation.

### Accessibility

- Neuer `--focus-ring`-CSS-Token (an Customizer-Primary gebunden).
- `:focus:not(:focus-visible) { outline: none; }` +
  sichtbarer `*:focus-visible`-Ring.
- `.biz-focus-shadow:focus-visible` als Button-/Link-Focus-Shadow-Utility.
- `prefers-reduced-motion: reduce` schaltet `scroll-behavior`,
  globale Animationen, Drawer-Transitions und Karten-Animationen ab.
- Dekorative SVG-Icons (Header- und Footer-Logo) erhalten zusätzlich
  `focusable="false"`.
- Service-Icons und Emojis in Listen mit `aria-hidden="true"` markiert.
- Footer-Brand-Link mit `biz-focus-shadow` versehen, damit der Logo-Link
  fokussierbar und sichtbar ist.

### Template-Cleanups

- Sämtliche `style="…"`-Inline-Attribute aus `page.php`, `404.php`,
  `error.php` und `index.php` entfernt und durch dedizierte CSS-Klassen
  (`biz-page-eyebrow`, `biz-prose`, `biz-prose-empty`, `biz-error-wrap`,
  `biz-error-wrap-narrow`, `biz-error-icon`, `biz-error-title`,
  `biz-error-text`, `biz-error-actions`) ersetzt.
- Footer-Service- und -Kontaktliste verwenden jetzt `biz_href()` +
  `htmlspecialchars()` für alle internen Links.
- Hero-CTA-Mail/Telefon-Links laufen über `biz_href()` (das auch
  `mailto:`/`tel:` schützt).
- `home.php` liest `about_image` aus Customizer-Gruppe `biz_content`
  (statt der nicht-deklarierten Gruppe `business`).
- Footer-Copyright unterstützt jetzt das Template
  `© {year} {site_title}. Alle Rechte vorbehalten.` aus dem Customizer
  mit `gmdate('Y')` und HTML-Escaping.

### Design – Editorial B2B-Identität

- Komplettes Repalette: Generischer Indigo `#6366f1` → deliberater
  Kupfer-Gold `#c08a2e`; Navy `#0f172a` → tieferes Slate-Ink `#0c1320`;
  weißer Section-Hintergrund → warmes Papier `#f7f5f0`.
- Hero: generische Purple-Radial-Glow-Orbs entfernt; ersetzt durch
  vertikale Gold-Rule + Hairline-Diagonal-Pattern und ein redaktionelles
  Eyebrow mit Rule-Line.
- CTA-Sektion: Indigo-Linear-Gradient entfernt; jetzt solide Slate-Fläche
  mit Gold-Top-Rule und Eyebrow.
- Service-Cards: Generischer 3px-Top-Gradient-Strich und translate-Y-Hover
  entfernt; ersetzt durch Gold-Left-Rule + Paper-Hover.
- Buttons: tighteres Padding, Akzent jetzt Gold-on-Ink, kein Bounce,
  Focus-Shadow-Doppelring.
- Stats: linksbündig, Hairline-Trenner zwischen Spalten, tabular-nums.
- Eyebrow-Komponente (`biz-eyebrow`) ersetzt überall die generische
  Tag-Pill (`biz-section-tag`).
- Highlight im Hero-H1: subtile Italic-Variante mit Underline-Rule statt
  reiner Indigo-Farbe.
- Mobile-Drawer: Gold-Left-Rule auf aktivem Item statt voller
  Indigo-Hintergrund.

## 3.0.0 – 2026-05-17

- Optimiertes Asset-Loading mit Preload/Defer und escaped Cache-Busting
  Query-Parametern.
- URL- und Safe-Headline-Helper hinzugefügt (`biz_href`, `biz_safe_headline`).
- Interne Links und Pfadkonkatenation in Templates vereinheitlicht.
- Inline-Style-Attribute durch CSS-Klassen ersetzt.
- Navigation und Reduced-Motion-Handling für bessere Accessibility.
