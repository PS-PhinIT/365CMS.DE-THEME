# PersonalFlow Theme

Version: 2.0.0
Target: 365CMS 4.0.0 – 4.1.0, PHP 8.4.1+
Constant: `PERSONALFLOW_THEME_VERSION`

PersonalFlow ist ein HR- und Personalvermittlungs-Theme für 365CMS. Es ist
für Personalagenturen, Recruiting-Plattformen und Karriere-Portale gebaut
und macht eine Sache explizit: den Status eines Menschen in einer
Recruiting-Pipeline.

## Designprofil

- **Anker**: warmes Amber (`#b45309`) statt des branchenüblichen
  SaaS-Indigo / -Violett. Der Amber-Ton liest sich menschlich und
  zugewandt – passend für eine Plattform, auf der Karriere-Entscheidungen
  getroffen werden – und ist mit Weiß WCAG-AA-tauglich (4.9:1).
- **Akzent**: Teal `#0f766e` als komplementärer „Growth"-Akzent –
  reserviert für aktive Pipeline-Punkte und den Hero-Live-Indikator.
- **Hero**: zweispaltige redaktionelle Komposition mit Copy + CTAs links,
  einer Hero-Aside (Talent-Card-Stack) rechts und einem 5-stufigen
  Pipeline-Ribbon direkt unter dem Hero – kein zentriertes
  Headline-CTA-Klischee.
- **Pipeline-Status-System**: sechs semantische Stufen mit eigenen
  Vordergrund-/Hintergrund-Paaren, einzeln gegen `#ffffff` AA-konform:

  | Stage | Foreground | Background |
  |---|---|---|
  | Beworben (`applied`) | `#475569` | `#e2e8f0` |
  | Screening (`screened`) | `#b45309` | `#fef3c7` |
  | Interview (`interview`) | `#1d4ed8` | `#dbeafe` |
  | Angebot (`offer`) | `#6d28d9` | `#ede9fe` |
  | Eingestellt (`hired`) | `#15803d` | `#dcfce7` |
  | Archiviert (`archived`) | `#57534e` | `#f5f5f4` |

- **Typografie**: Nunito Sans (Body, default), Nunito (Headlines –
  freundlich, rund), JetBrains Mono (KPI-Zahlen, Gehaltsangaben,
  Pipeline-Counts). Stats werden mit `font-feature-settings: "tnum","lnum"`
  in tabellarische Ziffern gerendert – Recruiter:innen können
  KPI-Spalten vergleichen. Schriften kommen aus der Core-Schriftverwaltung
  (lokal), sonst greift der System-Font-Stack – keine Google Fonts.
- **Person-Card-Pattern**: Avatar (Image oder Initialen-Plate), Name,
  Rolle, Standort, Stage-Badge, Skill-Chips, Action-Row (Profil ansehen
  / Kontakt aufnehmen). Wiederverwendbar über
  `partials/candidate-card.php`.

## Dateistruktur

```
personalflow/
├── functions.php           # Bootstrap, Asset-Pipeline, Customizer, Helper-Set
├── header.php              # DOCTYPE, <head>, sticky Header, Mobile-Drawer, Search-Panel
├── footer.php              # 4-spaltiger Footer, footer-nav, footer-legal
├── home.php                # Hero, Pipeline-Ribbon, KPI-Strip, Talent-Pool, Job-Board, CTA
├── index.php               # Beitrags-Fallback
├── page.php                # Statische CMS-Seiten (Prose)
├── 404.php                 # Nicht-gefunden-Seite (Startseite, Suche, /jobs falls vorhanden)
├── error.php               # Fehlerseiten-Vertrag 4.0.0, eigenständig als vollständiges Dokument
├── contact.php             # Core-Kontaktformular mit Kontaktdaten aus dem Customizer
├── knowledgebase/          # Core-Views (Archiv, Einzelartikel) im Theme-Rahmen
├── newsletter/             # Core-Views (Seite, Aktion, Ausgabe) im Theme-Rahmen
├── includes/
│   └── cms-v4-compat.php   # Gemeinsame 365CMS-4.x-Helfer aller Public Themes (theme_v4_*)
├── css/
│   └── cms-v4.css          # Gemeinsame Integrationsstile (Token-Brücke --tv4-*)
├── style.css               # WordPress-konformer Header, Tokens, Komponenten, Responsive
├── theme.json              # Manifest + Customizer-Schema
├── update.json             # Update-Feed
├── README.md
├── CHANGELOG.md
├── js/
│   └── navigation.js       # Mobile-Drawer, Search-Toggle, IntersectionObserver-Reveal
└── partials/
    └── candidate-card.php  # Wiederverwendbare Talent-Card
```

## Konstanten & Hooks

| Konstante | Wert |
|---|---|
| `PERSONALFLOW_THEME_VERSION` | `2.0.0` |
| `PERSONALFLOW_THEME_SLUG` | `personalflow` |

| Hook | Priorität | Aktion |
|---|---|---|
| `head` | 1  | `enqueueStyles()` – `style.css` und `css/cms-v4.css` mit `?v=`-Cache-Bust |
| `head` | 10 | `outputMetaTags()` – nur `theme-color` (SEO-Tags kommen aus dem Core) |
| `head` | 15 | `outputCustomStyles()` – Customizer → CSS-Variablen + custom_css |
| `before_footer` | 99 | `enqueueScripts()` – `navigation.js` defer + Cache-Bust |
| `member_head` | 20 | `outputMemberTokens()` – nur im Mitgliederbereich, statt aller obigen Hooks |
| `register_menu_locations` (Filter) | – | Menü-Locations für ThemeManager |

## Helper-Funktionen (`functions.php`)

| Funktion | Rückgabe | Zweck |
|---|---|---|
| `theme_is_logged_in()` | `bool` | CMS-Auth-Status |
| `theme_route_url(string $name)` | `string` | Theme-lokaler Routen-Resolver |
| `theme_nav_menu(string $location, string $mainNavContext = '')` | `void` | Rendert ein `<ul>`-Navigationsmenü mit `aria-current`; mit Kontext `desktop`/`mobile` folgen die Core-Einträge des Hooks `main_nav` |
| `get_header()` / `get_footer()` | `void` | Template-Inkludes über `ThemeManager` |
| `pf_get_setting($sec, $key, $def)` | `mixed` | Sicherer Customizer-Lese-Shortcut |
| `pf_get_flash()` / `pf_set_flash()` | – | Session-basierte Flash-Messages |
| `pf_href(string $target)` | `string` | Normalisiert relative/absolute Links auf SITE_URL |
| `pf_site_url()` / `pf_site_title()` | `string` | Convenience-Wrapper |
| `pf_body_class(...$extra)` | `string` | Server-rendered Body-Klasse (`pf-body` + Modifier) |
| `pf_safe_headline(string $raw)` | `string` | HTML-escaped Headline mit erlaubtem `<span class="hl">` |
| `pf_stage_label(string $stage)` | `string` | Customizer-Label zu Pipeline-Stage-Slug |

`theme_route_url` kennt: `home`, `search`, `login`, `register`, `member`,
`jobs`, `candidates`, `employers`, `pipeline`, `pricing`, `imprint`,
`privacy`, `terms`, sonst SITE_URL + Pfad.

## Customizer-Sektionen

| Sektion | Inhalt | Wirkt auf |
|---|---|---|
| `colors` | Brand- und Akzentfarben, Pipeline-Stage-Paare, Flächen-/Text-/Border-Tokens, Status (success/warning/error) | CSS-Variablen `--pf-*`, alle Komponenten |
| `typography` | Body/Heading/Stats-Schriftart, Basis-Schriftgröße, Zeilenhöhe, Heading-Weight | `--pf-font-*` (Font-Stacks mit System-Fallback) |
| `layout` | Container-Breite, Padding, Eckenradius, Sektionsabstand, Sticky-Header-Toggle | `--pf-max-width`, `--pf-radius`, `--pf-section-pad` |
| `header` | Logo, Header-Farben, Höhe, Logo-Max, Shadow-Toggle, Such-Toggle + Platzhalter | Header + Search-Panel |
| `footer` | Hintergrund / Text / Link, Beschreibung, Pipeline-/Arbeitgeber-Spalten-Toggle, Copyright-Template | Footer |
| `buttons` | Radius, Padding, Schriftgewicht, Text-Transform | `.pf-btn` |
| `hr_hero` | Hero-Badge, Headline (`<span class="hl">` erlaubt), Subline, CTA-Labels/URLs (für Kandidaten + Arbeitgeber), Pipeline-Ribbon-Toggle, Aside-Titel | `home.php` |
| `pipeline` | Stage-Labels (Beworben → Screening → Interview → Angebot → Eingestellt → Archiv) + Beispiel-Counts für das Ribbon | `home.php`, `partials/candidate-card.php` |
| `hr_content` | Sektions-Titel und -Subtitles, Salary-/Remote-/Available-Labels, KPI-Texte (4 Kennzahlen mit Label + Wert), CTA-Texte | `home.php` |
| `advanced` | Custom CSS (gegen `</style>`-Injection gefiltert) | `outputCustomStyles()` |
| `contact` | E-Mail, Telefon, Adresse, Erreichbarkeit | `contact.php` |

Jeder in `theme.json` deklarierte Schlüssel wird tatsächlich als
CSS-Variable ausgegeben oder im Template gelesen – keine Dead-Keys.

## Asset-Pipeline

- `enqueueStyles()` (`head:1`) – `style.css` und `css/cms-v4.css`
  (`?v=PERSONALFLOW_THEME_VERSION`). URLs sind `htmlspecialchars`-escaped.
- Keine externen Requests: keine Google Fonts, kein CDN.
- `outputMetaTags()` (`head:10`) – nur `theme-color`. Beschreibung, Robots,
  Canonical, Open Graph und JSON-LD kommen aus der Core-SEO
  (`theme_v4_head_tags()` in `header.php`), in der Vorschau mit `noindex`.
- `outputCustomStyles()` (`head:15`) – CSS-Variablen aus Customizer
  (Farben/Zahlen validiert über `pf_css_color()`/`pf_css_number()`) +
  Custom-CSS (regex-gefiltert gegen `</style>`).
- Im Core-Mitgliederbereich (`/member`) lädt das Theme keine Assets, sondern
  setzt über `member_head` nur die Palette als `--member-*`-Variablen.
- `enqueueScripts()` (`before_footer:99`) – defer + Cache-Bust.

## Sicherheit & Accessibility

- `cms_csp_runtime_tags(true)` als erstes Script im `<head>`, Theme-Script mit `defer`.
- Boolean-Customizer-Werte gehen durch `filter_var(..., FILTER_VALIDATE_BOOLEAN)`.
- `custom_css` wird via `sanitizeCustomCss()` gegen `</style>`-Injection
  regex-gefiltert.
- Skip-Link auf `#main` als erstes fokussierbares Element.
- `:focus:not(:focus-visible) { outline: none }` + sichtbarer
  `:focus-visible`-Ring mit `--focus-ring`-Token (`#92400e`, 6,9:1), plus
  `.pf-focus-shadow`-Utility (voller Ring mit Abstand) für Buttons / Links /
  Inputs; weiß im Amber-CTA, hellgelb im Footer.
- Mobile-Drawer und Search-Panel mit `aria-expanded` / `aria-controls`;
  der geschlossene Drawer ist per `visibility: hidden` nicht fokussierbar.
  Fokus in den Drawer beim Öffnen, Focus-Restore an den Opener, Escape-
  und Outside-Click-Schluss.
- Body-Scroll-Lock bei offenem Drawer.
- Reduced-Motion-Aware: keine Reveal-Transitions und keine
  Hover-`translateY` wenn `prefers-reduced-motion: reduce`; JS fällt auf
  sofortige Sichtbarkeit zurück.
- Dekorative SVG-Icons mit `focusable="false"` + `aria-hidden="true"`.
- Mobile- und Search-Toggle deklarieren `type="button"`,
  `aria-expanded`, `aria-controls`.
- Touch-Targets ≥ 44 × 44 px (`.pf-btn`, `.pf-icon-btn`,
  Search-Input/Button).

## Pipeline-Stages – Anwendung

```php
<?php $candidate = [
    'name'        => 'Lena Brandt',
    'role'        => 'Senior Product Designer',
    'location'    => 'Berlin',
    'stage'       => 'interview',   // applied|screened|interview|offer|hired|archived
    'skills'      => ['Figma', 'Design Systems'],
    'available'   => true,
    'profile_url' => '/kandidaten/lena-brandt',
];
include THEME_PATH . 'personalflow/partials/candidate-card.php';
```

Stage-Badge ohne Karten-Wrapper:

```html
<span class="pf-stage-badge pf-stage-badge--interview">Interview</span>
```

## Templates

- `home.php` – Hero (Copy + Talent-Aside) → Pipeline-Ribbon → KPI-Strip
  → Talent-Pool-Grid → Job-Board-Grid → CTA. Alle Inhalte aus
  Customizer; Demo-Datensätze stehen inline und sind klar als
  Platzhalter markiert.
- `index.php` / `page.php` – generische Beiträge- bzw. Seitenansicht,
  Prose-Layout, keine Inline-Styles.
- `404.php` – Routet zur Startseite, zur Suche und (falls vorhanden) zu `/jobs`.
- `error.php` – Fehlerseiten-Vertrag 4.0.0 (`error_code`/`errorCode`,
  `error_standalone`, `error_retry_after`), Error-Code in Stats-Font.
- `contact.php` – Core-Kontaktformular (`$contactFormHtml`) mit Kontaktdaten.
- `knowledgebase/*.php`, `newsletter/*.php` – Core-Views im Theme-Rahmen.
- `partials/candidate-card.php` – Wiederverwendbare Talent-Card; Aktionen
  erscheinen nur, wenn die Profilseite existiert, Avatare nur aus lokalen Uploads.
- Standardlinks in Footer, Hero und 404 (`/jobs`, `/kandidaten`,
  `/arbeitgeber`, `/pipeline`, `/preise`) erscheinen nur, wenn Route oder
  Seite existiert.

## Versionsabgleich

- `theme.json` → `"version": "2.0.0"`, `requires_cms` 4.0.0, `requires_php` 8.4.1,
  `tested_up_to` 4.1.0
- `style.css` → `Version: 2.0.0`, `Requires PHP: 8.4.1`, `Requires CMS: 4.0.0`,
  `Tested up to: 4.1.0`, `License: GPL-2.0-or-later`, `Text Domain: personalflow`
- `update.json` → `"version": "2.0.0"`, `"min_cms_version": "4.0.0"`
- `functions.php` → `define('PERSONALFLOW_THEME_VERSION', '2.0.0')`
