# Changelog

## 2.0.0 - 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `requires_php`, `tested_up_to: 4.1.0`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus. 403-, 429- und 503-Seiten antworten mit ihrem Status statt mit HTTP 500; eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet (ohne Header, Hooks und Datenbank), bei 429/503 mit `Retry-After`. Bisher rief `error.php` selbst `get_header()`/`get_footer()` auf und lud dafür `functions.php` nach.
- Desktop-Navigation und Mobil-Drawer lösen den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Kontext `desktop` bzw. `mobile`), auch bei leerem Menü.
- Menüeinträge zeigen die Beschriftung aus `title` (der Core liefert `title`, das Theme las nur `label`), der aktive Eintrag trägt `aria-current="page"`; Menü-URLs mit fremdem Schema werden verworfen.
- `registerNavMenus()` mit dem nicht existierenden `ThemeManager::registerMenuLocation()` entfernt; Menüpositionen nur über den Filter `register_menu_locations`.
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den `OptionStore` statt per SQL.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Kontaktdaten aus dem neuen Customizer-Bereich „Kontakt“ aus.
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` binden die Core-Views ein und setzen sie in den Theme-Rahmen (Skip-Link-Ziel `#main`, kein doppeltes `<main>`).
- Neu: `css/cms-v4.css` (gemeinsam mit allen Public Themes) und die Token-Brücke `--tv4-*` in `style.css` für Downloads, Feeds, Formulare, Hub-Sites, Medien-Credits, Editor.js-Blöcke, Inhaltsverzeichnis und wiederverwendbare Blöcke.
- Neu: `includes/cms-v4-compat.php` mit den Helfern `theme_v4_*`.
- Fußzeile, 404-Seite, Hero und Header zeigen die Theme-Ziele `/tracking`, `/partners`, `/routes` (und Downloads, Wissensdatenbank, Newsletter, Kontakt) nur, wenn die Seite bzw. Route existiert; leere Spalten und eine leere „Navigation“-Spalte entfallen. Logo-Fallback `site_logo`, Favicon `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Theme-Palette über `member_head` (`--member-*`); im Admin geänderte Member-Farben haben Vorrang. Stylesheet, Custom-Vars und Navigation-Script werden in der Member-Shell nicht mehr geladen.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite.
- Beitragsbild mit Fokus-Punkt (`object-position`), `width`/`height` und `fetchpriority="high"` als LCP-Bild statt `loading="lazy"`.
- Vorschau-Links und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr (auch ohne „Schriften lokal einbinden“); Schriften kommen aus der Core-Schriftverwaltung, sonst System-Font-Stack (`system-ui` ergänzt).
- Core-SEO-Ausgabe (`theme_v4_head_tags()`) statt der festen Tags aus `outputMetaTags()` (`robots index,follow` auch für Vorschauen, `og:url` der Startseite auf jeder Seite); Dokumenttitel aus Seitentitel/Core-SEO statt immer Site-Titel.
- `cms_csp_runtime_tags(true)` als erstes Script direkt nach `<meta charset>`; Navigation weiter per `defer`.
- Customizer-Farben und -Zahlen werden vor der Ausgabe im `<style>`-Block validiert (bisher nur HTML-escaped, `;}` hätte den Block verlassen können); `button_transform` nur aus den erlaubten Werten. Copyright-Text wird escaped (Entities bleiben erhalten).
- Generische Suchformular-Stile auf `.ll-search-form` begrenzt (sie verschoben bisher auch die Header-Schnellsuche).

#### Barrierefreiheit
- Customizer-Standardfarben in `theme.json` auf die Palette aus `style.css` gezogen: Primär `#0284c7` (Weiß darauf 4,1:1) → `#0e6ba8` (5,7:1), Hintergrund `#f0f9ff` → `#f3f5f8`, Muted `#64748b` (4,5:1 knapp darunter) → `#5b6b80` (5,0:1), Status-Badge-Texte (bisher 1,9–4,0:1) → 4,5–6,2:1.
- Fokusrahmen: Stahlblau auf hellen Flächen (Amber 2,0:1 → 7,2:1), Amber auf Header, Hero, KPI-Karten, CTA und Footer (8,1:1); Formularfelder mit vollem Fokusrahmen statt 22-%-Schatten.
- Eyebrow-Texte `#92400e` (bisher 3,0–3,2:1 → 6,0–6,5:1), Hover der Amber-Buttons mit dunkler Schrift (Weiß 3,5:1 → 5,0:1), KPI-Trends auf Dunkel (3,5:1/2,7:1 → 10,0:1/6,3:1), 404-/Fehlercode 3,5:1 (große Schrift).
- „Alle Beiträge“-Button und „Sendung verfolgen“ im Drawer waren unsichtbar (weiße bzw. dunkle Schrift auf gleichfarbigem Grund).
- Geschlossener Mobil-Drawer ist per `visibility: hidden` nicht mehr per Tab erreichbar (vorher nur `aria-hidden` mit fokussierbaren Links), Fokus wandert beim Öffnen in den Drawer.
- Fußzeilen-Überschriften `h2` statt `h3`, Fußzeilen-Spalten als benannte `nav`, `html lang` nach Inhaltssprache, redundante `role`-Attribute entfernt.
- `supports` ohne `dark-mode`: Das Theme hat keinen Dark Mode.

## 1.1.0 - 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- Kein doppelter Seitenrahmen mehr: Templates binden `header.php`/`footer.php` nicht mehr selbst ein – der Core rendert beide in `ThemeManager::render()`. `header.php`/`footer.php` sind zusätzlich gegen Doppel-Einbindung geschützt.
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 1.0.2 - 2026-05-19 Nachtrag

### Abschlussdokumentation v3/PHP 8.4

- `functions.php`, alle Basis-Templates, `style.css`, `js/navigation.js`, `theme.json`, `update.json` und README sind als finaler Logistik-Hardening-Stand dokumentiert.
- Nachgetragen sind die operativen Detailänderungen an Mobile-Drawer, Search-Panel, Focus-Restore, Tracking-Quick-Search, Status-System, Customizer-Mapping und Asset-Pipeline.
- Der Update-Feed trägt das Nachtragsdatum vom 19.05.2026, ohne die Version `1.0.2` erneut zu erhöhen.

## 1.0.2 - 2026-05-18

### v3 / PHP 8.4 / Hardening
- `LOGILINK_THEME_VERSION` als Theme-Konstante eingeführt; `theme.json`,
  `style.css`, `update.json` und `functions.php` synchronisiert.
- `style.css`-Header um `Requires PHP: 8.4`, `License`, `Text Domain` ergänzt.
- `enqueueStyles()` (`head:1`) und `enqueueScripts()` (`before_footer:99`)
  ersetzen das hartcodierte `<link rel="stylesheet">` und das alte
  `outputNavigationScript()` – beides versioniert via `?v=<theme-version>`,
  Stylesheet zusätzlich als `<link rel="preload" as="style">`.
- `outputMetaTags()` schreibt Description, OG-URL, Theme-Color und Robots.
- `outputCustomStyles()` mappt jetzt jeden Customizer-Schlüssel aus
  `theme.json`: Farben, Status-System, Typografie (size/line-height/weight),
  Layout (container/padding/radius/section-spacing/kpi_columns),
  Header (bg/text/height/logo-height), Footer (bg/text/link), Buttons
  (radius/padding/font-weight/transform). Booleans (sticky header, dashboard
  mode) gehen durch `filter_var(..., FILTER_VALIDATE_BOOLEAN)`.
- `mapFontChoice()` erweitert auf alle in `theme.json` deklarierten
  Familien – `Inter`, `Roboto`, `Open Sans`, `Lato`, `Roboto Condensed`,
  `Montserrat`, `Barlow Condensed`, `JetBrains Mono`, `Roboto Mono`,
  `Fira Code`, plus System-Monospace; Fallback-Ketten je `body`/`heading`/`mono`-Slot.
- Google-Fonts-URL lädt nur tatsächlich gewählte Familien (System/Mono-only
  Auswahl überspringt das `<link>`-Tag komplett), `preconnect`-Hints sind
  HTML-escaped.
- `custom_css` wird über `sanitizeCustomCss()` gegen `</style>`-Injection
  defensiv gefiltert.
- `cms_init` + `init` Doppelregistrierung der Menü-Locations bleibt erhalten;
  zusätzlich `register_menu_locations`-Filter.

### Helper-Set
- Theme-lokales Helper-Set hinzugefügt, parallel zum `medcarepro`-Vorbild:
  `theme_is_logged_in`, `theme_route_url`, `theme_nav_menu`, `get_header`,
  `get_footer`, `ll_get_setting`, `ll_get_flash`, `ll_set_flash`, `ll_href`,
  `ll_site_url`, `ll_site_title`, `ll_body_class`, `ll_safe_headline`.
- `ll_body_class()` rendert Sticky-Header- und Dashboard-Mode-Klassen
  serverseitig (kein JS-Class-Mutation).

### Templates
- `header.php`: hartcodierter Stylesheet entfernt; Logo mit
  `focusable="false"`/`aria-hidden="true"` SVG-Mark, `ll-focus-shadow`
  Fokus-Utility; Tracking-Quick-Search trägt `data-no-result` aus
  `logistics_tracking.tracking_no_result_text`; neuer Mobile-Drawer und
  Search-Panel mit `aria-controls`/`aria-expanded`/`aria-hidden`.
- `footer.php`: nutzt `gmdate('Y')`-Template `{year}` / `{site_title}`,
  alle URLs durch `theme_route_url()` aufgelöst, `theme_nav_menu('footer-nav')`
  und `theme_nav_menu('footer-legal')` eingebunden, keine Inline-Styles mehr.
- `home.php`: vollständig neu strukturiert. Hero mit Route-Linien-Visual
  (Origin/Hub/Destination, Status-Stoppunkte), Status-Stepper über alle
  sechs Stufen, Demo-Shipment-Cards mit links-anchored Status-Indikator,
  KPI-Cards mit Monospace-Zahlen und Delta-Indikatoren, Service-Grid mit
  SVG-Icons, Capacity-Booking-Skizze, Partner-Row, Registrierungs-CTA.
  Jede in `theme.json/logistics_content` deklarierte Customizer-Variable
  wird tatsächlich gerendert (`services_section_title`,
  `capacity_booking_title`, `capacity_units_label`,
  `kpi_punctuality_label`, `kpi_efficiency_label`, `kpi_damage_label`,
  `network_section_title`, `cta_section_title`, `cta_section_text`).
- `index.php`, `page.php`, `404.php`, `error.php`: alle Inline-`style=""`-
  Attribute entfernt, durch CSS-Klassen ersetzt; URLs durchgehend
  `htmlspecialchars`-escaped; `theme_route_url()` für Home/Tracking-Links.

### CSS
- Komplette Neuschreibung von `style.css` mit `--ll-*`-Token-System.
  Status-System mit WCAG-AA-tauglichen Fg/Bg-Paaren: `warehouse #475569 /
  #e2e8f0`, `picked #b45309 / #fef3c7`, `transit #1d4ed8 / #dbeafe`,
  `delivered #15803d / #dcfce7`, `delayed #b91c1c / #fee2e2`,
  `returned #6d28d9 / #ede9fe`.
- Eyebrow-Kicker in Monospace-Caps (Route-Nummern, Sektions-Labels) statt
  generischer Pills.
- Tracking-ID-Klasse `.ll-tracking-id` als operatives Code-Element.
- Shipment-Card-Pattern mit links-anchored Status-Stripe.
- KPI-Cards mit Monospace tnum/lnum + Delta-Pfeilen.
- Sicht-Reveal über `.ll-reveal` + `.is-visible`.
- Fokus-Token `--focus-ring`, Mouse/Keyboard-Fokus getrennt
  (`:focus:not(:focus-visible)` neutralisiert + sichtbarer
  `:focus-visible`-Ring + `.ll-focus-shadow`-Utility).
- Responsive Brüche bei 480 / 768 / 1024 Pixeln; Mobile-Drawer ersetzt
  das alte Inline-`.open`-Menü.

### JS
- `js/navigation.js` neu geschrieben nach dem `cms-newspaper`-Pattern:
  Sticky-State, Mobile-Drawer mit `aria-hidden`/`aria-expanded`/
  Outside-Click/Escape/Focus-Restore/Scroll-Lock, separates Search-Panel
  mit Focus-Restore zum Opener, reveal nur bei
  `!prefers-reduced-motion && 'IntersectionObserver' in window`, sonst
  unmittelbares `is-visible`-Fallback.

### Dokumentation
- `README.md`: stark erweitert, beschreibt vollständiges Designprofil,
  Customizer-Mapping, Helper-Set, Asset-Pipeline und Sicherheits-/A11y-
  Praktiken.
- `update.json`: auf `1.0.2`, neuer Changelog-Eintrag, `min_cms_version`
  auf `3.0.0` aktualisiert.

## 1.0.1 - 2026-05-17

- Added `cms_init` menu registration fallback for 365CMS v3 bootstrap flows.
- Fixed multiple Customizer key mismatches between `theme.json` and runtime
  mapping.
- Added font choice mapping for base, heading, and data fonts.
- Corrected status color mapping to `colors.status_*` tokens.
- Hardened URL/button handling in header and home templates.
- Replaced inline layout styles with dedicated CSS classes.
- Added keyboard-visible focus styling and focus ring token.
- Added reduced-motion guard for reveal animations.
- Added theme documentation (`README.md` and this changelog).

## 1.0.0 - 2026-02-21

- Erstveröffentlichung: Logistik & Netzwerk-Theme.
