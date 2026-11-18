# Business Presentation Theme

**Version:** 4.1.0
**Ziel:** 365CMS 4.0.0 – 4.2.0 · PHP 8.4.1+
**Slug:** `business`
**Text-Domain:** `biz-theme`

Editoriales B2B-/Corporate-Landingpage-Theme – Slate-Anker, deliberater
Kupfer-Gold-Akzent, redaktionelle Whitespace-Disziplin. Bewusst _kein_
generisches Indigo-/Purple-Template-Layout.

## Sektionen

`Hero → Leistungen → Über uns → Zahlen & Fakten → CTA`

Sticky-Header, Mobile-Drawer, dedizierte Page-/404-/Error-Templates.

## Bootstrap & Hooks

- `\CMS\Hooks::addAction('head', ...)` für Styles (`style.css`, `css/cms-v4.css`) / Meta / Custom-CSS; im Memberbereich nur die Palette über `member_head`. Keine externen Schriften.
- `\CMS\Hooks::addAction('before_footer', ...)` für `js/navigation.js` (deferred).
- Menüpositionen über den Core-Filter `register_menu_locations`; Standardeinträge nur zur Laufzeit und nur zu vorhandenen Zielen; Desktop- und Mobilnavigation lösen den Core-Hook `main_nav` aus.
- Konstante `BUSINESS_THEME_VERSION` synchron mit `theme.json`, `style.css`
  und `update.json`.

## Customizer

Vollständige Customizer-Schema-Deklaration in `theme.json` (`customization`):

| Gruppe        | Inhalt                                              |
| ------------- | --------------------------------------------------- |
| `colors`      | Akzent, Ink, Paper, Text, Border, Focus-Ring        |
| `typography`  | Body-/Heading-Schriftart, Größe, Zeilenhöhe         |
| `layout`      | Container, Sektionsabstand, Eckenradius             |
| `header`      | Logo, Sticky-Header-Toggle                          |
| `footer`      | Tagline, Copyright-Template                         |
| `biz_hero`    | Hero-Eyebrow, Headline (highlight-Span erlaubt), Lead, CTAs |
| `biz_content` | Über-uns-Heading & -Bild, CTA-Heading & -Text       |
| `advanced`    | `custom_css` (defensiv gegen CSS-/Style-Sinks)      |

## Sicherheit & Robustheit

- Alle URLs HTML-escaped auf Output; keine externen Requests (keine Google Fonts, keine CDNs).
- `enqueueStyles()` liefert `style.css` und `css/cms-v4.css` mit versionierter URL.
- `enqueueScripts()` setzt `defer`.
- `biz_href()` normalisiert relative/absolute/Anchor-/`mailto:`/`tel:`-URLs fail-closed über `biz_safe_url()`.
- `biz_safe_headline()` erlaubt nur `<span class="highlight">…</span>`,
  alles andere wird escaped.
- `biz_nav_menu()` validiert URLs mit Fallback auf sichere relative Pfade und Anchors.
- Seiten- und Beitragsinhalt kommt bereits bereinigt vom Core und wird unverändert ausgegeben; `biz_sanitize_content_html()` bleibt nur als Helfer für eigene Inhalte erhalten.
- `custom_css` wird gegen `<style>`-/`@import`-/`expression()`-/`javascript:`-/Legacy-CSS-Sinks gefiltert.
- Boolean-Customizer-Werte (z. B. `enable_sticky_header`) werden über
  `filter_var(..., FILTER_VALIDATE_BOOLEAN)` gelesen.
- Schriften: Customizer-Wahl (Inter, Source Sans 3, Source Serif 4) wird nur als Font-Stack gesetzt;
  die Dateien liefert die Core-Schriftverwaltung, sonst greift der System-Stack.

## Accessibility

- `:focus:not(:focus-visible) { outline: none; }` + sichtbare `:focus-visible`-
  Ring mit `--focus-ring`-Token.
- `prefers-reduced-motion: reduce` schaltet Smooth-Scroll, Hover-Animationen
  und Drawer-Transitions defensiv ab.
- Mobile-Toggle besitzt `aria-controls`, `aria-expanded` und `aria-label`.
- Drawer toggelt `aria-hidden`.
- Dekorative SVG-Icons haben `aria-hidden="true"` _und_ `focusable="false"`.
- Skip-Link zu `#main-content`.

## Design-Identität

- **Anker:** Deep-Slate `#0c1320` (Header, Hero, Footer).
- **Akzent:** Kupfer-Gold `#c08a2e` – signalisiert Premium-Beratung,
  bewusst kein generisches Indigo.
- **Sektions-Surface:** warmes Off-White `#f7f5f0` für ruhigen Editorial-Look.
- **Editorial Eyebrow** mit horizontaler Rule-Line statt generischer
  Tag-Pill-Badges.
- **Service-Cards** flat, mit Gold-Left-Rule auf Hover – kein
  translate-Y-Bounce, kein generischer Top-Gradient-Strich.
- **CTA-Sektion** als solide Slate-Fläche mit Gold-Top-Rule, _kein_
  Indigo-/Violett-Gradient.
- **Stats** als linksbündige, getrennte Säulen mit Hairline-Rules –
  redaktionell, kein Center-Aligned-Boxen-Look.

## Dateistruktur

```
business/
├── 404.php
├── CHANGELOG.md
├── contact.php            (Core-Kontaktformular, 4.0.0)
├── css/
│   └── cms-v4.css         (gemeinsame 4.x-Integrationsstile)
├── error.php
├── footer.php
├── functions.php
├── header.php
├── home.php
├── includes/
│   └── cms-v4-compat.php  (gemeinsame 4.x-Helfer theme_v4_*)
├── index.php
├── js/
│   └── navigation.js
├── knowledgebase/         (archive.php, single.php – Core-Views im Theme-Rahmen)
├── newsletter/            (page.php, action.php, issue.php)
├── page.php
├── README.md
├── style.css
├── theme.json
└── update.json
```
