# LogiLink Theme

Version: 2.1.0
Target: 365CMS 4.0.0 – 4.2.0, PHP 8.4.1+
Constant: `LOGILINK_THEME_VERSION`

LogiLink ist ein Logistik- & Transport-Theme für 365CMS. Es ist gebaut für
operative Klarheit: Tracking-Status, KPI-Dashboards, Dispatcher-Sichten,
Last-Mile-Zustellinformationen.

## Designprofil

- **Anker**: "Hangar Steel" – Nacht-Blau (`#0c1a2e`) als operativer Anker statt
  generisches SaaS-Sky-Blue. Header, Footer und KPI-Karten sind in diesem
  dunklen Industrieton, alle Body-Sektionen auf einem kühlen Paperboard
  (`#f3f5f8`).
- **Akzent**: Signal-Amber (`#f59e0b`) wird **sparsam** eingesetzt –
  Hauptsächlich für Status-Picked, aktive CTA-Buttons und den
  Hero-Akzentstreifen. Amber bleibt damit semantisch wirksam und nicht
  dekorativ.
- **Typografie**: Inter (Body, default), Roboto Condensed (Headlines,
  operative Dichte), JetBrains Mono (Tracking-IDs, KPI-Zahlen, Status-Pills,
  ETA-Stempel). Die Mono-Schrift signalisiert "maschinell erzeugte Referenz".
  Schriften kommen aus der Core-Schriftverwaltung (lokal), sonst greift der
  System-Font-Stack – das Theme lädt keine Google Fonts.
- **Hero**: Editorial-Zweispalter mit Copy links und Routen-Linien-Visual
  rechts (Origin → Hub → Destination, Stoppunkte status-gefärbt). Kein
  zentriertes Headline-mit-Gradient-Klischee.
- **Status-System**: Sechs semantische Stufen, WCAG-AA-tauglicher Kontrast
  zwischen Badge-Hintergrund und Vordergrund:
    - `--status-warehouse` `#475569` auf `#e2e8f0`
    - `--status-picked`    `#b45309` auf `#fef3c7`
    - `--status-transit`   `#1d4ed8` auf `#dbeafe`
    - `--status-delivered` `#15803d` auf `#dcfce7`
    - `--status-delayed`   `#b91c1c` auf `#fee2e2`
    - `--status-returned`  `#6d28d9` auf `#ede9fe`

## Customizer

Vollständig per Customizer angebunden – jeder Schlüssel aus `theme.json`
wird tatsächlich als CSS-Variable ausgegeben oder im Template gerendert:

- `colors.*` – Brand, Status-System
- `typography.*` – Body/Heading/Daten-Schrift, Schriftgrösse, Zeilenhöhe,
  Überschriftengewicht. Jede Auswahl wird auf einen Font-Stack mit
  System-Fallback abgebildet (keine externen Schrift-Requests).
- `layout.*` – Container, Padding, Radius, Section-Spacing, KPI-Spaltenanzahl,
  Sticky-Header Opt-out, Dashboard-Modus.
- `header.*` – Logo, Header-Farben, Höhe, Logo-Max-Höhe,
  Tracking-Quick-Search Toggle + Platzhaltertext.
- `footer.*` – Farben, Beschreibung, Copyright (`{year}` / `{site_title}`
  Template-Platzhalter).
- `buttons.*` – Radius, Padding, Schriftgewicht, Text-Transform.
- `logistics_hero.*` – Hero-Badge, Headline, Subline, CTA-Labels.
- `logistics_tracking.*` – Section-Titel, Status-Labels, No-Result-Text
  (als `data-no-result`-Attribut am Tracking-Quick-Search-Formular für
  spätere Client-Logik bereitgestellt).
- `logistics_content.*` – Services, Capacity-Booking, KPI-Labels,
  Network/CTA-Sektionen.
- `advanced.custom_css` – wird defensiv gegen `</style>`-Injection gefiltert.
- `contact.*` – E-Mail, Telefon, Adresse und Erreichbarkeit neben dem
  Core-Kontaktformular (`contact.php`).

Farb- und Zahlenwerte werden vor der Ausgabe validiert (`ll_css_color()`,
`ll_css_number()`); ungültige Werte fallen auf den Standard zurück.

## Asset-Pipeline

- `enqueueStyles()` auf `head:1` – `style.css` und `css/cms-v4.css`
  (je `?v=LOGILINK_THEME_VERSION`).
- `outputMetaTags()` auf `head:10` – nur `theme-color`. Beschreibung, Robots,
  Canonical, Open Graph und JSON-LD kommen aus der Core-SEO
  (`theme_v4_head_tags()` in `header.php`).
- `outputCustomStyles()` auf `head:15` – CSS-Variablen aus Customizer +
  Custom-CSS.
- `enqueueScripts()` auf `before_footer:99` – `<script ... defer>` für
  `js/navigation.js?v=LOGILINK_THEME_VERSION`.
- Im Core-Mitgliederbereich (`/member`) lädt das Theme nichts davon, sondern
  setzt über `member_head` nur die Palette als `--member-*`-Variablen.

## Helper-Funktionen

Stellt das vollständige Theme-Helper-Set bereit:

- `theme_is_logged_in()`, `theme_route_url($name)`,
  `theme_nav_menu($location, $mainNavContext = '')` (mit Kontext `desktop`/`mobile`
  folgen die Core-Einträge des Hooks `main_nav`)
- `get_header()`, `get_footer()`
- `ll_get_setting($section, $key, $default)`, `ll_get_flash()`,
  `ll_set_flash($type, $message)`
- `ll_href($target)`, `ll_site_url()`, `ll_site_title()`, `ll_body_class()`,
  `ll_safe_headline($raw)`

`theme_route_url` kennt: `home`, `tracking`, `login`, `register`, `member`,
`partners`, `routes`, `search`, sonst SITE_URL + Pfad.

## Sicherheit & Accessibility

- Keine externen Requests (keine Google Fonts, kein CDN); Asset-URLs sind
  `htmlspecialchars`-escaped.
- `cms_csp_runtime_tags(true)` als erstes Script im `<head>`, Theme-Script mit `defer`.
- Boolean-Customizer-Werte werden über `filter_var(..., FILTER_VALIDATE_BOOLEAN)`
  gelesen.
- `custom_css` wird vor Output gegen `</style>`-Injection regex-gefiltert.
- Skip-Link auf `#main` als erstes fokussierbares Element.
- `:focus:not(:focus-visible) { outline: none }` + sichtbarer
  `:focus-visible`-Ring mit Token `--focus-ring`.
- Mobile-Drawer und Search-Panel mit `aria-expanded`/`aria-controls`; der
  geschlossene Drawer ist per `visibility: hidden` nicht fokussierbar.
  Fokus in den Drawer beim Öffnen, Focus-Restore an Opener, Escape- und
  Outside-Click-Schluss.
- Fokusrahmen Stahlblau auf hellen, Amber auf dunklen Flächen (jeweils ≥ 3:1).
- Body-Scroll-Lock bei offenem Drawer.
- Reduced-Motion-Aware: keine Reveal-Transitions wenn
  `prefers-reduced-motion: reduce`, JS fällt auf sofortige Sichtbarkeit zurück.
- Dekorative SVGs/Emoji erhalten `focusable="false"` + `aria-hidden="true"`.
- Mobile- und Search-Toggle deklarieren `type="button"`, `aria-expanded`,
  `aria-controls`.

## Templates

- `header.php` – Site-Header, primäre Nav, Tracking-Quick-Search, Mobile-Toggle,
  Search-Toggle, Mobile-Drawer, Search-Panel. Keine hardcoded `<link>`-Tags –
  Stylesheet kommt aus `enqueueStyles()`.
- `home.php` – Routen-Linien-Hero, Status-Stepper, Demo-Shipment-Cards,
  KPI-Dashboard, Service-Übersicht, Capacity-Booking-Skizze,
  Partner-Netzwerk, Registrierungs-CTA. Alle Inhalte aus dem Customizer,
  null Inline-Styles.
- `index.php` / `page.php` – generische Beitrags-/Seiten-Ansicht, nutzt
  `ll-card` und `ll-prose-wrap`.
- `404.php` – Fehlerseite mit Startseite, Suche und (falls vorhanden)
  Sendungsverfolgung.
- `error.php` – Fehlerseiten-Vertrag 4.0.0 (`error_code`/`errorCode`,
  `error_standalone`, `error_retry_after`), eigenständig als vollständiges
  Dokument mit Theme-Stylesheet.
- `contact.php` – Core-Kontaktformular (`$contactFormHtml`) mit Kontaktdaten.
- `knowledgebase/*.php`, `newsletter/*.php` – Core-Views im Theme-Rahmen.
- `footer.php` – Footer mit Service-Links, Konto-Links, Footer-Nav-Menü und
  Footer-Legal-Menü, copyright via `gmdate('Y')` und Customizer-Template.
  Standardlinks erscheinen nur, wenn Route oder Seite existiert.
- `includes/cms-v4-compat.php`, `css/cms-v4.css` – gemeinsame 365CMS-4.x-Schicht
  aller Public Themes (Fehlerseiten, `main_nav`, Standardlinks, SEO/Vorschau,
  Memberbereich, Token-Brücke `--tv4-*` zu den Core-Modulen).

## Versionsabgleich

- `theme.json` → `"version": "2.0.0"`, `requires_cms` 4.0.0, `requires_php` 8.4.1,
  `tested_up_to` 4.2.0
- `style.css` → `Version: 2.0.0`, `Requires PHP: 8.4.1`, `Requires CMS: 4.0.0`,
  `Tested up to: 4.2.0`, `License: GPL-2.0-or-later`, `Text Domain: logilink`
- `update.json` → `"version": "2.0.0"`
- `functions.php` → `define('LOGILINK_THEME_VERSION', '2.0.0')`
