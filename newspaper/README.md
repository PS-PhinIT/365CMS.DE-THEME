# CMS Newspaper Theme

**Version:** 2.0.0
**Ziel:** 365CMS 4.0.0 – 4.1.0 · PHP 8.4.1+
**Slug:** `cms-newspaper` (Marketplace), Theme-Ordner `newspaper`
**Text-Domain:** `cms-newspaper`

Editoriales News-/Magazin-Theme – Broadsheet-Typografie (Playfair Display
Masthead-Serif + Source Sans 3 Body + IBM Plex Mono Kicker/Meta), Ink-
Schwarz als Anker, sparsam eingesetztes Signal-Rot als Kategorie- und
Breaking-Akzent, Rule-Lines statt Card-Shadows. Bewusst _kein_ generischer
SaaS-Indigo, _keine_ Glassmorphism-Tendenz.

## Sektionen der Startseite

`Hero/Repo-Card → Breaking-Stack → Info-Box-Grid → Archive-Grid → Feed-Grid`

Sticky 3-Bar-Masthead (Utility-Bar + Wordmark/Navigation + Themen-Schnell­
navigation), Search-Toggle mit Focus-Restore, Mobile-Drawer mit
`hidden`, Fokus-Rückgabe und Escape-Close.

## Bootstrap & Hooks

- `\CMS\Hooks::addAction('head', …)` registriert `style.css`, `css/cms-v4.css`,
  `theme-color` und die Customizer-getriebenen CSS-Variablen. SEO-Tags
  (Beschreibung, Robots, Canonical, Open Graph, JSON-LD) kommen aus dem Core
  über `theme_v4_head_tags()` in `header.php`.
- `\CMS\Hooks::addAction('before_footer', …)` lädt `js/navigation.js` mit
  `defer`-Attribut und versionierter URL.
- Menü-Positionen nur über den Filter `register_menu_locations`
  (`ThemeManager::registerMenuLocation()` gibt es seit 365CMS 4.0 nicht mehr).
- Hauptnavigation und Mobil-Drawer lösen den Core-Hook `main_nav` aus
  (Kontext `desktop` bzw. `mobile`).
- Im Mitgliederbereich (`/member`) nur `member_head` mit der Theme-Palette.
- Konstante `CMSNEWSPAPER_THEME_VERSION` synchron mit `theme.json`,
  `style.css`-Header und `update.json`.
- `includes/cms-v4-compat.php`, `css/cms-v4.css` – gemeinsame 365CMS-4.x-Schicht
  aller Public Themes (Fehlerseiten-Vertrag, `main_nav`, Standardlinks,
  SEO/Vorschau, Memberbereich, Token-Brücke `--tv4-*`).
- `contact.php`, `knowledgebase/*.php`, `newsletter/*.php` – Core-Formular
  bzw. Core-Views im Theme-Rahmen (kein doppeltes `<main>`).

## Customizer-Schema (`theme.json` → `customization`)

| Gruppe          | Inhalt                                                     |
| --------------- | ---------------------------------------------------------- |
| `colors`        | Ink-Stufen, Papier/Surface, Akzent-Rot, Hairline-Rule, Live-Grün |
| `typography`    | Display/Body/Mono-Familie, Größe, Zeilenhöhe              |
| `layout`        | Container, Sektionsabstand, Eckenradius                   |
| `header`        | Logo, Sticky-Toggle, Utility-Bar/Themen-Bar/Search-Toggle, Statustext |
| `footer`        | Tagline, Copyright-Template, Partner-Strip-Toggle         |
| `buttons`       | Padding, Eckenradius, Text-Transform                      |
| `news_hero`     | Repo-Card-Kicker, Headline, Lead, CTA-Label/URL           |
| `news_content`  | Sektionsüberschriften, Info-Boxen, Feed-Headings, Live-Indikator |
| `advanced`      | `custom_css` (defensiv gefiltert gegen `</style>`-Injection) |
| `contact`       | Angezeigte E-Mail, Telefon, Adresse, Erreichbarkeit auf der Kontaktseite |

Neu in `header`: `social_linkedin_url`, `social_github_url`, `social_x_url`
(leer = kein Symbol in der Utility-Bar).

Menü-Positionen: `primary-nav`, `footer-nav`, `footer-legal`,
`topics-nav` (Themen-Schnellnavigation in der dritten Header-Leiste).

## Sicherheit & Robustheit

- Alle Asset-URLs (`style.css`, `css/cms-v4.css`, `js/navigation.js`) werden
  HTML-escaped ausgegeben und mit versionierter Cache-Bust-Query versehen.
  Keine externen Requests: keine Google Fonts, keine CDNs.
- `enqueueScripts()` setzt `defer`; `cms_csp_runtime_tags(true)` ist das erste Script im `<head>`.
- Customizer-Farben und -Zahlen werden vor der Ausgabe ins CSS validiert bzw. geklemmt.
- `news_href()` normalisiert relative/absolute/Anchor-/`mailto:`/`tel:`-URLs.
- `news_brand_html()` erlaubt einzig den inneren `<span>`-Highlight im
  Wordmark (z. B. `PHIN<span>IT</span>.DE`); alle anderen Tags werden
  escaped.
- `theme_nav_menu()` lässt über `news_safe_url()` nur relative Pfade, Anker,
  `http(s)`, `mailto:` und `tel:` zu.
- `custom_css` wird gegen `</style>`-Injection gefiltert.
- Alle Boolean-Customizer-Werte (`enable_sticky_header`, `show_utility_bar`,
  `show_topics_bar`, `show_search`, `show_partner_strip`,
  `show_breaking_label`) werden ausnahmslos über
  `filter_var(…, FILTER_VALIDATE_BOOLEAN)` gelesen.
- Schriften kommen ausschließlich aus der Core-Schriftverwaltung; jeder
  Font-Stack endet auf System-Schriften.

## Accessibility

- Skip-Link zu `#main-content`.
- `:focus:not(:focus-visible) { outline: none; }` + sichtbarer
  `*:focus-visible`-Ring mit `--focus-ring`-Token.
- `.news-focus-shadow` als Doppelring-Utility für Brand-Links, Suche,
  Toggles.
- `prefers-reduced-motion: reduce` schaltet Smooth-Scroll, Reveal-
  Animationen, Live-Dot-Pulse und Hover-Image-Scales ab.
- Header-Toggles (Suche, Mobile-Menü) tragen `type="button"`, `aria-label`,
  `aria-expanded` und `aria-controls`.
- Suche restauriert den ursprünglichen Fokus beim Schließen.
- Mobile-Drawer ist geschlossen `hidden` (nicht per Tab erreichbar), fokussiert
  beim Öffnen den ersten Link und gibt den Fokus beim Schließen zurück.
- Kontraste nach WCAG 2.1 AA (Signal-Rot `#c22510`, Meta-Grau `#6f7071`,
  aufgehellter Akzent `--news-accent-on-dark` auf Ink-Flächen).
- Dekorative SVGs nutzen `aria-hidden="true"` und `focusable="false"`.

## Design-Identität

- **Anker:** Ink-Schwarz `#0a0b0c` – Druckschwarz für Masthead, Headlines,
  Repo-Card und Footer.
- **Akzent:** Signal-Rot `#c22510` (bis 1.1.0 `#e32d12`) – ausschließlich für Kategorie-Badges,
  Breaking-Indikator, das Quadrat-Mark in Section-Headers und Card-Top-Rule
  der Hero-Repo-Karte. Keine generische SaaS-Indigo-Palette.
- **Surface:** Off-White `#f8f9fa` (Seite) + reines Weiß `#ffffff` (Karten).
  Erzeugt ein „Papier auf Druckmaschine“-Gefühl.
- **Editorial Trinity:**
  - **Playfair Display** als Display-Schrift – starke Masthead-Serif mit
    deutlichem Kontrast, geeignet für klassische Zeitungs-Headlines.
  - **Source Sans 3** als Body-Schrift – moderner humanistischer Sans, an
    digitalen Bildschirmen erprobt von Adobe Source und Mozilla.
  - **IBM Plex Mono** als Kicker-/Meta-Schrift – das technische
    Selbstverständnis eines IT-Portals wird sofort sichtbar.
- **Print-derivierte Hierarchie:** Kicker (uppercase mono mit Rule-Prefix)
  → Display-Headline → Lead → Meta-Line. Alle Sektionen werden durch eine
  2 px starke Ink-Bottom-Rule mit Akzent-Quadrat eingeleitet, nicht durch
  Card-Shadows.
- **Buttons:** Ink-Rechtecke mit 4 px Radius, Uppercase, Hover wechselt
  auf Signal-Rot. Keine Hochglanz-Pillen.
- **Breaking-/Live-Indikator:** kleiner pulsierender roter Punkt im
  Section-Header der Breaking-Sektion – sparsam, nicht über die ganze
  Seite gestreut, sofort abschaltbar per Customizer.
- **Responsive Walk:** 1440 → 1024 → 768 → 320 produziert
  4-Spalten-Footer / 3-Spalten-Grid → 3-Spalten-Footer / 2-Spalten-Grid →
  1-Spalten-Stack (Drawer aktiviert, Utility- und Topics-Bar versteckt) →
  Single-Column-Search-Form.

## Dateistruktur

```
newspaper/
├── 404.php
├── CHANGELOG.md
├── admin/customizer.php
├── blog.php, blog-single.php, search.php
├── contact.php
├── css/cms-v4.css, css/customizer-admin.css
├── error.php
├── footer.php
├── functions.php
├── header.php
├── home.php
├── images/placeholder.svg
├── includes/cms-v4-compat.php
├── index.php
├── js/
│   └── navigation.js, customizer-admin.js
├── knowledgebase/archive.php, single.php
├── newsletter/page.php, action.php, issue.php
├── page.php
├── README.md
├── style.css
├── theme.json
└── update.json
```

## Migrationshinweis

Der statische Prototyp `newspaper.html` ist seit 2.0.0 entfernt: Er lud
Google Fonts und Bilder von picsum.photos und war unter
`/themes/newspaper/newspaper.html` öffentlich abrufbar. Die produktiv
ausgelieferte Startseite ist `home.php` und liest alle relevanten Inhalte
aus dem Customizer.
