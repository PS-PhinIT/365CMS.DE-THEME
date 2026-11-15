# TechNexus Theme

**Version:** 2.0.0  
**Ziel:** 365CMS 4.0.0 – 4.1.0 · PHP 8.4.1+  
**Slug:** `technexus`  
**Text-Domain:** `technexus`

IT- & Tech-Hub-Theme für Dienstleister, Softwarehäuser und Expert-Netzwerke.
Charcoal-Oberflächen mit elektrischem Cyan-Akzent, dezentes Node-Grid im Hero,
JetBrains Mono nur für Code, Badges und Fehlercodes.

## Startseite

`Hero (Netz-Mesh + KPIs) → Landing-Features → Experten-Teaser → CTA`

Sticky Header mit optionaler Blur-Leiste, Dark-Mode-Toggle (`aria-pressed`,
`:root[data-theme="dark"]`), Suchpanel mit Focus-Restore, Mobile-Menü mit
`aria-expanded` / `aria-controls`. Die Landing-Features kommen aus dem
Core-`LandingPageService` (Felder `icon`, `title`, `description`); Experten-
und Hero-Links erscheinen nur, wenn die Ziel-Route (z. B. `/it-experts`) existiert.

## Bootstrap & Hooks

- `\CMS\Hooks::addAction('head', …)` – `style.css` + `css/cms-v4.css`, `theme-color`,
  Customizer-CSS (`#tn-custom-vars`). SEO-Tags (Beschreibung, Robots, Canonical,
  Open Graph, JSON-LD) kommen aus dem Core (`theme_v4_head_tags()` in `header.php`).
- `\CMS\Hooks::addAction('before_footer', …)` – `js/navigation.js` mit `defer`.
- Menüpositionen nur über den Filter `register_menu_locations`
  (`ThemeManager::registerMenuLocation()` gibt es im Core nicht).
- Im Core-Mitgliederbereich (`/member`) keine Theme-Assets, nur `member_head` →
  `--member-*`-Palette (hell und `prefers-color-scheme: dark`).
- Konstante `TECHNEXUS_THEME_VERSION` synchron mit `theme.json`, `style.css`,
  `update.json`.

## Customizer (`theme.json` → `customization`)

| Gruppe             | Inhalt                                              |
| ------------------ | --------------------------------------------------- |
| `colors`           | Primär/Sekundär, Cyan-Akzent, Code-Grün, Dark-Surfaces |
| `typography`       | Basis/Heading/Code, Größe, Zeilenhöhe, Gewicht     |
| `layout`           | Container, Padding, Grid-Hintergrund, Card-Spalten   |
| `header`           | Logo, Höhe, Blur, Status-Punkt                     |
| `footer`           | Texte, Tech-Links, Copyright-Template              |
| `buttons`          | Radius, Padding, Gewicht, Transform                 |
| `tech_hero`        | Badge, Headline, CTAs, Gradient, Stats              |
| `tech_expert_cards`| Tech-Stack, Skill-Meter, Hover-Effekt               |
| `tech_content`     | Sektionsüberschriften, Registrierungs-CTA          |
| `advanced`         | `custom_css`, Dark-Mode-Toggle, Standard-Dark       |
| `contact`          | E-Mail, Telefon, Adresse, Erreichbarkeit (`contact.php`) |

Menü-Positionen: `primary`, `footer-nav`, `footer-legal`, `tech-cats`.

## Helper

`theme_is_logged_in`, `theme_route_url`, `theme_nav_menu($location, $mainNavContext = '')`
(mit Kontext `desktop` folgen die Core-Einträge des Hooks `main_nav`), `get_header`,
`get_footer`, `tn_get_setting` / `technexus_get_setting`, `tn_get_flash`,
`tn_set_flash`, `tn_href`, `tn_site_title`, `tn_body_class`, `tn_safe_headline`,
`tn_html_attr`, `tn_css_color`, `tn_css_number`. `theme_logout_url()` ist veraltet
(Abmelden per `theme_v4_logout_form()`).

## Dateien (4.x)

- `contact.php` – Core-Kontaktformular (`$contactFormHtml`) mit Kontaktdaten.
- `knowledgebase/*.php`, `newsletter/*.php` – Core-Views im Theme-Rahmen.
- `includes/cms-v4-compat.php`, `css/cms-v4.css` – gemeinsame 365CMS-4.x-Schicht
  aller Public Themes (Fehlerseiten-Vertrag, `main_nav`, Standardlinks,
  SEO/Vorschau, Memberbereich, Token-Brücke `--tv4-*`).
- `error.php` – Fehlerseiten-Vertrag 4.0.0 (`error_code`/`errorCode`,
  `error_standalone`, `error_retry_after`), eigenständig als vollständiges Dokument.

## Sicherheit

- Asset-URLs HTML-escaped, Cache-Bust via `TECHNEXUS_THEME_VERSION`.
- `custom_css` filtert `</style>`-Injection; Customizer-Farben/-Zahlen validiert.
- Booleans via `filter_var(…, FILTER_VALIDATE_BOOLEAN)`.
- Keine externen Requests: keine Google Fonts (Schriften aus der Core-Schriftverwaltung,
  sonst System-Font-Stack), kein CDN.
- `cms_csp_runtime_tags(true)` als erstes Script im `<head>`.
- Abmelden als CSRF-geschütztes POST-Formular.

## Accessibility

- Skip-Link, sichtbarer `:focus-visible`-Ring (`--focus-ring`: Petrol `#0e7490` auf
  hellen, Cyan `#22d3ee` auf dunklen Flächen und im Dark Mode).
- Kontraste WCAG AA in Hell und Dunkel (Primär-Button 5,4:1, Muted 4,6:1 auf
  `--bg-secondary`, Links 5,7:1 bzw. 8,1:1 im Dark Mode).
- Touch-Targets min. 44px für Toggles und primäre Buttons.
- `prefers-reduced-motion` deaktiviert Scroll-Reveal und Hover-Transforms.
- Dekorative SVGs: `focusable="false"` `aria-hidden="true"`.
