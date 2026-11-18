# Changelog

## 2.1.0 - 2026-11-18

### Audit 365CMS 4.2.0 (Plan und Befunde: `DOC/audit/v4.2.0/newspaper.md`)

#### Sicherheit
- Customizer: `newspaper_customizer_normalize_url()` prüfte absolute Adressen nur mit `FILTER_SANITIZE_URL`, das kein Schema prüft; `javascript:…` wäre gespeichert worden. Erlaubt sind jetzt Pfade, Anker, http(s), `mailto:` und `tel:`; `//host` und `/\host` werden verworfen.
- `news_safe_url()` (Menüs, Partner-Leiste) lehnt `/\host` ab.
- Logo-Upload: Zufallsname statt `logo-<Zeitstempel>` (vorhersagbar, gleichzeitige Uploads überschrieben sich), Mehrfach-Uploads und Array-Eingaben werden abgewiesen, ein fehlgeschlagenes Anlegen des Upload-Ordners wird gemeldet.
- `blog.php`: Paginierungslinks bauen auf dem normalisierten Anfragepfad statt auf `REQUEST_URI` auf.

#### Inhalte und Fehler
- **Startseite mit echten Beiträgen:** Sind Beiträge veröffentlicht, zeigen „Breaking“ und das Archiv die neuesten Beiträge (eine Abfrage, ohne den vollen Inhalt). Die statischen Demo-Artikel und die erfundenen Schlagzeilen fremder Medien („Heise IT-News“, „Borns IT-Blog“) erscheinen nur noch ohne eigene Beiträge.
- **Partner-Leiste:** Bisher verlinkte jede Installation sieben fest eingetragene fremde Domains (Standard „an“). Die Links werden jetzt im Customizer gepflegt (*Footer → Partner-Links*, „Bezeichnung | URL“ je Zeile, Standard leer); ohne Einträge entfällt die Leiste.
- Startseite ohne JavaScript: `.news-reveal` war per CSS unsichtbar; ausgeblendet wird nur noch mit laufendem Script (`html.news-js`).
- Großschreibung der Datumsangaben mit `mb_strtoupper()` („MÄR“ statt „MäR“).
- Customizer verwendete beim Rückfall den Marketplace-Slug `cms-newspaper` als Ordnernamen.
- `declare(strict_types=1)` in allen Vorlagen (7 Dateien ergänzt).

#### 365CMS 4.2.0
- Gemeinsame Schicht **Kit 1.1.0** (`includes/cms-v4-compat.php`, `css/cms-v4.css`): Modulprüfung, Community-Helfer (nur bei aktivem Modul und vorhandenen Core-Vorlagen), `theme_v4_user_can()` (Rolle `moderator` ohne Admin-Zugang), `theme_v4_slug()`, Token-Brücke `--community-*`; Härtungen bei Backslash-Adressen und Unterverzeichnis-Erkennung.
- Fußzeile: Eintrag „Leser-Community“, sobald das Modul aktiv ist.
- WCAG 2.2 (2.4.11): `scroll-padding-top` für den Sticky-Masthead.
- `tested_up_to` 4.2.0; `requires_cms` bleibt 4.0.0.

## 2.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`). Der Theme-Ordner heißt weiterhin `newspaper`, der Marketplace-Slug bleibt `cms-newspaper`.

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus. Bisher las die Seite nur `$GLOBALS['error_code']`, zeigte immer „Etwas ist schiefgelaufen“ und lud im Fatal-Handler `functions.php` samt Datenbankzugriffen nach. 403-, 429- und 503-Seiten behalten ihren Status, bei 429/503 mit `Retry-After`; eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit `style.css` und `css/cms-v4.css`, im Seitenrahmen entsteht kein zweites `<main>`.
- Hauptnavigation (`desktop`) und Mobil-Drawer (`mobile`) lösen den Core-Hook `main_nav` direkt nach den Menüeinträgen aus, auch bei leerem Menü.
- Menüeinträge zeigen die Beschriftung aus `title` (der Core liefert `title`, das Theme las nur `label` – Einträge blieben leer); aktive Einträge tragen `aria-current="page"`, die Erkennung berücksichtigt Installationen im Unterverzeichnis und markiert `/blog` nicht mehr bei `/blogger`.
- Entfernt: `ThemeManager::registerMenuLocation()` auf `init`/`cms_init`; Menü-Positionen nur noch über `register_menu_locations` (ohne Doppelungen).
- `theme_use_local_fonts()` liest über den `OptionStore` statt per SQL. Theme-URLs über den Theme-Ordner statt `getThemeUrl('cms-newspaper')`.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Redaktionskontakt aus dem neuen Customizer-Bereich „Kontakt“ aus.
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` (Core-Views im Theme-Rahmen, Skip-Link-Ziel `#main-content`).
- Neu: `includes/cms-v4-compat.php` und `css/cms-v4.css` (gemeinsam mit allen Public Themes), Token-Brücke `--tv4-*` und Rahmen `.theme-v4-module` in `style.css`.
- `page.php` gibt das vom Core gerenderte HTML unverändert aus. Bisher entfernte `strip_tags()` u. a. `<form>`, `<button>`, `<input>`, `<picture>`, `<svg>` und `<nav>` – Downloads, Feeds, Formulare und WebP/AVIF-Bilder (4.1.0) waren dadurch kaputt.
- Footer-Spalten „Ressourcen“ und „Portal“ zeigen nur vorhandene Routen bzw. veröffentlichte Seiten (neu: Downloads, Newsletter; Knowledge Base auf `/kb`), leere Spalten und die Themen-/Rechtsmenüs ohne Einträge entfallen. Das Ausblenden der vierten Footer-Spalte auf Tablet-Breite (Impressum/Datenschutz) entfällt.
- Startseite: Demo-Kacheln, Info-Box-Links und Feed-Einträge verlinken nur noch vorhandene Ziele (bisher `/artikel/…`, `/playbooks`, `#`). „Alle Beiträge“ in `index.php` zeigt auf `/blog` statt auf die nicht vorhandene Route `/archiv`.
- Utility-Bar: Social-Profile aus dem Customizer (`social_*_url`) statt drei Platzhalter-Links auf `#`.
- Logo nur als lokale URL, Fallback `site_logo`, mit `width`/`height`; Favicon aus `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Newspaper-Palette über `member_head` (`--member-*`); Stylesheet, Customizer-Variablen und Script werden in der Member-Shell nicht mehr geladen.
- Farbvorschlag `login_page.colors` in `theme.json`.
- Beitragsbild mit Fokus-Punkt (`object-position`).
- Vorschau-Links und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr (bisher bei ausgeschalteter Option „Schriften lokal einbinden“). Der Prototyp `newspaper.html` (Google Fonts, Bilder von picsum.photos, öffentlich abrufbar) ist entfernt. Font-Stacks enden auf System-Schriften.
- SEO über `theme_v4_head_tags()` (Core-SEO). Entfernt: site-weite Meta-Description, `og:url` = Startseite und `robots: index,follow` auf allen Seiten. Dokumenttitel nach Beitrag/Seite/Modul statt immer nur Site-Titel.
- `cms_csp_runtime_tags(true)` direkt nach `<meta charset>` (bisher nach dem Viewport); `navigation.js` weiter mit `defer`, `preload` des Stylesheets entfällt (doppelte Anfrage).
- Customizer-Werte im CSS-Block werden validiert (Farben, geklemmte Zahlen, Auswahlwerte) statt nur HTML-escaped. Die Font-Stacks werden nicht mehr HTML-escaped: `&quot;` im `<style>`-Block machte `--news-font-*` ungültig, die gewählten Schriften griffen nie.
- Menü-URLs über `news_safe_url()` (nur Pfad, Anker, `http(s)`, `mailto:`, `tel:`); `FILTER_VALIDATE_URL` ließ z. B. `javascript://…` durch.
- Beitragsbild als LCP mit `fetchpriority="high"` statt `loading="lazy"`, Demo-Bilder mit `width`/`height`.

#### Barrierefreiheit
- Kontraste: Signal-Rot `#c22510` statt `#e32d12` (4,3:1 → 5,6:1 auf Papier), Meta-Grau `#6f7071` statt `#9a9b9c` (2,6:1 → 4,7:1), Akzent auf Ink-Flächen `--news-accent-on-dark` (4,4:1 → 6,7:1), Footer-Copy und Partner-Leiste `#8a8a8a` statt `#444`/`#555` (2,2/2,8:1 → 6,1:1), Footer-Überschriften 6,3:1 statt 4,5:1. Fokusrahmen auf dunklen Flächen hell.
- Footer-Überschriften `h2` statt `h4`, Footer-Spalten als `<nav>` mit Überschrift.
- Mobil-Drawer: geschlossen `hidden` (bisher nur `aria-hidden`, Links blieben per Tab erreichbar), Fokus auf den ersten Link beim Öffnen und zurück zum Schalter beim Schließen. Suchpanel ohne redundantes `aria-hidden`.
- `html lang` nach Inhaltssprache, Skip-Link und Bedienelemente zweisprachig, redundante Landmark-Rollen entfernt.
- `supports` ohne `dark-mode`: Das Theme hat keinen Dark Mode.

## 1.1.0 – 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- `header.php`/`footer.php` sind gegen Doppel-Einbindung geschützt (der Core rendert beide in `ThemeManager::render()`).
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Theme-Customizer: „Abbrechen“ im Zurücksetzen-Dialog funktioniert wieder (fehlerhaftes Attribut `data-customizer-reset-close"`); tote Legacy-Sidebar-Einbindung entfernt.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 1.0.0 – 2026-05-19 Nachtrag

### Registry- und Release-Sync

- `cms-newspaper` ist als vollständiges neues Theme in der Theme-Registry `index.json` nachgetragen.
- Die neu angelegten Theme-Dateien (`functions.php`, Templates, `style.css`, `theme.json`, `update.json`, `js/navigation.js` und Admin-/CSS-Unterordner) bleiben als ein zusammengehöriger 365CMS-v3-Initialstand dokumentiert.
- README, Changelog und Update-Feed tragen den Nachtragsstand vom 19.05.2026, ohne die Initialversion `1.0.0` zu erhöhen.

## 1.0.0 – 2026-05-18

### Erstes funktionales 365CMS-v3-Release

`cms-newspaper` ist ab dieser Version ein vollständiges 365CMS-v3-Theme
auf PHP 8.4. Aus dem bisherigen Static-HTML-Prototyp (`newspaper.html`,
0.1.0) ist ein produktiv ausgelieferbares Theme mit Customizer-Schema,
PHP-Templates und Hook-Integration geworden.

### Neue Dateien

- `theme.json` – vollständiges Customizer-Schema (`colors`, `typography`,
  `layout`, `header`, `footer`, `buttons`, `news_hero`, `news_content`,
  `advanced`) inkl. Menüpositionen `primary-nav`, `footer-nav`,
  `footer-legal`, `topics-nav`.
- `functions.php` – Singleton `CmsNewspaper_Theme` mit registrierten
  Hooks (`head`, `before_footer`, `init`, `cms_init`), Konstante
  `CMSNEWSPAPER_THEME_VERSION`, Customizer-getriebene CSS-Variablen,
  Google-Fonts mit `preconnect`, defensiver `custom_css`-Filter,
  Helper-Set (`theme_is_logged_in`, `theme_route_url`, `theme_nav_menu`,
  `get_header`, `get_footer`, `news_get_setting`, `news_get_flash`,
  `news_set_flash`, `news_config`, `news_href`, `news_site_title`,
  `news_body_class`, `news_brand_html`).
- `header.php` – 3-Bar-Masthead (Utility-Bar + Wordmark/Nav + Themen-
  Schnellnavigation), Skip-Link, Suche-Toggle mit `aria-expanded` /
  `aria-controls`, Mobile-Toggle, Suchpanel, Mobile-Drawer-Container,
  Flash-Message-Render-Slot. Body-Klassen werden serverseitig über
  `news_body_class()` gerendert.
- `home.php` – Broadsheet-Front-Page: Hero-Repo-Card, Breaking-Stack
  mit Kategorie-Badges, 2-spaltiges Info-Box-Grid, 3×3 Archiv-Grid und
  2-spaltiges Feed-Grid. Inhalte werden über `news_config()` aus dem
  Customizer gelesen, Default-Artikel-Stubs spiegeln die Inhalte des
  Original-Prototyps.
- `index.php` – Fallback-Listing mit `news-page-hero` + Call-to-Action.
- `page.php` – generische CMS-Page mit `news-prose`-Container,
  `strip_tags()`-Whitelist und Last-Updated-Zeile.
- `404.php` – gebrandetes 404 mit großer 404-Ziffer und Akzent-Underline.
- `error.php` – generische Fehlerseite, Status-Code-getrieben.
- `footer.php` – 4-spaltiges Footer-Grid (Brand + Themen + Ressourcen +
  Portal), optionaler Partner-Strip, Copy-Zeile mit `gmdate('Y')`-basiertem
  Copyright und `footer-legal`-Menü.
- `style.css` – Theme-Header (Requires PHP 8.4, License GPL-2.0-or-later,
  Text Domain `cms-newspaper`), CSS-Custom-Properties als Design-Tokens,
  Reset/Base, Layout-Primitives, Header (3 Bars), Buttons (Ink-Rectangle),
  Hero-Repo-Card, Listen-Karten, Info-Boxen, Tile-Grid, Feed-Boxen,
  Page-Hero, Prose, Error-Layout, Footer (Multi-Tier-Slab),
  Reveal-Animationen (nur bei `prefers-reduced-motion: no-preference`),
  Responsive-Walk 1024 → 768 → 480, globaler Reduced-Motion-Safety-Net.
- `js/navigation.js` – IIFE, `prefersReducedMotion`-Guard, Sticky-Header-
  Scroll-Klasse, Mobile-Drawer-Toggle (Open/Close/Outside-Click/Escape),
  Search-Panel-Toggle mit Focus-Restore, optionaler `IntersectionObserver`-
  Reveal (nur wenn Motion erlaubt; Fallback aktiviert alle
  `.news-reveal`-Elemente direkt).
- `update.json` – Update-Feed mit `version: 1.0.0`, `min_cms_version:
  3.0.0`, `requires_php: 8.4`.

### Aktualisierte Dateien

- `README.md` – vollständig neu geschrieben: dokumentiert ab sofort den
  funktionalen Zustand, Customizer-Sektionen, Security-/A11y-Hardening
  und Design-Identität. Historischer Hinweis auf die 0.1.0-Static-Phase
  ist im CHANGELOG erhalten.
- `CHANGELOG.md` – diese Datei; 0.1.0-Eintrag bleibt als historischer
  Anker erhalten.

### Design – Editoriale Newspaper-Identität

- **Editorial Trinity:** Playfair Display (Masthead-Serif), Source Sans 3
  (Body) und IBM Plex Mono (Kicker/Meta) – serif gegen sans für
  Druck-Hierarchie, mono für technische Präzision.
- **Restpalette:** Ink-Schwarz `#0a0b0c`, Off-White-Papier `#f8f9fa`,
  reines Surface-Weiß; Signal-Rot `#e32d12` ausschließlich für
  Kategorie-Badges, Breaking-Live-Indikator, das rote Quadrat-Mark in
  Section-Headers und die Top-Rule der Hero-Repo-Karte. Bewusst kein
  generisches SaaS-Indigo, keine Glassmorphism-Optik.
- **Section-Header** mit 2 px Ink-Bottom-Rule und 14 px-Akzent-Quadrat
  ersetzen generische Tag-Pills.
- **Buttons** als Ink-Rechtecke mit 4 px Radius statt Hochglanz-Pillen;
  Hover wechselt auf Signal-Rot.
- **Breaking-/Live-Dot** sparsam in der Breaking-Sektion, abschaltbar
  via Customizer.
- **Container** auf 1040 px begrenzt (editorial-Lesetempo statt
  generischer SaaS-1200/1440).

### Sicherheit & Robustheit

- Konstante `CMSNEWSPAPER_THEME_VERSION` synchron mit `theme.json` und
  `update.json`.
- Asset-URLs HTML-escaped, mit `preload` und `defer`.
- Google Fonts laden nur tatsächlich gewählte Familien.
- Boolean-Customizer-Reads ausnahmslos über `filter_var(…,
  FILTER_VALIDATE_BOOLEAN)`.
- `custom_css` defensiv gegen `</style>`-Injection gefiltert.
- `theme_nav_menu()` validiert URLs (`FILTER_VALIDATE_URL`) und akzeptiert
  ansonsten nur relative Pfade oder Anchors.
- Externe Partner-Links setzen automatisch `target="_blank"` +
  `rel="noopener noreferrer"`.

### Accessibility

- `:focus:not(:focus-visible)` + sichtbarer `:focus-visible`-Ring an
  `--focus-ring` (Signal-Rot).
- Doppelring-Utility `.news-focus-shadow` für Brand-Links und Toggles.
- `prefers-reduced-motion: reduce` schaltet Reveal-Animationen,
  Live-Dot-Pulse und Image-Hover-Scales ab.
- Mobile-Drawer und Search-Panel toggeln `aria-hidden` und stellen Fokus
  korrekt wieder her.

## 0.1.0 – 2026-05-17

- Baseline-Dokumentation für das damals noch statische `cms-newspaper`-
  Verzeichnis hinzugefügt.
- Stand zu diesem Zeitpunkt: ausschließlich `newspaper.html`
  (Static-HTML-Prototyp), kein `theme.json`, kein `functions.php`, keine
  PHP-Templates.
- Migrationspfad zur 365CMS-v3-Themestruktur dokumentiert. Dieser Pfad
  wurde mit Release 1.0.0 abgeschlossen.
