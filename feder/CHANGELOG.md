# Changelog

## 2.1.0 - 2026-11-18

### Audit 365CMS 4.2.0 (Plan und Befunde: `DOC/audit/v4.2.0/feder.md`)

#### Sicherheit
- `feder_safe_url()` und die URL-Felder des Customizers lehnen `/\host` ab: Ein Menüeintrag oder Customizer-Link `/\evil.example` wurde als lokaler Pfad ausgegeben, Browser lesen ihn als `//evil.example` (fremde Adresse).
- Customizer-Upload: Mehrfach-Uploads (`u_…[]`) werden abgewiesen statt mit „Array to string conversion“ verarbeitet.
- Paginierung und Archiv-Suchformular bauen auf dem normalisierten Anfragepfad statt auf `REQUEST_URI` auf.

#### Fehler
- Navigation: Bei einer Installation im Unterverzeichnis war nie ein Menüeintrag aktiv (Anfragepfad mit, Menüpfad ohne Unterverzeichnis verglichen). Jetzt über `theme_v4_is_current_path()`.
- Schlagwort-Links aus Namen mit Akzenten (`Café` → `/tag/caf`) zeigten ins Leere; Slugs jetzt wie der Core (`theme_v4_slug()`: `cafe`, `groesse`).

#### Performance
- Vor/Zurück-Links unter einem Beitrag laden den Beitragsinhalt nicht mehr mit (zwei Abfragen ohne `content`).

#### 365CMS 4.2.0
- Gemeinsame Schicht **Kit 1.1.0** (`includes/cms-v4-compat.php`, `css/cms-v4.css`): Modulprüfung, Community-Helfer (nur bei aktivem Modul und vorhandenen Core-Vorlagen), `theme_v4_user_can()` (Rolle `moderator` ohne Admin-Zugang), `theme_v4_slug()`, Token-Brücke `--community-*` (auch im Dark Mode über `--tv4-*`); Härtungen bei Backslash-Adressen und Unterverzeichnis-Erkennung.
- Standard-Footermenü: Eintrag „Community“, sobald das Modul aktiv ist.
- `tested_up_to` 4.2.0; `requires_cms` bleibt 4.0.0.

## 2.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Breaking Change: CSS-Präfix `fdr-`
- Klassen, Custom Properties und `data-*`-Attribute heißen jetzt `fdr-…` statt `fd-…`. Der in 4.0.0 in den Core gewanderte Feed-Aggregator (`feeds.css`) und die gemeinsamen 4.x-Stile setzen selbst `--fd-accent`, `--fd-text`, `--fd-border` … sowie `.fd-header`, `.fd-card`, `.fd-main`, `.fd-pagination`; mit dem alten Präfix hätten sie Links, Header und Karten des Themes umgefärbt bzw. umgebaut. Eigenes CSS im Customizer (`advanced → custom_css`) bitte auf `fdr-` umstellen.

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus; 403-, 429- und 503-Seiten behalten ihren Status, bei 429/503 mit `Retry-After`. Eigenständige Fehler- und Wartungsseiten sind vollständige Dokumente mit Theme-Stylesheet und eigenem `<main>` (ohne Header, Hooks, Customizer und Datenbank); im Theme-Rahmen kein zweites `<main>`. Standardtexte je Status in Deutsch und Englisch.
- Desktop- und Mobilnavigation lösen den Core-Hook `main_nav` direkt nach den Menüeinträgen aus (Downloads, Knowledgebase, Newsletter) – auch wenn das Menü leer ist; aktive Einträge tragen `aria-current="page"`.
- `theme_use_local_fonts()` liest `privacy_use_local_fonts` über den `OptionStore` statt per SQL.
- `feder_get_categories()` zählt veröffentlichte Beiträge mit `JOIN` + `GROUP BY` in einer Abfrage je Request statt einer Zählabfrage je Kategorie.

#### Vorlagen und Core-Module
- Neu: `contact.php` gibt das Core-Formular (`$contactFormHtml`) mit Kontaktdaten aus dem neuen Customizer-Bereich „Kontakt“ aus (E-Mail-Fallback: Autor:innen-E-Mail, dann Administrator-Adresse).
- Neu: `knowledgebase/archive.php`, `knowledgebase/single.php`, `newsletter/page.php`, `newsletter/action.php`, `newsletter/issue.php` setzen die Core-Views in den Feder-Rahmen (Skip-Link-Ziel `#main-content`, kein doppeltes `<main>`).
- Neu: `css/cms-v4.css` (gemeinsam mit allen Public Themes) und die Token-Brücke `--tv4-*` in `style.css` für hell und dunkel: Downloads, Feeds, Formulare, Hub-Sites, Medien-Credits, Editor.js-Blöcke, Inhaltsverzeichnis, wiederverwendbare Blöcke.
- Neu: `includes/cms-v4-compat.php` mit den Helfern `theme_v4_*`.
- `page.php` läuft auch mit `$page['id'] = 0` und ohne Titel (Downloads, Feed-Aggregator).
- Fallback-Menüs, „Mehr über mich“ und die Newsletter-Box verlinken nur vorhandene Seiten und Routen (z. B. `/ueber-uns`, `/impressum`, `/datenschutz`). Die Newsletter-Box zeigt standardmäßig auf `/newsletter`; die Footer-Navigation enthält Newsletter und Kontakt, sofern vorhanden. Logo-Fallback `site_logo`, Favicon `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget übernehmen die Feder-Palette über `member_head` (`--member-*`); im Admin geänderte Member-Farben haben Vorrang. In der Member-Shell lädt das Theme keine Stylesheets, Schriften oder Scripts mehr. Nur die helle Palette: Die Tabler-Shell des Cores hat keinen Dunkelmodus.
- Login: Farbvorschlag `login_page.colors` in `theme.json` für die Core-Loginseite.
- Beitrags-, Seiten- und Startseitenbilder mit Fokus-Punkt (`object-position`).
- Vorschau-Links (`$isPreview`) und nicht veröffentlichte Inhalte: `noindex, nofollow`, keine JSON-LD-Ausgabe, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr, auch nicht bei ausgeschalteter Option „Schriften lokal einbinden“; Schriften kommen aus der Core-Schriftverwaltung (`local_font_slugs`), sonst greifen die System-Fallbacks der Font-Stacks.
- SEO-Kopf über `theme_v4_head_tags()` statt eigenem Hook; der Dokumenttitel berücksichtigt `$title` der Core-Module und Fehlerseiten.
- `cms_csp_runtime_tags(true)` als erstes Script im `<head>`; das Theme-Script lädt weiter mit `defer`.
- LCP-Bild (Beitrag, Seite, hervorgehobener Beitrag der Startseite) mit `fetchpriority="high"`, übrige Bilder `loading="lazy"`; `width`/`height` aus den echten Bildmaßen lokaler Uploads.

#### Barrierefreiheit
- Skip-Link und Vorschau-Hinweis in der Inhaltssprache, `html lang` nach Inhaltssprache (`de`/`en`).
- Kontraste geprüft (hell/dunkel, alle ≥ 4,5:1 für Text, Fokusrahmen ≥ 7:1); neue Formularrahmen der Core-Module ≥ 3:1.

## 1.0.0 – 2026-09-27

Erstveröffentlichung des Blog-Themes **Feder** für 365CMS 3.4.00.

- Lesefokussiertes Layout mit schmaler Lesespalte, Serifen-Typografie (Fraunces für Überschriften, Literata für den Lesetext, Inter für Metadaten) und Papier-Palette mit Bordeaux-Akzent.
- Hell-/Dunkelmodus: automatisch nach Systemeinstellung, erzwungen hell/dunkel oder per Schalter im Header (Auswahl wird im Browser gespeichert, ohne Aufblitzen beim Laden).
- Startseite: Autor:innen-Intro mit Portrait und Profil-Links, hervorgehobener neuester Beitrag, Beitragsliste gruppiert nach Jahren, Themen-Chips mit Beitragszahl, Newsletter-/Kontaktbox.
- Beiträge: Lesezeit, Lesefortschritt, Initiale, Aktualisierungsdatum, Schlagwörter, Teilen-Links (Link kopieren, E-Mail, LinkedIn, Bluesky – ohne Fremd-Scripts), Autorenbox, vorheriger/nächster Beitrag und verwandte Beiträge.
- Archive: Blog, Kategorie, Schlagwort und Autor:in in einem gemeinsamen Template inklusive Kategorie-/Schlagwort-Übersicht (`$isOverview`), Archivsuche und nummerierter Seitennavigation.
- Suche, 404 mit Suchfeld und neuesten Beiträgen, eigenständige Fehlerseite für den Fatal-Handler.
- 365CMS-3.4-Vertrag: Header/Footer gegen Doppel-Einbindung geschützt, `cms_csp_runtime_tags()` als erstes Script, CSP-Nonce für alle Inline-Blöcke, `body_start`/`before_footer`/`body_end`, SEO-Metadaten über `SEOService::renderCurrentHeadTags()`, Menüpositionen über `register_menu_locations`.
- Datenschutz: Google Fonts entfallen bei `privacy_use_local_fonts = 1`; die gewählten Schriften werden dann über `local_font_slugs` aus dem Font-Manager geladen. Teilen-Links sind reine Links.
- Customizer (`theme.json` → `customization`) mit eigener Admin-Oberfläche: Farben (hell/dunkel), Typografie, Layout, Header, Autor:in, Startseite, Beiträge, Footer und eigenes CSS; Bild-Upload pro Bildfeld.
