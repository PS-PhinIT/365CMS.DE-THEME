# Kompass – Informations-Theme für 365CMS

**Version:** 2.1.0
**Ziel:** 365CMS 4.0.0 – 4.2.0 · PHP 8.4.1+
**Slug:** `kompass`
**Kategorie:** Information / Service-Portal / Wissensdatenbank

Barrierearmes Theme für Kommunen, Vereine, Verbände, Hilfe-Center und Wissensdatenbanken.
Die Suche steht im Mittelpunkt, Themenkacheln und Schnellzugriffe führen ohne Umwege zum
Ziel. Schriftgröße und Kontrast lassen sich direkt auf der Seite umschalten.

## Startseite

`Such-Hero („Häufig gesucht“) → Schnellzugriff → Themenbereiche → Aktuelle Informationen + FAQ → Service & Kontakt`

Die Themenbereiche entstehen automatisch aus den Kategorien (Hauptkategorien mit
Unterkategorien und Beitragszahl). Alle Texte, Schnellzugriffe und Fragen sind über den
Customizer pflegbar.

## Barrierefreiheit

- Leiste mit Schriftgröße (A / A+ / A++) und hohem Kontrast – die Wahl wird im Browser
  gespeichert und schon vor dem ersten Rendern angewendet.
- Optionale Links zu **Leichter Sprache** und **Gebärdensprache**, weitere Links über die
  Menüposition `service-nav`.
- Fokus nach GOV.UK-Muster, Skip-Link, `aria-current`, `aria-pressed`, FAQ mit nativen
  `details`/`summary`, Unterstützung für `prefers-reduced-motion`, `forced-colors` und Druck.
- Footer-Link zur **Erklärung zur Barrierefreiheit** (Standard `/barrierefreiheit`, erscheint nur, wenn die Seite veröffentlicht ist – bitte die
  Seite anlegen oder den Link im Customizer anpassen/leeren).

## Templates

| Datei | Zweck |
| --- | --- |
| `home.php` | Startseite mit Such-Hero und allen Sektionen |
| `page.php` | Informationsseite mit Inhaltsverzeichnis, Stand, Werkzeugen und Rückmeldung |
| `blog.php` | Archive: Aktuelles, Thema, Stichwort, Autor:in, „Themenbereiche“, „Themen A–Z“ |
| `category.php`, `tag.php`, `author.php` | nutzen `blog.php` |
| `blog-single.php` | Beitrag mit Inhaltsverzeichnis, Stichwörtern und weiteren Informationen |
| `search.php` | Suche mit Suchtipps und Einstiegen bei leeren Ergebnissen |
| `404.php` | Nicht gefunden mit Suche, Schnellzugriffen und Kontakt |
| `error.php` | Fehlerseite aller Status außer 404 (Router-Vertrag 4.0.0, eigenständig für Wartung/Fatal-Handler) |
| `contact.php` | Kontaktseite mit Core-Formular und den Angaben aus „Service & Kontakt“ |
| `knowledgebase/*.php`, `newsletter/*.php` | Core-Views der Module im Kompass-Rahmen (gemeinsame Overrides) |
| `index.php` | Fallback (z. B. `/sitemap` als Seitenübersicht, `/autoren`) |

## Menüpositionen

| Slug | Verwendung |
| --- | --- |
| `primary` | Hauptnavigation (Fallback: Start, Themen, Aktuelles, Themen A–Z, Kontakt) |
| `service-nav` | Service-Links in der Barrierefreiheits-Leiste |
| `footer-nav` | Footer-Spalte „Service“ |
| `footer-legal` | Rechtliche Links |

## Customizer

| Gruppe | Inhalt |
| --- | --- |
| `colors` | Markenfarbe, dunkle Markenfarbe, Signalfarbe, Text, Flächen, Linien |
| `typography` | Überschrift-/Fließtextschrift (u. a. Atkinson Hyperlegible), Grundgröße |
| `layout` | Seitenbreite, Eckenradius, Inhaltsverzeichnis (an/aus, ab wie vielen Überschriften) |
| `header` | Logo, Barrierefreiheits-Leiste, Schriftgröße, Kontrast, Leichte Sprache, Gebärdensprache, Suchfeld |
| `kp_notice` | Hinweisbanner: Art, Titel, Text, Link, nur Startseite |
| `kp_hero` | Überschrift, Text, „Häufig gesucht“ |
| `kp_quick` | Bis zu sechs Schnellzugriffe (Text, Link, Symbol) |
| `kp_home` | Themenbereiche und Aktuelles (Überschriften, Anzahl) |
| `kp_faq` | Bis zu sechs Fragen und Antworten, Link zu allen Fragen |
| `kp_service` | Service-Kontakt: Organisation, Anschrift, Telefon, E-Mail, Sprechzeiten, Button, Rückmeldung |
| `footer` | Kurzbeschreibung, Link zur Erklärung zur Barrierefreiheit, Copyright |
| `advanced` | Eigenes CSS (wird gefiltert) |

## 365CMS-4.x-Vertrag

- Header/Footer über `ThemeManager::render()`, gegen Doppel-Einbindung geschützt; `header.php` öffnet `<main id="main-content">` (`THEME_V4_HEADER_HAS_MAIN`, `THEME_V4_MAIN_ID`).
- `includes/cms-v4-compat.php` und `css/cms-v4.css` – gemeinsame 4.x-Schicht aller Public Themes (Fehlerseiten-Vertrag, `main_nav`, Standardlinks, OptionStore, SEO/Vorschau, Fokus-Punkt, Memberbereich, Token-Brücke `--tv4-*` zu den Core-Modulen). Die Token-Brücke steht am Ende von `style.css` (inkl. hohem Kontrast).
- `contact.php`, `knowledgebase/*.php`, `newsletter/*.php` – Core-Formular bzw. Core-Views im Kompass-Rahmen; Kontaktdaten aus `kp_service`.
- `cms_csp_runtime_tags(true)` als erstes Script (Theme-Script mit `defer`), CSP-Nonce für Inline-Styles/-Scripts, keine Inline-Handler, keine HTML-Sinks.
- Kopfbereich über `theme_v4_head_tags()` (Core-SEO); Vorschau-Links und Entwürfe mit `noindex, nofollow`, ohne JSON-LD und mit Hinweisleiste. Der Titel berücksichtigt `$title` der Core-Module und läuft über `page_title`.
- Hauptnavigation mit Core-Hook `main_nav`; Standardlinks (Fallback-Menüs, Schnellzugriffe, FAQ-, Service- und Barrierefreiheits-Link) nur zu vorhandenen Zielen.
- Memberbereich: keine Frontend-Assets, nur die Kompass-Palette über `member_head`.
- `before_footer`/`body_end` im Footer, `body_start` nach `<body>`.
- Beitragslinks über `PermalinkService`, Archivlinks über `cms_get_archive_url()`.
- Das Core-Inhaltsverzeichnis wird ausgeblendet, sobald Kompass sein eigenes Verzeichnis in der Seitenleiste zeigt.
- Keine Google Fonts: Die Schriften kommen über `local_font_slugs` aus der Core-Schriftverwaltung (`public-sans`, `atkinson-hyperlegible` …), sonst greift der System-Fallback.
