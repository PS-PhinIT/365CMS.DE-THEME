# Changelog

## 2.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus; 403-, 429- und 503-Seiten behalten ihren Status, bei 429/503 mit `Retry-After`. Eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet und eigenem `<main>` (ohne Header, Hooks, Customizer und Datenbank); im Theme-Rahmen kein zweites `<main>`. Standardtexte je Status in Deutsch und Englisch.
- Desktop- und Mobilnavigation lösen den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Downloads, Knowledgebase, Newsletter) – auch bei leerem Menü; aktive Einträge tragen `aria-current="page"`.
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den `OptionStore` statt per SQL.
- `kontor_get_categories()` zählt veröffentlichte Beiträge mit `JOIN` + `GROUP BY` in einer Abfrage je Request statt einer Zählabfrage je Kategorie.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Seitenkopf, Brotkrumen und den vorhandenen Kontaktdaten (`kt_contact`) aus (E-Mail-Fallback: Administrator-Adresse).
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` setzen die Core-Views in den Kontor-Rahmen (Skip-Link-Ziel `#main-content`, kein doppeltes `<main>`).
- Neu: `css/cms-v4.css` (gemeinsam mit allen Public Themes) und die Token-Brücke `--tv4-*` in `style.css`: Downloads, Feeds, Formulare, Hub-Sites, Medien-Credits, Editor.js-Blöcke, Inhaltsverzeichnis, wiederverwendbare Blöcke.
- Neu: `includes/cms-v4-compat.php` mit den Helfern `theme_v4_*`.
- `page.php` läuft auch mit `$page['id'] = 0` und ohne Titel (Downloads, Feed-Aggregator); dort ohne Kontaktkarte.
- Fallback-Menüs und alle Buttons (Header, Hero, „Über uns“, Kontakt-Band, Kontaktkarte, 404) verlinken nur vorhandene Seiten und Routen (z. B. `/ueber-uns`, `/impressum`, `/datenschutz`); die Footer-Navigation enthält den Newsletter, sofern aktiv. Leere Footer-Spalten („Leistungen“, „Kontakt“) entfallen. Logo-Fallback `site_logo`, Favicon `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Kontor-Palette über `member_head` (`--member-*`); im Admin geänderte Member-Farben haben Vorrang. In der Member-Shell lädt das Theme keine Stylesheets, Schriften oder Scripts mehr.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite.
- Beitrags-, Seiten-, Hero- und Kartenbilder mit Fokus-Punkt (`object-position`).
- Vorschau-Links (`$isPreview`) und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr, auch nicht bei ausgeschalteter Option „Schriften lokal einbinden“; Schriften kommen aus der Core-Schriftverwaltung (`local_font_slugs`), sonst greifen die System-Fallbacks der Font-Stacks.
- SEO-Kopf über `theme_v4_head_tags()` statt eigenem Hook; der Dokumenttitel berücksichtigt `$title` der Core-Module und Fehlerseiten.
- `cms_csp_runtime_tags(true)` als erstes Script im `<head>`; das Theme-Script lädt weiter mit `defer`.
- LCP-Bild (Hero, Beitrag, Seite) mit `fetchpriority="high"`, übrige Bilder `loading="lazy"`; `width`/`height` aus den echten Bildmaßen lokaler Uploads.

#### Barrierefreiheit
- Fokusrahmen auf hellen Flächen in Tannengrün-Tinte (`--kt-ink`, 14,4:1 auf Weiß); das bisherige Mint erreichte dort nur 1,8:1. Auf dunklen Flächen bleibt der Rahmen weiß (14,4:1 auf Tinte, 6,5:1 auf Tannengrün).
- Skip-Link und Vorschau-Hinweis in der Inhaltssprache, `html lang` nach Inhaltssprache (`de`/`en`).

## 1.0.0 – 2026-09-27

Erstveröffentlichung des Business-Themes **Kontor** für 365CMS 3.4.00.

- Tannengrün als Markenanker mit Mint-Akzent und Sand-Flächen, Manrope-Typografie, großzügige Abstände und klare Karten.
- Kontaktleiste (Telefon, E-Mail, Social) über einem fixierten Header mit Marke, Navigation, Suche und Beratungs-Button; mobiles Menü mit Kontaktangaben.
- Startseite komplett über den Customizer pflegbar: Hero (Kicker, Überschrift mit *Hervorhebung*, Einleitung, zwei Buttons, Vertrauenspunkte, Bild oder Leistungs-Kacheln, Bewertungs-Plakette), Kundenleiste, sechs Leistungen mit Symbolauswahl und optionalem Link, Über uns mit Bild/Monogramm und Checkliste, Kennzahlen, Ablauf, Referenz, FAQ (natives `details`/`summary`) und Aktuelles aus dem Blog.
- Kontakt-Band vor dem Footer und vierspaltiger Footer mit Unternehmens-, Leistungs- und Kontaktspalte.
- Unterseiten mit Seitenkopf, Brotkrumen und Kontaktkarte in der Seitenleiste (bei Impressum/Datenschutz automatisch ohne).
- Blog „Aktuelles“ mit Kategorie-Filter, Übersichten, Archivsuche und Seitennavigation; Beitragsseite mit Lesezeit, Schlagwörtern, Teilen-Links (LinkedIn, XING, E-Mail), Seitenleiste und verwandten Beiträgen.
- Suche mit Leistungs-Vorschlägen, 404 im Hero-Stil, eigenständige Fehlerseite.
- 365CMS-3.4-Vertrag: Header/Footer gegen Doppel-Einbindung geschützt, `cms_csp_runtime_tags()`, CSP-Nonce, `body_start`/`before_footer`/`body_end`, SEO über `SEOService::renderCurrentHeadTags()`, Menüpositionen über `register_menu_locations`, lokale Schriften über `local_font_slugs`.
