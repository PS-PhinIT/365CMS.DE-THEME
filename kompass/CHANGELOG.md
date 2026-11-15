# Changelog

## 2.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus; 403-, 429- und 503-Seiten behalten ihren Status, bei 429/503 mit `Retry-After`. Eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet und eigenem `<main>` (ohne Header, Hooks, Customizer und Datenbank); im Theme-Rahmen kein zweites `<main>`. Standardtexte je Status in Deutsch und Englisch.
- Die Hauptnavigation löst den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Downloads, Knowledgebase, Newsletter) – auch bei leerem Menü. Desktop und Mobil teilen sich eine Liste, deshalb einmal mit dem Kontext `desktop`.
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den `OptionStore` statt per SQL.
- `kompass_categories()` ermittelt die Beitragszahlen (Hauptkategorie oder Zuordnung) in einer vorab gruppierten Zähltabelle statt einer Unterabfrage je Kategorie; nur veröffentlichte Beiträge.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Seitenkopf, Brotkrumen und den vorhandenen Angaben aus „Service & Kontakt“ aus (E-Mail-Fallback: Administrator-Adresse).
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` setzen die Core-Views in den Kompass-Rahmen (Skip-Link-Ziel `#main-content`, kein doppeltes `<main>`).
- Neu: `css/cms-v4.css` (gemeinsam mit allen Public Themes) und die Token-Brücke `--tv4-*` in `style.css`, inklusive Werten für den hohen Kontrast: Downloads, Feeds, Formulare, Hub-Sites, Medien-Credits, Editor.js-Blöcke, Inhaltsverzeichnis, wiederverwendbare Blöcke.
- Neu: `includes/cms-v4-compat.php` mit den Helfern `theme_v4_*`.
- `page.php` läuft auch mit `$page['id'] = 0` und ohne Titel (Downloads, Feed-Aggregator); der PDF-Link erscheint nur für echte Seiten.
- Fallback-Menüs, Schnellzugriffe, „Alle Fragen und Antworten“, der Service-Button und die „Erklärung zur Barrierefreiheit“ verlinken nur vorhandene Seiten und Routen (z. B. `/hilfe-und-faq`, `/ueber-uns`, `/barrierefreiheit`, `/impressum`). Die Footer-Navigation enthält den Newsletter, sofern aktiv; die Kontaktspalte entfällt ohne Kontaktdaten. Logo-Fallback `site_logo`, Favicon `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Kompass-Palette über `member_head` (`--member-*`); im Admin geänderte Member-Farben haben Vorrang. In der Member-Shell lädt das Theme keine Stylesheets, Schriften oder Scripts mehr.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite.
- Beitrags-, Seiten- und Meldungsbilder mit Fokus-Punkt (`object-position`).
- Vorschau-Links (`$isPreview`) und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr, auch nicht bei ausgeschalteter Option „Schriften lokal einbinden“; Schriften kommen aus der Core-Schriftverwaltung (`local_font_slugs`), sonst greifen die System-Fallbacks der Font-Stacks.
- SEO-Kopf über `theme_v4_head_tags()` statt eigenem Hook; der Dokumenttitel berücksichtigt `$title` der Core-Module und Fehlerseiten.
- `cms_csp_runtime_tags(true)` als erstes Script im `<head>`; das Theme-Script lädt weiter mit `defer`.
- LCP-Bild (Beitrag, Seite) mit `fetchpriority="high"`, Meldungsbilder `loading="lazy"`; `width`/`height` aus den echten Bildmaßen lokaler Uploads.

#### Barrierefreiheit
- Skip-Link und Vorschau-Hinweis in der Inhaltssprache, `html lang` nach Inhaltssprache (`de`/`en`).
- Fokusrahmen der Core-Module in Tintenschwarz (19,6:1), im hohen Kontrast Weiß auf Schwarz; Formularrahmen 7,3:1. Alle Standard-Textkontraste geprüft (≥ 6,6:1).

## 1.0.0 – 2026-09-27

Erstveröffentlichung des Informations-Themes **Kompass** für 365CMS 3.4.00.

- Ruhiges Nordblau mit Signalgelb, Atkinson Hyperlegible für Fließtext und Public Sans für Überschriften; alle Standardfarben erfüllen WCAG 2.2 AA.
- Barrierefreiheits-Leiste: Schriftgröße A / A+ / A++ und hoher Kontrast (Schwarz/Weiß/Gelb), gespeichert im Browser und vor dem ersten Rendern angewendet; optionale Links zu Leichter Sprache und Gebärdensprache sowie Menüposition `service-nav`.
- Fokus nach GOV.UK-Muster (gelbe Fläche mit dunkler Unterkante), Skip-Link, `aria-current`, beschriftete Schalter mit `aria-pressed`.
- Hinweisbanner (Information, Wichtig, Erledigt) – wahlweise nur auf der Startseite.
- Startseite: Such-Hero mit sichtbarem Label und „Häufig gesucht“, bis zu sechs Schnellzugriffe mit Symbolen, Themenkacheln aus den Kategorien samt Unterthemen und Beitragszahl, aktuelle Informationen mit Datumsblöcken, FAQ (`details`/`summary`) und Service-Kontakt mit Sprechzeiten.
- Informationsseiten und Beiträge mit automatischem Inhaltsverzeichnis „Auf dieser Seite“ (H2/H3, aktiver Abschnitt wird markiert), Stand-Datum, Drucken, Link kopieren, PDF-Download (Seiten, sofern Dompdf verfügbar) und „War diese Seite hilfreich?“ per E-Mail.
- Archive mit Themen-Seitenleiste und Kontaktkarte, Unterthemen, Archivsuche; Übersicht „Themenbereiche“ als Themenbaum und „Themen A–Z“ mit Buchstaben-Sprungleiste.
- Suche mit Treffertyp und Datum, Suchtipps, „Häufig gesucht“ und Schnellzugriffen bei leeren Ergebnissen; 404 mit Suche und Einstiegen, Seitenübersicht für `/sitemap`, eigenständige Fehlerseite.
- 365CMS-3.4-Vertrag: Header/Footer gegen Doppel-Einbindung geschützt, `cms_csp_runtime_tags()`, CSP-Nonce, `body_start`/`before_footer`/`body_end`, SEO über `SEOService::renderCurrentHeadTags()`, Menüpositionen über `register_menu_locations`, lokale Schriften über `local_font_slugs`.
