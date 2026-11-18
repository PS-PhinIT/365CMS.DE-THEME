# Changelog

## 2.1.0 - 2026-11-18

### Audit 365CMS 4.2.0 (Plan und Befunde: `DOC/audit/v4.2.0/personalflow.md`)

#### Sicherheit
- Menü-URLs: `//host` und `/\host` galten als lokale Pfade und wurden als Link auf eine fremde Adresse ausgegeben. Neuer Prüfer `pf_menu_url()` (http(s), lokale Pfade, Anker, `mailto:`, `tel:`).
- Entfernt: „Merken“ (`?action=save`) an Stellenkarten und „Kontakt aufnehmen“ (`?action=contact`) an Kandidatenkarten. Beide waren GET-Links ohne Handler, die nur die Zielseite neu luden; eine Merkfunktion per GET wäre zudem CSRF-anfällig.

#### Fehler
- Startseite ohne JavaScript: `.pf-reveal` war per CSS unsichtbar (offen seit 2.0.0); ausgeblendet wird jetzt nur mit laufendem Script (`html.pf-js`).
- `mailto:`- und `tel:`-Menüeinträge wurden zu `#`.
- `declare(strict_types=1)` in allen Vorlagen (7 Dateien ergänzt).

#### 365CMS 4.2.0
- Gemeinsame Schicht **Kit 1.1.0** (`includes/cms-v4-compat.php`, `css/cms-v4.css`): Modulprüfung, Community-Helfer (nur bei aktivem Modul und vorhandenen Core-Vorlagen), `theme_v4_user_can()` (Rolle `moderator` ohne Admin-Zugang), `theme_v4_slug()`, Token-Brücke `--community-*`; Härtungen bei Backslash-Adressen und Unterverzeichnis-Erkennung.
- Fußzeile „Wissen & Kontakt“: Eintrag „Community“, sobald das Modul aktiv ist.
- WCAG 2.2 (2.4.11): `scroll-padding-top` hält fokussierte Elemente unter dem festen Header sichtbar.
- `tested_up_to` 4.2.0; `requires_cms` bleibt 4.0.0.

## 2.0.0 - 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `requires_php`, `tested_up_to: 4.1.0`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus. 403-, 429- und 503-Seiten antworten mit ihrem Status statt mit HTTP 500; eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet (ohne Header, Hooks und Datenbank), bei 429/503 mit `Retry-After`. Bisher rief `error.php` selbst `get_header()`/`get_footer()` auf; die Fehlerkarte trug `pf-reveal` und blieb ohne `navigation.js` (eigenständige Seite) unsichtbar.
- Desktop-Navigation und Mobil-Drawer lösen den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Kontext `desktop` bzw. `mobile`), auch bei leerem Menü.
- Menüeinträge zeigen die Beschriftung aus `title` (der Core liefert `title`, das Theme las nur `label`), der aktive Eintrag trägt `aria-current="page"`; Menü-URLs mit fremdem Schema werden verworfen.
- `registerNavMenus()` mit dem nicht existierenden `ThemeManager::registerMenuLocation()` entfernt; Menüpositionen über den Filter `register_menu_locations` (der `menus`-Block in `theme.json` bleibt als Admin-Fallback, der Core dedupliziert nach Slug).
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den `OptionStore` statt per SQL.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Kontaktdaten aus dem neuen Customizer-Bereich „Kontakt“ aus.
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` binden die Core-Views ein und setzen sie in den Theme-Rahmen (Skip-Link-Ziel `#main`, kein doppeltes `<main>`).
- Neu: `css/cms-v4.css` (gemeinsam mit allen Public Themes) und die Token-Brücke `--tv4-*` in `style.css` für Downloads, Feeds, Formulare, Hub-Sites, Medien-Credits, Editor.js-Blöcke, Inhaltsverzeichnis und wiederverwendbare Blöcke.
- Neu: `includes/cms-v4-compat.php` mit den Helfern `theme_v4_*`.
- Fußzeile, Hero, 404-Seite und Demo-Karten zeigen die Theme-Ziele `/jobs`, `/kandidaten`, `/arbeitgeber`, `/pipeline`, `/preise` (und Downloads, Wissensdatenbank, Newsletter, Kontakt) nur, wenn die Seite bzw. Route existiert; leere Spalten und eine leere „Service“-Spalte entfallen. Logo-Fallback `site_logo`, Favicon `site_favicon`.
- `partials/candidate-card.php`: Avatare nur aus lokalen Uploads (mit `width`/`height`), Aktionslinks mit Kandidatenname für Screenreader.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Theme-Palette über `member_head` (`--member-*`); im Admin geänderte Member-Farben haben Vorrang. Stylesheet, Custom-Vars und Navigation-Script werden in der Member-Shell nicht mehr geladen.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite.
- Beitragsbild mit Fokus-Punkt (`object-position`), `width`/`height` und `fetchpriority="high"` als LCP-Bild statt `loading="lazy"`.
- Vorschau-Links und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr (auch ohne „Schriften lokal einbinden“); Schriften kommen aus der Core-Schriftverwaltung, sonst System-Font-Stack (`system-ui` bzw. Serif-Fallback ergänzt).
- Core-SEO-Ausgabe (`theme_v4_head_tags()`) statt der festen Tags aus `outputMetaTags()` (`robots index,follow` auch für Vorschauen, `og:url` der Startseite auf jeder Seite); Dokumenttitel aus Seitentitel/Core-SEO statt immer Site-Titel.
- `cms_csp_runtime_tags(true)` als erstes Script direkt nach `<meta charset>`; Navigation weiter per `defer`.
- Customizer-Farben und -Zahlen werden vor der Ausgabe im `<style>`-Block validiert (bisher nur HTML-escaped); `button_transform` nur aus den erlaubten Werten. Copyright-Text wird escaped (Entities bleiben erhalten).
- Generische Suchformular-Stile auf die Suchseite begrenzt (sie verschoben bisher auch das Header-Suchpanel).

#### Barrierefreiheit
- Fokus: `.pf-focus-shadow` und Buttons zeigen einen vollen Ring (`#92400e`, 6,9:1) statt eines Schattens mit 22 % Deckkraft (1,4:1); im Amber-CTA weiß (5,0:1), im Footer `#fde68a` (14:1). Suchfeld mit sichtbarem Fokusrand.
- Muted-Farbe `#78716c` → `#6b645f`: auf `--pf-paper-alt` 4,47:1 → 5,4:1, auf den Stage-Flächen des Pipeline-Ribbons 4,3:1 → ≥ 4,7:1 (auch als Customizer-Standard in `theme.json`).
- Formularfeld-Rand `--pf-input-border` 3,4:1 statt 1,5:1.
- „Alle Beiträge“-Button war unsichtbar (weiße Schrift auf weißer Karte).
- Geschlossener Mobil-Drawer ist per `visibility: hidden` nicht mehr per Tab erreichbar (vorher nur `opacity: 0` + `aria-hidden` mit fokussierbaren Links), Fokus wandert beim Öffnen in den Drawer.
- Fußzeilen-Überschriften `h2` statt `h3`, Fußzeilen-Spalten als benannte `nav`, Stellentitel als `h3` unter der Abschnitts-`h2`, `html lang` nach Inhaltssprache, redundante `role`-Attribute entfernt.

## 1.1.0 - 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- Kein doppelter Seitenrahmen mehr: Templates binden `header.php`/`footer.php` nicht mehr selbst ein – der Core rendert beide in `ThemeManager::render()`. `header.php`/`footer.php` sind zusätzlich gegen Doppel-Einbindung geschützt.
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 1.0.1 - 2026-05-19 Nachtrag

### Abschlussdokumentation v3/PHP 8.4

- `README.md`, `CHANGELOG.md`, `partials/candidate-card.php`, `functions.php`, Templates, `style.css`, `theme.json`, `update.json` und `js/navigation.js` sind als finaler PersonalFlow-Completion-/Re-Audit-Stand dokumentiert.
- Nachgetragen sind die technischen Details zu Pipeline-Stage-Labels, Candidate-Card-Partial, Body-Class-Rendering, Search-/Mobile-Drawer-A11y und vollständigem Customizer-Key-Mapping.
- Der Update-Feed trägt das Nachtragsdatum vom 19.05.2026, ohne die Version `1.0.1` erneut zu erhöhen.

## 1.0.1 - 2026-05-18

### Inventory-Pfad
- Stand bei Audit-Beginn: partiell v3-strukturiertes Theme bei Version
  1.0.0 mit substanziellen Lücken (hartcodiertes `<link>` im Header,
  fehlende `enqueueStyles()`, Customizer-Key-Mismatches zwischen
  `theme.json` und Templates, viele Inline-Styles, kein Helper-Set,
  falsche Beschreibung in `update.json`, kein README/CHANGELOG).
- Gewählter Pfad: **Completion + Re-Audit** – fehlende Konventions-
  Bausteine ergänzt und bestehende Stellen gegen die Logilink-/
  MedCarePro-/CMS-Newspaper-Vorbilder begradigt.

### v3 / PHP 8.4 / Hardening
- `PERSONALFLOW_THEME_VERSION` und `PERSONALFLOW_THEME_SLUG` als
  Theme-Konstanten eingeführt; `theme.json`, `style.css`, `update.json`
  und `functions.php` auf `1.0.1` synchronisiert.
- `style.css`-Header erweitert: `Requires PHP: 8.4`,
  `License: GPL-2.0-or-later`, `Text Domain: personalflow`,
  `Tested up to CMS: 3.x`.
- `enqueueStyles()` (`head:1`) und `enqueueScripts()`
  (`before_footer:99`) eingeführt – versioniert via
  `?v=PERSONALFLOW_THEME_VERSION`, Stylesheet zusätzlich als
  `<link rel="preload" as="style">`. Hartcodiertes
  `<link rel="stylesheet">` aus `header.php` entfernt.
- `outputMetaTags()` (`head:10`) schreibt Description, OG-URL,
  Theme-Color und Robots.
- `outputCustomStyles()` mappt jetzt **jeden** in `theme.json`
  deklarierten Customizer-Schlüssel auf eine CSS-Variable – Brand,
  Pipeline-Status-Paare (sechs fg/bg-Paare), Flächen-/Text-/Rule-Tokens,
  Status (success/warning/error), Typografie (Schriftart/Größe/
  Zeilenhöhe/Heading-Weight), Layout (Container/Padding/Radius/
  Section-Spacing), Header (bg/text/border/height/logo-max), Footer
  (bg/text/link), Buttons (radius/padding/weight/transform), Focus-Ring
  und Focus-Ring-Shadow. Booleans (Sticky-Header, Header-Schatten)
  gehen durch `filter_var(..., FILTER_VALIDATE_BOOLEAN)`.
- `mapFontChoice()` neu eingeführt mit allen in `theme.json`
  deklarierten Body-/Heading-/Stats-Familien (Nunito Sans, Nunito,
  Inter, Open Sans, Lato, Roboto, Poppins, Manrope, Raleway,
  Montserrat, Playfair Display, JetBrains Mono, Roboto Mono, Roboto
  Condensed, Oswald, System Monospace). Slot-spezifische
  Fallback-Ketten für `body` / `heading` / `stats`.
- Google-Fonts-URL lädt nur tatsächlich gewählte Familien. Bei reinem
  System-Stack (`system-mono`) entfällt der ganze `<link>`-Tag.
  `preconnect`-Hinweise sind HTML-escaped.
- `custom_css` wird über `sanitizeCustomCss()` defensiv gegen
  `</style>`-Injection regex-gefiltert.
- `cms_init` + `init` Doppelregistrierung der Menü-Locations;
  zusätzlich `register_menu_locations`-Filter.

### Customizer-Key-Konsistenz (war: vorher gebrochen)
- Vorher las `home.php` `hr_hero.cta_jobseeker_label`,
  `cta_jobseeker_url`, `cta_employer_label`, `cta_employer_url`,
  `show_stats_bar` und `hr_content.candidates_section_title`,
  `match_score_label`, `register_cta_label` – **keine** dieser Keys
  existierte in `theme.json`. Jetzt:
  - Hero-Keys in `theme.json` als `hero_badge`, `hero_headline`
    (mit `<span class="hl">`-Support), `hero_subline`,
    `cta_jobseeker_label/url`, `cta_employer_label/url`,
    `hero_aside_title`, `show_pipeline_ribbon` deklariert und in
    `home.php` exakt so gelesen.
  - Content-Keys: `talent_section_title/subtitle`,
    `jobs_section_title/subtitle`, `salary_label`, `remote_label`,
    `available_label`, `kpi_section_title`, `kpi_*_label/value` (vier
    KPI-Paare), `cta_section_title/text`, `cta_register_label`.
- `header.show_search`, `header.search_placeholder`,
  `header.show_header_shadow`, `footer.show_pipeline_links`,
  `footer.show_employer_links` – früher gelesen, nicht deklariert –
  jetzt in `theme.json` und allen Templates konsistent.

### Helper-Set
- Theme-lokales Helper-Set hinzugefügt, parallel zu MedCare Pro /
  LogiLink: `theme_is_logged_in`, `theme_route_url`, `theme_nav_menu`,
  `get_header`, `get_footer`, `pf_get_setting`, `pf_get_flash`,
  `pf_set_flash`, `pf_href`, `pf_site_url`, `pf_site_title`,
  `pf_body_class`, `pf_safe_headline`.
- `pf_stage_label()` löst Pipeline-Stage-Slugs (`applied` … `archived`)
  auf die Customizer-Labels auf.
- `pf_body_class()` rendert Sticky-Header- und Shadow-Mode-Klassen
  serverseitig (kein JS-Class-Mutation vor Paint).

### Templates
- `header.php`: hartcodierter Stylesheet entfernt; `pf_body_class()`
  rendert die Body-Klasse server-seitig; Logo mit `pf-logo-mark`-
  Initial-Plate (kein Emoji-Dependency mehr); Search-Toggle und
  Mobile-Toggle tragen `type="button"`, `aria-expanded`,
  `aria-controls`, `aria-label`; Search-Panel hat
  `aria-hidden="true"` per Default. Neuer `<nav id="pfMobileDrawer">`
  als separater Drawer mit eigenem `aria-hidden`-Toggle und
  Overlay-Element für Outside-Click.
- `footer.php`: nutzt `gmdate('Y')`-Template `{year}` / `{site_title}`,
  alle Links durch `theme_route_url()` aufgelöst,
  `theme_nav_menu('footer-nav')` und `theme_nav_menu('footer-legal')`
  eingebunden, keine Inline-Styles.
- `home.php`: vollständig neu strukturiert. Hero mit zweispaltiger
  Komposition (Copy + Talent-Aside), Pipeline-Ribbon direkt unter dem
  Hero, KPI-Strip mit JetBrains-Mono-tnum-Zahlen, Talent-Pool-Grid
  über `partials/candidate-card.php`, Job-Board-Grid mit
  Job-Card-Pattern, Registrierungs-CTA. Alle Daten aus dem
  Customizer; Demo-Records inline und klar als Platzhalter markiert.
- `index.php`, `page.php`, `404.php`, `error.php`: alle Inline-
  `style=""`-Attribute entfernt; durch semantische CSS-Klassen ersetzt
  (`pf-page-shell`, `pf-error-shell`, `pf-error-card`,
  `pf-error-code`, `pf-error-actions`, `pf-prose-wrap`, `pf-prose`,
  `pf-meta-line`); URLs `htmlspecialchars`-escaped und über
  `theme_route_url()` aufgelöst.
- Neu: `partials/candidate-card.php` – wiederverwendbare Talent-Card
  mit Avatar-Plate (Image oder Initialen), Name, Rolle, Standort,
  Stage-Badge, Skill-Chips, Verfügbarkeits-Indikator und Action-Row.

### CSS
- Komplette Neuschreibung von `style.css` mit `--pf-*`-Token-System.
- Pipeline-Status-System mit sechs WCAG-AA-konformen fg/bg-Paaren
  gegen `--pf-surface = #ffffff`:
  - `applied   #475569 / #e2e8f0`
  - `screened  #b45309 / #fef3c7`
  - `interview #1d4ed8 / #dbeafe`
  - `offer     #6d28d9 / #ede9fe`
  - `hired     #15803d / #dcfce7`
  - `archived  #57534e / #f5f5f4`
- Pipeline-Step-Komponente (`.pf-pipeline-step--*`) rendert eine
  5-stufige Ribbon-Sicht mit eigener Stage-Farbe je Spalte.
- Talent-Card-Pattern (`.pf-talent-card`) mit Avatar-Spalte (Initialen
  via CSS oder Image-Override), Meta-Zeile, Stage-Row und
  Action-Border.
- Job-Card-Pattern (`.pf-job-card`) mit linksbündigem Titel,
  rechts-anchored Remote-Badge, Gehalts-Display in Stats-Font,
  Tag-Row und CTA-Border.
- KPI-Strip mit `font-feature-settings: "tnum","lnum"` für
  tabellarische Ziffern.
- Eyebrow-Pille im Hero und Section-Eyebrow für Sektions-Kicker
  (mit `font-stats` und Caps-Tracking).
- Skip-Link, `.pf-skip-link` als erstes fokussierbares Element.
- Fokus-Token `--focus-ring` und `--focus-ring-shadow`;
  `:focus:not(:focus-visible)` neutralisiert; sichtbarer
  `:focus-visible`-Ring + `.pf-focus-shadow`-Utility für Inputs/
  Buttons/Logos.
- Mobile-Drawer mit Overlay (`.pf-mobile-overlay`) und
  Scroll-Lock-Pattern. Search-Panel als separate Komponente.
- Responsive Brüche bei 480 / 768 / 960 Pixel; Container-Padding
  reduziert sich progressiv. `prefers-reduced-motion: reduce`
  deaktiviert Hover-Translate und Reveal-Transitions.
- Print-Styles entfernen Header/Footer/CTAs.

### JS
- `js/navigation.js` neu nach dem LogiLink-/CMS-Newspaper-Pattern:
  Sticky-Header-Scroll-State, Mobile-Drawer mit `aria-hidden`/
  `aria-expanded` + Outside-Click + Escape + Focus-Restore +
  Body-Scroll-Lock, separates Search-Panel mit Focus-Restore zum
  Opener, Reveal nur bei
  `!prefersReducedMotion && 'IntersectionObserver' in window`, sonst
  unmittelbares `is-visible`-Fallback. IDs umbenannt zu
  `pfMobileToggle`, `pfMobileDrawer`, `pfMobileOverlay`,
  `pfSearchToggle`, `pfSearchPanel`, `pfSearchClose`.

### Dokumentation
- `README.md` neu (vorher nicht vorhanden) – Designprofil,
  Pipeline-Status-Tabelle, Konstanten/Hooks, Helper-Set,
  Customizer-Sektionen, Asset-Pipeline, Sicherheits- und A11y-
  Praktiken, Verwendung der Candidate-Card.
- `CHANGELOG.md` neu (vorher nicht vorhanden).
- `update.json` korrigiert: vorher fälschlich
  "Portfolio & Personal Brand Theme", jetzt HR-/Personalvermittlungs-
  Beschreibung; `min_cms_version` von `0.20.0` auf `3.0.0` angehoben.

## 1.0.0 - 2026-02-21

- Erstveröffentlichung: HR- & Personalvermittlungs-Theme.
