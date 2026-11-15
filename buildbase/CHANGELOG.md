# Changelog

## 4.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus. 403-, 429- und 503-Seiten antworten mit ihrem Status statt mit HTTP 500; eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet (ohne Header, Hooks und Datenbank), bei 429/503 mit `Retry-After`. `error.php` ruft `get_header()`/`get_footer()` nicht mehr selbst auf (doppelter Seitenrahmen, im Fatal-Handler mit Datenbankzugriff) und lädt im Fatal-Handler nur `includes/cms-v4-compat.php`.
- Die Hauptnavigation löst den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Downloads, Knowledgebase, Newsletter), auch ohne gepflegtes Menü. Die Navigation ist für Desktop und Mobil dieselbe, deshalb einmal mit dem Kontext `desktop`.
- Menüeinträge zeigen die Beschriftung aus `title` (der Core liefert `title`, das Theme las nur `label`); der aktive Eintrag trägt `aria-current="page"` und ist sichtbar markiert (bisher ohne Stil). Anker-, `mailto:`- und `tel:`-Einträge gelten nie als aktiv.
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den OptionStore statt per SQL.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Kontaktdaten aus dem neuen Customizer-Bereich „Kontakt“ aus (Telefon-Fallback: Notfall-Nummer, E-Mail-Fallback: `ADMIN_EMAIL`).
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` binden die Core-Views im Theme-Rahmen ein (Skip-Link-Ziel `#main`, genau ein `<main>`).
- Neu: `css/cms-v4.css` und `includes/cms-v4-compat.php` (gemeinsam mit allen Public Themes) sowie die Token-Brücke `--tv4-*` in `style.css`.
- Fußzeile, Startseite und 404-Seite verlinken nur noch vorhandene Seiten und Routen (z. B. `/handwerker`, `/angebot`, `/projekte`, `/pricing` nur, wenn veröffentlicht); leere Spalten und die Referenz-Sektion ohne Zielseite entfallen. Neue Footer-Spalte „Service“ (Kontakt, Newsletter, Wissensdatenbank), die registrierten Menüs `footer-nav` und `footer-legal` werden ausgegeben (bisher nie).
- Copyright: `&copy;` aus der Vorlage erschien als „&amp;copy;“.
- Blog-Archiv (`index.php`): Titel für Kategorie- und Schlagwort-Archive, Blätter-Links (bisher keine Paginierung), Beiträge auch als Array.
- `page.php` läuft auch für Downloads und Feeds (`id` 0) und gibt das Core-HTML unverändert aus; fehlende Seite mit `h1`.
- Logo-Fallback `site_logo` (mit echten Bildmaßen), Favicon `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die BuildBase-Palette über `member_head` (`--member-*`); im Admin geänderte Member-Farben haben Vorrang. Theme-Stylesheet und Scripts werden in der Member-Shell nicht mehr geladen.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite.
- Beitragsbild mit Fokus-Punkt (`object-position`).
- Vorschau-Links (`$isPreview`) und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr (bisher sieben Familien auf jeder Seite); Schriften kommen aus der Core-Schriftverwaltung, sonst System-Stack. Die Customizer-Schriftwahl wirkt wieder: die Font-Stacks wurden HTML-escaped (`&quot;`) in den `<style>`-Block geschrieben, `--font-heading` war dadurch ungültig.
- Core-SEO-Ausgabe (`theme_v4_head_tags()`) statt nur Site-Beschreibung; Seitentitel aus Modulen (`$title`).
- `cms_csp_runtime_tags(true)` als erstes Script nach `<meta charset>`; Navigation weiter per `defer`.
- Beitragsbild als LCP-Bild mit `width`/`height` und `fetchpriority="high"` statt `loading="lazy"`.

#### Barrierefreiheit
- Farb-Mapping: `section_dark_bg` landete in `--bg-secondary` und färbte die helle Sektion `.bb-section--alt` (Registrierungs-CTA der Startseite) dunkel – Text 1,2:1, Überschrift 2,1:1. Jetzt `--section-dark`; die Alt-Sektion bleibt hell (Text 16,0:1, Überschrift 6,5:1).
- Kontraste: Muted-Farbe `#78716c` → `#6b645f` (4,4:1 → 5,3:1 auf `#f5f5f4`), Outline-Button im Hero 2,1:1 → 4,8:1 (Amber), Fußzeilen-Unterzeile 6,1:1 → 7,9:1.
- Fokusrahmen: auf hellen Flächen Dunkelbraun (6,8:1 statt Gold 3,1:1), auf Header, Hero, Footer Amber (4,8–8,1:1), im Notfall-Banner in Textfarbe.
- Fußzeilen-Überschriften `h2` statt `h3` (kein Sprung unter `h1`), `aria-current`, `html lang` nach Inhaltssprache, Notfall-Banner ohne `role="alert"` (wurde bei jedem Seitenaufruf vorgelesen).
- Escape schließt Mobil-Menü und Suchpanel und gibt den Fokus an den Schalter zurück; das Suchpanel nutzt nur noch `hidden`.
- Karten-Einblendung nur mit laufendem Script: ohne JavaScript (z. B. auf der eigenständigen Wartungsseite) waren `.bb-card`-Elemente unsichtbar.
- `supports` ohne `dark-mode`: Das Theme hat keinen Dark Mode.

## 3.1.0 – 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- Kein doppelter Seitenrahmen mehr: Templates binden `header.php`/`footer.php` nicht mehr selbst ein – der Core rendert beide in `ThemeManager::render()`. `header.php`/`footer.php` sind zusätzlich gegen Doppel-Einbindung geschützt.
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 3.0.2 – 2026-05-19 Nachtrag

- Abschlussdokumentation für den Security-/Robustheits-Pass: validierte Customizer-CSS-Werte, fail-closed URL-Helper, strict_types/ABSPATH-Guards und ausgelagerte Template-Styles sind als finaler v3/PHP-8.4-Stand nachgetragen.
- `theme.json`, `update.json`, `style.css`, `functions.php` und `CHANGELOG.md` bleiben auf `3.0.2` synchron; der Update-Feed trägt das Nachtragsdatum vom 19.05.2026.

## 3.0.2 – 2026-05-18

### Security-/Robustheits-Pass (365CMS v3.x.x / PHP 8.4)

- Customizer-CSS-Werte werden vor der Ausgabe als CSS-Variablen über strikte Farb-, Zahlen- und Choice-Helper validiert bzw. auf die `theme.json`-Grenzen begrenzt.
- `advanced.custom_css` entfernt nun zusätzlich zu `</style>` auch `<style>`-Tags, `@import`, `expression()`, `javascript:`-URLs sowie Legacy-Sinks wie `behavior`/`binding`.
- Header-/Footer-/Home-/Index-/404-/Error-URLs laufen über `buildbase_safe_url()` mit sicheren Fallbacks; Logo-URLs und Post-URLs validieren fail-closed.
- Footer-Copyright wird als Text behandelt, Platzhalter werden ersetzt und anschließend escaped ausgegeben.
- `header.php`, `footer.php`, `home.php`, `page.php`, `index.php`, `404.php` und `error.php` verwenden jetzt einheitlich `declare(strict_types=1)` mit `ABSPATH`-Guard.
- `BUILDBASE_THEME_VERSION`, `style.css`, `theme.json` und `update.json` sind auf `3.0.2` synchronisiert.

## 3.0.1 – 2026-05-17

### Re-Audit-Pass (PHP 8.4 / 365CMS v3.x.x)

**Bootstrap & Konfiguration**
- Konstante `BUILDBASE_THEME_VERSION` (`3.0.1`) hinzugefügt; synchron zu `theme.json`, `style.css`-Header und `update.json`.
- `style.css` mit vollem Theme-Header inkl. `Requires PHP: 8.4`, `License`, `Text Domain`, `Tags`.
- `enqueueStyles()` als neuer `head`-Hook (Priorität 1): Preload + Stylesheet, beide mit `?v=VERSION` Cache-Busting.
- Hardcodierter `<link rel="stylesheet">` aus `header.php` entfernt.
- Google-Fonts-URL erweitert auf alle in `theme.json` deklarierten Optionen (Roboto, Roboto Condensed, Oswald, Open Sans, Lato, Inter, Bebas Neue).
- Navigation-Script bekommt jetzt ebenfalls `?v=VERSION`.

**Customizer-Verkabelung**
- `outputCustomStyles()` schreibt jetzt alle in `theme.json` deklarierten Tokens als CSS-Variablen:
  Header-/Footer-Farben, Hero-Gradient-Start/-End, Button-Radius/-Padding/-Transform,
  Container-Breite, Section-Spacing, Border-Radius, Font-Size-Base, Line-Height-Base, Font-Weight-Heading,
  Safety/Success/Error/Link-Color.
- `custom_css` aus Customizer wird gegen `</style>`-Injection gefiltert.
- `theme.json`: neue Keys `header.show_search_btn`, `build_hero.show_hero_stats`, `build_hero.hero_stat_{1,2,3}_{value,label}`, `build_content.cta_button_label`.
- `footer.php`: liest jetzt `footer.footer_show_crafts` (korrekt) statt nicht-deklariertem `show_trade_links`; Boolean via `filter_var(..., FILTER_VALIDATE_BOOLEAN)`.
- `home.php`: liest jetzt `cta_section_title` / `cta_section_text` / `cta_button_label` (alle in `theme.json` deklariert) statt nicht-deklarierter Keys `quote_section_*` / `register_cta_label`.
- Hero-Kennzahlen sind jetzt vollständig Customizer-getrieben (mit `show_hero_stats` Toggle) – keine fest verdrahteten Marketing-Zahlen mehr.

**Templates / Härtung**
- Inline `style=""` aus `index.php`, `page.php`, `404.php`, `error.php` komplett entfernt – ersetzt durch neue CSS-Utility-Klassen `.bb-page-section`, `.bb-page-title`, `.bb-page-article`, `.bb-container--narrow`, `.bb-post-title`, `.bb-post-excerpt`, `.bb-empty`, `.bb-error-screen`, `.bb-error-card`, `.bb-error-code`, `.bb-error-code--system`, `.bb-error-title`, `.bb-error-text`, `.bb-error-actions`.
- Inline `<script>document.body.classList.add('has-emergency-banner')</script>` aus `header.php` entfernt – `has-emergency-banner` wird jetzt serverseitig per `$bodyClasses[]` gesetzt.
- Body-Klassen werden gesammelt und einmal escaped ausgegeben.
- Tel-Link-Sanitation (`/[^0-9+]/`) beibehalten und vereinheitlicht.
- Alle `<a href="…">` jetzt durch `$safe()` escaped (auch interne Pfade auf Basis von `SITE_URL`).
- `SVG`-Icons bekommen `focusable="false"` zusätzlich zu `aria-hidden="true"`.
- `aria-controls="searchPanel"` / `aria-controls="site-navigation"` ergänzt für bessere Screenreader-Unterstützung.

**Design (KI-Slop Review)**
- Glassmorphism-Badge im Hero (`backdrop-filter: blur(4px)`) ersetzt durch solides Hi-Vis-Tag in Sicherheits-Amber mit schwarzen Seitenleisten – referenziert Baustellen-Schilder.
- Hero-Gradient nutzt jetzt Customizer-Werte (`--hero-gradient-start`/`--hero-gradient-end`) statt hardcodiertem Sekundär-Farbverlauf.
- Hero bekommt dezentes 36 × 36 px Blueprint-Raster als CSS-only Pseudo-Element mit Mask-Fade – direkter Verweis auf Architekten-Plan.
- Hero-Bodenkante: 45° Hazard-Stripe in Sicherheits-Amber/Schwarz als Baustellen-Markierung.
- Buttons mit solidem 2-px-Bottom-Shadow statt floating Card-Schatten – "Gewicht" statt "weicher Pill-Look".
- Cards bekommen 3-px-Top-Border (Primary → Safety-Amber im Hover) als Eck-Stempel-Referenz.
- Section-Header H2 mit 56 × 4 px Safety-Amber-Unterstreichung als Mess-Signatur.
- Standardpalette in `style.css` jetzt deckungsgleich mit `theme.json`-Defaults (vorher: Orange `#f97316` vs. Amber `#b45309`).
- Stats-Block bekommt Top-Border-Akzent und schrumpft auf 320 px sauber (responsive Tests).
- Mobile Menu (768 px): Hauptnavigation wird jetzt in dunkler Header-Farbe ausgeklappt statt heller Karten-BG – kontrastiert besser zur Sticky-Header-Optik.

**Accessibility**
- `:focus-visible` Outline + Offset bleibt; zusätzlich `.bb-focus-shadow` Utility für Form-Inputs (Search).
- `prefers-reduced-motion: reduce` Guard für Karten-Entry-Animation in `style.css`; bereits in `js/navigation.js` für IntersectionObserver vorhanden.
- `scroll-behavior: smooth` wird unter `prefers-reduced-motion: reduce` auf `auto` zurückgesetzt.
- Skip-Link bekommt Safety-Amber Hintergrund + Großbuchstaben – bewusst auffällig.

**Hinweis:** Theme enthält keine eigenen SQL-Statements (alle DB-Zugriffe via `\CMS\Services\*`); SQL-Härtungs-Audit daher nicht erforderlich.

## 3.0.0 – 2026-04-30

- Erstes Modernisierungs-Pass auf 365CMS v3.x.x: `cms_init`-Menüregistrierung,
  Font-Mapping-Helper, Reduced-Motion-Guard in Navigation, ARIA-Sync für Search-Panel.

## 1.0.0 – 2026-02-21

- Erstveröffentlichung des Bau- & Handwerk-Themes.
