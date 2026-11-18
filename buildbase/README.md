# BuildBase Theme

**Version:** 4.1.0
**Target:** 365CMS 4.0.0 – 4.2.0, PHP 8.4.1+
**Identität:** Bau & Handwerk, Projektabwicklung, Handwerksbetriebe.

## Fokus

Verzeichnis- und Portfolio-Theme für Handwerksbetriebe, Baufirmen und Architekten.
Robuster, eckiger Look mit erdiger Amber-Braun Palette, Sicherheits-Amber Akzentlinie
und Blueprint-Rasterung im Hero. Keine Trend-Glassmorphism, keine generischen
blau-violetten Verläufe – jede Designentscheidung ist auf das Gewerk abgestimmt.

## Architektur

- Theme-Klasse `BuildBase_Theme` mit `\CMS\Hooks::addAction(...)`-Bindings.
- Menüpositionen über den Core-Filter `register_menu_locations`; die Hauptnavigation löst den Core-Hook `main_nav` aus.
- Konstante `BUILDBASE_THEME_VERSION` synchron zu `theme.json`, `style.css`-Header und `update.json`.
- `enqueueStyles()` lädt `style.css` und `css/cms-v4.css` (Cache-Busting `?v=`); im Memberbereich nur die Palette über `member_head`.
- Navigation-JS wird mit `defer` und Cache-Busting geladen.
- Keine externen Schriften: lokale Schriften aus der Core-Schriftverwaltung, sonst System-Stack.
- Alle Customizer-Werte werden String-gecastet und mit `htmlspecialchars()` ausgegeben; Booleans via `filter_var(..., FILTER_VALIDATE_BOOLEAN)`.

## Designprinzipien

- **Farben:** Amber-Braun (`#b45309`) primär, Tiefes Braun (`#92400e`) sekundär,
  Gold-Akzent (`#d97706`), Sicherheits-Amber (`#f59e0b`) als Hi-Vis-Signal.
  Steingrau-Neutrale für Rahmen, Cards und Hintergründe.
- **Typografie:** Roboto (Body) für klare Lesbarkeit auf Baustellen-Devices,
  Roboto Condensed (Headlines) für signaletische Dichte, Oswald (Badges/Gewerke)
  für plakative Beschilderung.
- **Form:** Eckige 4 px Radien (`--btn-radius`, `--radius-sm`), 2 px Border-Akzente,
  3 px Topborder auf Cards als Referenz an Eck-Stempel auf Bauplänen.
- **Hero:** Dunkler Beton-Verlauf + dezentes Blueprint-Raster (CSS-only), Hazard-Stripe
  (45° Amber/Schwarz) als Bodenkante – direkter visueller Bezug auf Baustellen-Markierungen.
- **Buttons:** Solider 2-px-Bottom-Shadow erzeugt Gewicht statt floating Card-Look.

## Modernisierungs-Highlights (3.0.1)

- Vollständige Customizer-Variablen-Verkabelung: Header-/Footer-Farben, Hero-Gradient,
  Button-Radius/-Padding/-Transform, Container-Breite, Section-Spacing, Typografie-Tokens.
- Hero-Kennzahlen (Stats) jetzt Customizer-getrieben – keine fake Marketing-Zahlen mehr.
- `style.css` mit vollem Theme-Header inkl. `Requires PHP: 8.4`.
- Glassmorphism-Badge im Hero ersetzt durch solides Hi-Vis-Tag.
- Inline-`style=""` aus `index.php`, `page.php`, `404.php`, `error.php` komplett entfernt
  und durch `.bb-page-section`, `.bb-page-title`, `.bb-error-screen`, `.bb-error-card`
  Utility-Klassen ersetzt.
- Inline-`<script>` für Emergency-Banner-Body-Klasse entfernt – Klasse wird jetzt
  serverseitig in `header.php` gesetzt.
- `footer_show_crafts` Customizer-Key korrekt verkabelt (vorher: `show_trade_links`).
- Tel-Link sanitiert auf `0-9+`.
- Google-Fonts-URL nutzt jetzt alle in `theme.json` angebotenen Schriftarten.
- `custom_css` aus Customizer wird gegen `</style>`-Injection gefiltert.
- Reduced-Motion Guard für Karten-Entry-Animation und Smooth-Scrolling.

## Dateien

```
buildbase/
├── 404.php
├── CHANGELOG.md
├── contact.php                  (Core-Kontaktformular, 4.0.0)
├── css/cms-v4.css               (gemeinsame 4.x-Integrationsstile)
├── error.php
├── footer.php
├── functions.php
├── header.php
├── home.php
├── includes/cms-v4-compat.php   (gemeinsame 4.x-Helfer theme_v4_*)
├── index.php
├── js/navigation.js
├── knowledgebase/{archive,single}.php
├── newsletter/{page,action,issue}.php
├── page.php
├── README.md
├── blog-single.php
├── search.php
├── style.css
├── theme.json
└── update.json
```
