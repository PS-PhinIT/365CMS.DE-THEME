# Changelog

## 2.1.0 - 2026-11-18

### Audit 365CMS 4.2.0 (Plan und Befunde: `DOC/audit/v4.2.0/rundschau.md`)

#### Sicherheit
- `rundschau_safe_url()` und die URL-Felder des Customizers lehnen `/\host` ab: Ein Menüeintrag oder Customizer-Link `/\evil.example` wurde als lokaler Pfad ausgegeben, Browser lesen ihn als `//evil.example` (fremde Adresse).
- Schlagwort-Archiv: `%` und `_` im Schlagwort wurden in der `LIKE`-Suche nicht maskiert (`/tag/a%25b` passte auf beliebige Schlagwörter).
- Customizer-Upload: Mehrfach-Uploads (`u_…[]`) werden abgewiesen statt mit „Array to string conversion“ verarbeitet.
- Paginierung und Archiv-Suchformular bauen auf dem normalisierten Anfragepfad statt auf `REQUEST_URI` auf.

#### Fehler
- `mailto:`- und `tel:`-Menüeinträge galten als extern und öffneten einen neuen Tab; die Aktiv-Erkennung nutzt den Kit-Helfer (Pfadgrenze beim Unterverzeichnis).
- Schlagwort-Links aus Namen mit Akzenten (`Café` → `/tag/caf`) zeigten ins Leere; Slugs jetzt wie der Core.

#### 365CMS 4.2.0
- Gemeinsame Schicht **Kit 1.1.0** (`includes/cms-v4-compat.php`, `css/cms-v4.css`): Modulprüfung, Community-Helfer (nur bei aktivem Modul und vorhandenen Core-Vorlagen), `theme_v4_user_can()` (Rolle `moderator` ohne Admin-Zugang), `theme_v4_slug()`, Token-Brücke `--community-*`; Härtungen bei Backslash-Adressen und Unterverzeichnis-Erkennung.
- Standard-Footermenü: Eintrag „Leserforum“, sobald das Community-Modul aktiv ist.
- `tested_up_to` 4.2.0; `requires_cms` bleibt 4.0.0.

## 2.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus. Bisher las die Seite nur `$GLOBALS['error_code']`/`$errorCode` und ignorierte den Titel des Routers; 403-, 429- und 503-Seiten behalten ihren Status, bei 429/503 mit `Retry-After`. Eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit `style.css` und `css/cms-v4.css` (ohne Header, Hooks, Datenbank und Customizer, auch ohne `functions.php`), im Seitenrahmen entsteht kein zweites `<main>`.
- Ressort-Leiste und Mobilmenü lösen den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Kontext `desktop` bzw. `mobile`, als `<li>` im Theme-Markup) – auch beim Fallback-Menü.
- Aktive Menüeinträge erhalten `aria-current="page"` jetzt auch bei Installationen in einem Unterverzeichnis (der aktuelle Pfad wurde bisher nicht um den Pfad von `SITE_URL` gekürzt).
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den `OptionStore` statt per SQL. Die Ressort-Zählung (`rundschau_categories()`) nutzt eine gruppierte Abfrage statt einer Unterabfrage je Kategorie; alle Beitragsabfragen filtern weiter über `cms_post_publication_where()` (`status = 'published'`, 4.1.0-Status `pending` bleibt unsichtbar).

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Redaktionskontakt aus dem neuen Customizer-Bereich „Kontakt“ aus (E-Mail-Fallback `ADMIN_EMAIL`).
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` binden die Core-Views ein und setzen sie in den Theme-Rahmen (`THEME_V4_HEADER_HAS_MAIN`, Skip-Link-Ziel `#main-content`).
- Neu: `includes/cms-v4-compat.php` und `css/cms-v4.css` (gemeinsam mit allen Public Themes), Token-Brücke `--tv4-*` am Ende von `style.css` und Rahmen `.theme-v4-module` in Containerbreite.
- `page.php` (auch Downloads und Feeds mit `$page['id'] = 0`) setzt `aria-label` nur bei vorhandenem Titel.
- Fallback-Menüs (Service-Leiste, Footer „Service“, rechtliche Links) zeigen nur vorhandene Routen bzw. veröffentlichte Seiten; leere Footer-Spalten und Navigationen entfallen. Das Newsletter-Band verlinkt standardmäßig `/newsletter` und blendet den Button aus, wenn das Ziel fehlt (bisher `/kontakt`).
- Logo-Fallback auf die Core-Option `site_logo` (mit `width`/`height`), Favicon aus `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Rundschau-Palette über `member_head` (`--member-*`); im Admin geänderte Member-Farben haben Vorrang. Stylesheet, Customizer-Variablen und `theme.js` werden in der Member-Shell nicht mehr geladen.
- Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite und den Login-Showcase.
- Aufmacher-, Artikel- und Seitenbild mit Fokus-Punkt (`object-position` aus `focal_x`/`focal_y` bzw. Filter `cms_media_focal_point`).
- Vorschau-Links (`$isPreview`) und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste `.theme-v4-preview-banner`. Der Dokumenttitel übernimmt `$title` der Core-Module.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr (bisher bei ausgeschalteter Option „Schriften lokal einbinden“). Schriften kommen aus der Core-Schriftverwaltung (`local_font_slugs`), alle Font-Stacks enden auf System-Schriften.
- SEO-Ausgabe über `theme_v4_head_tags()` (Core-SEO) statt des eigenen `head`-Callbacks; eigener Header-Code des SEO-Moduls bleibt erhalten.
- `cms_csp_runtime_tags(true)` als erstes Script im `<head>`; `theme.js` lädt weiter mit `defer`.
- Aufmacher-, Artikel- und Seitenbild als LCP mit `fetchpriority="high"` (nicht lazy), Teaser- und Autorenbilder `loading="lazy"`, alle mit `width`/`height`.

#### Barrierefreiheit
- Signalfarbe `#c2410c` statt `#e8590c`: weiße Schrift auf Orange (Skip-Link, Ticker-Label, Akzent-Button) und orangefarbene Uhrzeiten auf Weiß/Fläche erreichen 5,2:1 bzw. 4,7:1 statt 3,6:1 bzw. 3,3:1. Hover des Akzent-Buttons `#9a3412`.
- Skip-Link mit doppeltem Fokusring (weiß/dunkel), sichtbar auf Service-Leiste und hellem Grund.
- `html lang` weiter nach Inhaltssprache, Skip-Link-Text zweisprachig.

## 1.0.0 – 2026-09-27

Erstveröffentlichung des News-Themes **Rundschau** für 365CMS 3.4.00.

- Header in drei Ebenen: Service-Leiste mit Datum und Claim, Masthead mit Wortmarke (oder Logo) und Suche, fixierte Ressort-Navigation in Petrol mit Signal-Orange-Markierung; mobil horizontal scrollbar mit Verlaufskanten.
- Nachrichten-Ticker unter der Navigation: neueste Beiträge oder Beiträge eines Schlagworts (z. B. „eilmeldung“), gleichmäßige Laufgeschwindigkeit, Pause beim Überfahren/Fokussieren und per Schalter, statische Liste bei `prefers-reduced-motion`.
- Startseite komplett aus der Datenbank: Aufmacher + vier Top-Meldungen, Seitenleiste „Neueste Meldungen“ (Zeitleiste) und „Meistgelesen“ (Aufrufe im wählbaren Zeitraum), automatische Ressort-Blöcke (Auswahl und Reihenfolge per Slug-Liste steuerbar), optionales „Im Fokus“-Band für ein Schlagwort und Newsletter-Band.
- Stabile Ressortfarben je Kategorie für Kicker, Rubrikenlinien, Archivköpfe und Listen (abschaltbar).
- Artikel: Brotkrumen, Kicker, Teaser, Byline mit Datum/Uhrzeit, Aktualisierung und Lesezeit, Teilen-Leiste (Link kopieren, E-Mail, WhatsApp, LinkedIn, Drucken – ohne Fremd-Scripts), Themen-Chips, Seitenleiste „Mehr aus dem Ressort“/„Meistgelesen“ und „Mehr zum Thema“.
- Archive für alle Meldungen, Ressorts, Themen und Autor:innen inklusive Übersichten (`$isOverview`), Archivsuche und nummerierter Seitennavigation; Suche, 404 und eigenständige Fehlerseite.
- 365CMS-3.4-Vertrag: Header/Footer gegen Doppel-Einbindung geschützt, `cms_csp_runtime_tags()`, CSP-Nonce für Inline-Blöcke, `body_start`/`before_footer`/`body_end`, SEO über `SEOService::renderCurrentHeadTags()`, Menüpositionen über `register_menu_locations`, lokale Schriften über `local_font_slugs`.
- Customizer mit Farben, Typografie, Layout, Header, Ticker, Startseite, Artikel, Footer und eigenem CSS.
