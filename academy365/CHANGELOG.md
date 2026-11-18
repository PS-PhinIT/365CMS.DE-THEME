# Changelog

## 4.1.1 - 2026-11-18

### Folgeaudit 365CMS 4.2.0 (Plan und Befunde: `DOC/audit/v4.2.0/academy365.md`, Abschnitt 8)

Nachgeholt wurden die im ersten Audit offenen Punkte: die Prüfung im Browser (Chromium, axe-core, WCAG 2.2 AA; Werkzeug unter `DOC/audit/v4.2.0/tools/`), die Kosten der Standardlink-Prüfung sowie Beispielinhalte und Zweisprachigkeit.

#### Fehler
- **Mobil war die Navigation nicht erreichbar:** Unter 640 px verdrängten „Anmelden“ und „Kostenlos lernen“ den Menüknopf aus dem Bild, das Logo brach in mehrere Zeilen um. Die Konto-Aktionen stehen jetzt im Menüpanel, der Titel wird gekürzt statt umgebrochen; geprüft bei 320, 375, 600, 700 und 1024 px.
- Menüknopf: Schließen per Escape oder Klick daneben ließ die Beschriftung „Menü schließen“ stehen.
- Der beim Abwärtsscrollen ausgeblendete Header bleibt sichtbar, solange der Fokus darin liegt (WCAG 2.4.11).
- Startseite: Die Kennzahlenleiste erscheint nur noch mit Inhalt (vorher leeres Element mit Beschriftung für Screenreader).

#### Barrierefreiheit (WCAG 2.2 AA, im Browser gemessen)
- Kontrast: Muted-Farbe `#6b7280` erreichte auf den Lila-Tönen nur 4,07–4,41:1, jetzt `#5b6472` (mindestens 5,04:1). Bewertungszahl in Gold auf Weiß (2,15:1) jetzt dunkel, Sterne `--accent-dark` (3,19:1). Bereits gespeicherte Customizer-Farben bleiben erhalten.
- Links im Fließtext sind unterstrichen (Violett hat nur 2,8:1 zum Text, 1.4.1).
- Zielgrößen (2.5.8): Fußzeilenlinks, Rechtslinks und Fußzeilen-Logo mindestens 24 px.

#### Performance
- **Standardlink-Prüfung:** Jeder Link auf eine Seite (Fußzeile, Startseite, 404) fragte `PageManager::getPageBySlug()` einzeln ab (`SELECT *` samt Seiteninhalt, ungepuffert): 11 bis 16 Abfragen je Seitenaufruf. Jetzt eine Abfrage je Request (nur `slug`, `slug_en` und ein Flag, höchstens 500 Zeilen; bei mehr Seiten eine gebündelte Abfrage je Aufruf). Veröffentlichungsdatum und englischer Inhalt werden berücksichtigt, Slugs unabhängig von Groß-/Kleinschreibung und Prozent-Kodierung.

#### Inhalte
- **Beispielkurse und Beispiel-Dozenten** (erfundene Bewertungen, Teilnehmerzahlen) lassen sich im Customizer abschalten (*Beispielkurse anzeigen*, *Beispiel-Dozenten anzeigen*, Standard an) und sind sichtbar als „Beispiel“ gekennzeichnet. Ohne Customizer zeigt die Startseite keine erfundenen Kennzahlen oder Superlative mehr („Die #1 Lernplattform“, „185.000 Lernende“).

#### Zweisprachigkeit
- Header, Suche, Archiv, Beitrag, Seite, 404, Fußzeile (Beschriftungen der Standardspalten, Copyright), Startseiten-Texte und die Beschriftungen des Menüknopfs (bisher fest in `navigation.js`) folgen der Seitensprache (`/en/…`); Datumsangaben je Sprache (`02.11.2026` / `Nov 2, 2026`). Die Ziele der Standardlinks in der Fußzeile bleiben die Standardrouten.

#### Wartung
- Doppelte `$safeUrl`-Closures in `blog-single.php` und `search.php` durch `academy365_safe_url()` ersetzt; `academy365_format_date()` statt zweier Kopien.
- Gemeinsame Schicht **Kit 1.2.0** (`includes/cms-v4-compat.php`): `theme_v4_link_target()`, `theme_v4_page_index()`, `theme_v4_page_is_published()`, `theme_v4_pages_published()`; `theme_v4_link_available()`/`theme_v4_filter_links()` behalten ihre Signatur. Die übrigen Themes folgen in ihren eigenen Pull Requests.

## 4.1.0 - 2026-11-18

### Audit 365CMS 4.2.0 (Plan und Befunde: `DOC/audit/v4.2.0/academy365.md`)

#### Sicherheit
- Menü-URLs (`theme_nav_menu()`) wurden mit `FILTER_VALIDATE_URL` geprüft, das `javascript://…%0a…` durchlässt: Ein Menüeintrag konnte so ein Script-Link werden. Erlaubt sind jetzt nur http(s), lokale Pfade, Anker, `mailto:` und `tel:` (`academy365_menu_url()`).
- `academy365_safe_url()` lehnt `/\host` ab (Browser werten `\` wie `/`, also eine fremde Adresse) und gibt Pfade mit Steuerzeichen nicht mehr bereinigt, sondern gar nicht zurück; `theme_route_url()` fällt nie mehr auf die ungeprüfte URL zurück.
- Paginierungslinks im Archiv bauen auf dem normalisierten Anfragepfad statt auf `REQUEST_URI` auf.

#### Fehler
- Schriftwahl im Customizer war wirkungslos: Die Font-Stacks wurden HTML-escaped in den `<style>`-Block geschrieben (`&quot;Open Sans&quot;`), die ganze `font-family`-Deklaration war ungültig.
- Die Standard-Headline zeigte `<br>` als Text („Lerne neue Skills.<br>Starte …“).
- Startseite: „Kurskatalog entdecken“, „Alle Kurse“, „Zum Kurs“, „Alle Tutoren“ und der Pro-Button verlinkten auf `/courses`, `/tutors`, `/pro` usw., auch ohne solche Seiten (404). Sie erscheinen nur noch, wenn das Ziel existiert.
- Zähler-Animation der Kennzahlen las den Tausenderpunkt als Dezimaltrenner („185.000 Lernende“ endete als „185.0 Lernende“, „2.400+ Kurse“ als „2.4 + Kurse“); am Ende steht wieder genau der Originaltext.
- Escape zog auf Mobilgeräten den Fokus auch bei geschlossenem Menü auf den Menüknopf.
- Teilnehmerzahlen mit deutschem Tausenderpunkt; Fortschrittsbalken mit zugänglichem Namen.

#### 365CMS 4.2.0
- Gemeinsame Schicht **Kit 1.1.0** (`includes/cms-v4-compat.php`, `css/cms-v4.css`): `theme_v4_module_enabled()` (unbekannte Module gelten nicht als aktiv), `theme_v4_community_available()/_url()/_link()` (Community nur bei aktivem Modul und vorhandenen Core-Vorlagen, Basispfad aus den Community-Einstellungen), `theme_v4_user_can()` (Capabilities; Rolle `moderator` ohne Admin-Zugang), `theme_v4_slug()` (`CMS\Slug::fromText()` mit Rückfall), Token-Brücke `--community-*` für die Core-Vorlagen der Community; Härtungen: `theme_v4_local_asset_url()` und `theme_v4_safe_back_url()` lehnen Backslash-Adressen ab, `theme_v4_is_current_path()` erkennt das Unterverzeichnis nur noch an einer Pfadgrenze.
- Fußzeile „Plattform“: Eintrag „Community“, sobald das Modul aktiv ist.
- Kurs-Slugs der Startseite über `theme_v4_slug()` (Umlaute wie im Core: `ae`, `oe`, `ue`, `ss`).
- WCAG 2.2 (2.4.11): `scroll-padding-top` hält fokussierte Elemente unter dem festen Header sichtbar.
- `tested_up_to` 4.2.0; `requires_cms` bleibt 4.0.0.

## 4.0.1 - 2026-11-15

### Gemeinsame 4.x-Schicht 1.0.1
- `css/cms-v4.css`: Die Variablen des Feed-Aggregators (`--fd-*`) gelten nur noch in `.fd-aggregator`/`.fd-body` statt global auf `body`.
- `theme_v4_member_tokens_style()`: `focus` erzeugt einen gültigen `box-shadow`-Wert für `--member-focus-ring` (bisher fiel der Fokus-Halo im Memberbereich weg); keine dunkle Member-Palette, weil die Tabler-Shell keinen Dunkelmodus hat.

## 4.0.0 - 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus. 403-, 429- und 503-Seiten antworten mit ihrem Status statt mit HTTP 500; eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet (ohne Header, Hooks und Datenbank), bei 429/503 mit `Retry-After`.
- Die Hauptnavigation löst den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Downloads, Knowledgebase, Newsletter). Die Navigation ist für Desktop und Mobil dieselbe, deshalb einmal mit dem Kontext `desktop`.
- Menüeinträge zeigen die Beschriftung aus `title` (der Core liefert `title`, das Theme las nur `label`), der aktive Eintrag trägt `aria-current="page"`.
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den `OptionStore` statt per SQL.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Kontaktdaten aus dem neuen Customizer-Bereich „Kontakt“ aus.
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` binden die Core-Views ein und setzen sie in den Theme-Rahmen (Skip-Link-Ziel `#main`, kein doppeltes `<main>`); `show_category_sidebar`, CSRF und Honeypot bleiben Sache des Cores.
- Neu: `css/cms-v4.css` (gemeinsam mit allen Public Themes) und die Token-Brücke `--tv4-*` in `style.css`: Downloads inkl. Block-Layout `button`, Feeds, Formulare, Hub-Sites, `cms-media-credit`, Editor.js-Bild/-Medien-Text/-Zitat/-Button, Inhaltsverzeichnis (`show_title_toc`), wiederverwendbare Blöcke.
- Neu: `includes/cms-v4-compat.php` mit den Helfern `theme_v4_*` (Fehlerseiten, `main_nav`, Standardlinks, OptionStore, SEO/Vorschau, Logout-Formular, Fokus-Punkt, Memberbereich).
- Fußzeile und 404-Seite verlinken nur noch vorhandene Seiten und Routen (z. B. `/courses`, `/newsletter`, `/barrierefreiheit` nur, wenn veröffentlicht bzw. aktiv). Logo-Fallback `site_logo`, Favicon `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Theme-Palette über `member_head` (`--member-*`); im Admin geänderte Member-Farben haben Vorrang. Das Frontend-Stylesheet wird in der Member-Shell nicht mehr geladen.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite und den Login-Showcase; Showcase-Stile für den Legacy-Modus in `css/cms-v4.css`.
- Beitragsbild mit Fokus-Punkt (`object-position` aus `focal_x`/`focal_y` bzw. Filter `cms_media_focal_point`).
- Vorschau-Links (`$isPreview`) und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr; Schriften kommen aus der Core-Schriftverwaltung, sonst System-Font-Stack.
- Core-SEO-Ausgabe (`SEOService::renderCurrentHeadTags()`) statt nur Site-Titel/-Beschreibung: Seitentitel, Canonical, Robots, Open Graph, JSON-LD.
- `cms_csp_runtime_tags(true)` als erstes Script im `<head>`; Navigation weiter per `defer`.
- Beitragsbild als LCP-Bild mit `width`/`height` und `fetchpriority="high"` statt `loading="lazy"`.

#### Barrierefreiheit
- Kontraste: Badge-Texte (bisher 2,0–3,4:1), Standard-Muted-Farbe (`#9ca3af`, 2,4:1 → `#6b7280`, 4,5:1) und Fokusrahmen (Gold 2,2:1 → Indigo 9:1, auf dunklen Flächen Gold 7,4:1).
- Fußzeilen-Überschriften `h2` statt `h4` (keine übersprungenen Ebenen), `aria-controls` an Menü- und Suchschalter, `html lang` nach Inhaltssprache.
- `supports` ohne `dark-mode`: Das Theme hat keinen Dark Mode.

## 3.1.0 - 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- Kein doppelter Seitenrahmen mehr: Templates binden `header.php`/`footer.php` nicht mehr selbst ein – der Core rendert beide in `ThemeManager::render()`. `header.php`/`footer.php` sind zusätzlich gegen Doppel-Einbindung geschützt.
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 3.0.2 - 2026-05-19 Nachtrag

- Abschlussdokumentation für den Security-/Robustheits-Pass: Template-Guards, URL-Fallbacks, Customizer-CSS-Validierung und defensive `advanced.custom_css`-Bereinigung sind als zusammengehöriger v3/PHP-8.4-Release-Stand dokumentiert.
- `theme.json`, `update.json`, `style.css`, `functions.php` und `CHANGELOG.md` bleiben auf `3.0.2` synchron; der Update-Feed trägt das Nachtragsdatum vom 19.05.2026.

## 3.0.2 - 2026-05-18

Security-/Robustheits-Pass für academy365 (365CMS v3.x.x / PHP 8.4):

- **Customizer-CSS gehärtet:** Farbwerte laufen über `academy365_css_color()`, numerische Tokens werden mit `academy365_css_number()` auf die Schema-Grenzen geclamped, Select-Werte über Allowlists geprüft.
- **Custom CSS defensiv bereinigt:** `advanced.custom_css` entfernt nun `<style>`-Tags, `@import`, `expression()`, `javascript:`-URLs sowie alte Browser-Sinks wie `behavior`/`binding` vor der Ausgabe.
- **URL-Ausgaben fail-closed:** Header-Logo, Header-Routen, Footer-Links sowie 404/Error-Aktionen nutzen `academy365_safe_url()` mit sicheren Fallbacks.
- **Template-Standard:** `header.php`, `footer.php`, `home.php`, `page.php`, `index.php`, `404.php` und `error.php` besitzen jetzt `declare(strict_types=1)` und `ABSPATH`-Guard.
- **Version & Manifest:** `ACADEMY365_THEME_VERSION`, `style.css`, `theme.json` und `update.json` auf `3.0.2`; Manifest-Anforderungen auf 365CMS `3.0.0` und PHP `8.4` gesetzt.

## 3.0.1 - 2026-05-17

Re-Audit-Pass für academy365 (365CMS v3.x.x / PHP 8.4):

- **Version & Metadaten:** `theme.json`, `update.json`, `style.css`-Header und neue Konstante `ACADEMY365_THEME_VERSION` (`functions.php`) sind synchron auf 3.0.1. `style.css` deklariert jetzt einen vollständigen Theme-Header inklusive `Requires PHP: 8.4`.
- **Customizer-Vollausbau:** `outputCustomStyles()` mappt jetzt Header-Farben, Header-Höhe, Logo-Max-Höhe, Header-Shadow-Switch, Sticky-Header-Switch, Footer-Farben, Container-Breite, Content-Padding, Border-Radius, Section-Spacing, Button-Padding/-Radius/-Weight/-Transform sowie `font_size_base` / `line_height_base` / `font_weight_heading`. Vorher wurden nur Farben und drei Fonts ausgewertet — der Rest der `theme.json`-Settings war wirkungslos.
- **Schema-Lücke geschlossen:** `header.show_search_btn` ist jetzt in `theme.json` deklariert; das Template las den Key bereits, ohne dass er im Customizer-Schema existierte.
- **Stylesheet-Auslieferung:** `header.php` linkt das Stylesheet nicht mehr direkt. Stattdessen liefert `Academy365_Theme::enqueueStyles()` `preload` + `stylesheet` mit Cache-Busting-Querystring (`?v=3.0.1`) — passend zum Pattern in 365Network.
- **Inline-Styles entfernt:** `home.php` (`style="--rating:..."` und `style="width:X%"`), `page.php` (`font-family:var(--font-body)`), `index.php`, `404.php`, `error.php` (jeweils `font-size`/`display`/`margin-top`). Dynamische Daten laufen jetzt über `data-rating` / `data-progress`; alles andere wurde in CSS-Klassen (`.ac-error-emoji`, `.ac-error-actions`, `.ac-empty-emoji`, `.ac-page-body`) extrahiert.
- **URL-Helper:** `home.php` baut alle Lern-, Registrierungs-, Kurs- und Tutoren-URLs über `theme_route_url()` mit `SITE_URL`-Pfad-Fallback statt String-Konkatenation.
- **JS-Hardening:** `js/navigation.js` setzt Progress-Bar-Breite direkt (kein Animations-Run) wenn `prefers-reduced-motion: reduce` aktiv ist; Rating-Sterne werden aus `data-rating` gelesen, mit `parseFloat` (Komma-tolerant) und Clamping `[0..5]`.
- **Design / Anti-KI-Slop:** Der Hero verwendet jetzt einen drei-schichtigen Verlauf — Indigo-Tiefe (Studium) unten links, Violett-Brand (Lernweg) in der Mitte, Gold-Korona (Zertifikat) oben rechts. Damit ersetzt die academy365-Identität den generischen 135°-Two-Stop-Verlauf des Vorgängers. Auch der Progress-Bar verläuft jetzt Violett → Indigo → Gold und visualisiert „Studium → Fortschritt → Zertifikat" anstelle eines beliebigen Zwei-Farben-Verlaufs.
- **Card-Affordanz:** Kurs-Card-Titel bekommen on-hover eine Border-Bottom-Linie in Primärfarbe statt nur eines generischen `translateY(-4px)`. Lift bleibt, aber das Lese-Signal („tiefer hineinklicken") ist jetzt explizit.
- **Responsiveness:** Neue Breakpoint-Regeln für 540 px (Section-Heads stapeln, Error-Action-Buttons stapeln) und für 900 px (Footer-Spalten kollabieren) sind explizit definiert. Hero-Headline und Container nutzen `clamp(...)` bzw. `--container-max` aus dem Customizer.

## 3.0.0 - 2026-05-17

- Compatibility Hook für Menü-Registrierung in `cms_init`.
- Customizer-Key-Mapping für Farben, Typografie, Labels und Sektions-Toggles korrigiert.
- Safe Headline / Slug Helper für robuste Ausgabe und Route-Bau.
- Inline-Template-Styles in dedizierte Stylesheet-Klassen refaktoriert.
- JS-Verhalten für Motion-Präferenzen und robuste Observer-/Search-State-Behandlung verbessert.

## 1.0.0 - 2026-02-21

- Erstveröffentlichung: E-Learning & Kursplattform-Theme.
