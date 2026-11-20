# Feder – Blog-Theme für 365CMS

**Version:** 2.1.1
**Ziel:** 365CMS 4.0.0 – 4.2.0 · PHP 8.4.1+
**Slug:** `feder`
**Kategorie:** Blog

Lesefokussiertes Theme für persönliche Blogs, Autor:innen und Essay-Sammlungen.
Eine schmale Lesespalte, klassische Serifen-Typografie und eine ruhige
Papier-Palette mit Bordeaux-Akzent stellen den Text in den Mittelpunkt.
Ein Hell-/Dunkelmodus, Lesefortschritt und Autorenbox runden das Leseerlebnis ab.

## Startseite

`Autor:innen-Intro → neuester Beitrag (hervorgehoben) → Beitragsliste nach Jahren → Themen → Newsletter-/Kontaktbox`

Alle Inhalte der Startseite werden live aus den veröffentlichten Beiträgen und
Kategorien geladen; Texte der Intro- und Newsletter-Box stammen aus dem Customizer.

## Templates

| Datei | Zweck |
| --- | --- |
| `home.php` | Startseite |
| `blog.php` | Beitragsarchiv (Blog, Kategorie, Schlagwort, Autor:in, Übersichten) |
| `category.php`, `tag.php`, `author.php` | nutzen `blog.php` |
| `blog-single.php` | Beitrag mit Lesezeit, Teilen, Autorenbox, Navigation, verwandten Beiträgen |
| `page.php` | Statische Seite |
| `search.php` | Suche mit Trefferliste und Themen-Vorschlägen |
| `404.php` | Nicht gefunden – mit Suche und neuesten Beiträgen |
| `error.php` | Fehlerseite aller Status außer 404 (Router-Vertrag 4.0.0, eigenständig für Wartung/Fatal-Handler) |
| `contact.php` | Kontaktseite mit Core-Formular und Kontaktdaten aus dem Customizer-Bereich `contact` |
| `knowledgebase/*.php`, `newsletter/*.php` | Core-Views der Module im Feder-Rahmen (gemeinsame Overrides) |
| `index.php` | Fallback (z. B. `/autoren`, `/sitemap`) |

## Menüpositionen

| Slug | Verwendung |
| --- | --- |
| `primary` | Hauptnavigation im Header und im mobilen Menü |
| `footer-nav` | Footer-Navigation |
| `footer-legal` | Rechtliche Links (Impressum, Datenschutz) |

Solange ein Menü nicht gepflegt ist, zeigt das Theme sinnvolle Standardlinks an
(z. B. Start, Alle Texte, Themen) – ohne dabei Einträge in die Datenbank zu schreiben.

## Customizer

Theme-Editor → **Feder – Theme-Customizer** (`admin/customizer.php`, Schema in `theme.json`):

| Gruppe | Inhalt |
| --- | --- |
| `colors` | Akzent, Tinte, Text, Papier, Flächen, Linien – jeweils für hell und dunkel |
| `typography` | Überschrift-, Lese- und UI-Schrift, Schriftgröße, Zeilenhöhe, Initiale |
| `layout` | Breite der Lesespalte und breiter Elemente, Eckenradius |
| `header` | Logo, Untertitel, fixierter Header, Suche, Hell/Dunkel-Schalter, Farbschema |
| `feder_author` | Name, Rolle, Kurzbiografie, Portrait, Profil-Links, RSS |
| `feder_home` | Intro-Texte, Hervorhebung, Anzahl Beiträge, Themen, Newsletter-Box |
| `feder_article` | Lesezeit, Lesefortschritt, Teilen, Autorenbox, Navigation, verwandte Beiträge |
| `footer` | Kurzbeschreibung, Copyright (`{year}`, `{site_title}`), „Nach oben“ |
| `advanced` | Eigenes CSS (wird gefiltert) |
| `contact` | E-Mail, Telefon, Adresse, Erreichbarkeit für `contact.php` |

## 365CMS-4.x-Vertrag

- `ThemeManager::render()` bindet Header und Footer ein; beide sind gegen Doppel-Einbindung geschützt. `header.php` öffnet `<main id="main-content">` (`THEME_V4_HEADER_HAS_MAIN`, `THEME_V4_MAIN_ID`).
- `includes/cms-v4-compat.php` und `css/cms-v4.css` – gemeinsame 4.x-Schicht aller Public Themes (Fehlerseiten-Vertrag, `main_nav`, Standardlinks, OptionStore, SEO/Vorschau, Fokus-Punkt, Memberbereich, Token-Brücke `--tv4-*` zu den Core-Modulen). Die Token-Brücke steht am Ende von `style.css` (hell und dunkel).
- CSS-Präfix `fdr-` (Klassen, Custom Properties, `data-fdr-*`): `fd-` belegt der Feed-Aggregator des Cores.
- `header.php` gibt `cms_csp_runtime_tags(true)` als erstes Script aus (Theme-Scripts laden mit `defer`); alle Inline-`<style>`/`<script>` tragen den CSP-Nonce. Keine Inline-Handler, keine HTML-Sinks im JavaScript (Trusted Types).
- Kopfbereich über `theme_v4_head_tags()` (Core-SEO: Description, Robots, Canonical, Open Graph, JSON-LD); Vorschau-Links und nicht veröffentlichte Inhalte erhalten `noindex, nofollow`, keine JSON-LD und eine Hinweisleiste. Der Titel berücksichtigt `$title` der Core-Module und läuft über den Filter `page_title`.
- Desktop- und Mobilnavigation lösen den Core-Hook `main_nav` aus; Standardlinks (Fallback-Menüs, Autorenlink, Newsletter-Box) erscheinen nur, wenn das Ziel existiert.
- Memberbereich: keine Frontend-Assets, nur die Feder-Palette über `member_head`.
- `footer.php` löst `before_footer` und `body_end` aus (Cookie-Consent, Web-Vitals, PhotoSwipe).
- Beitragslinks über `PermalinkService`, Archivlinks über `cms_get_archive_url()`.
- Keine Google Fonts: Die gewählten Schriften kommen über den Filter `local_font_slugs` aus der Core-Schriftverwaltung (Slugs: `fraunces`, `literata`, `inter` …), sonst greift der System-Fallback.
- `theme.json`, `update.json`, `style.css` und `FEDER_THEME_VERSION` tragen dieselbe Version.

## Barrierearmut

Skip-Link, sichtbarer Fokus (`:focus-visible`), semantische Überschriften, `aria-current`
in Menüs, `aria-expanded`/`aria-controls` an Such- und Menü-Schaltern, Escape schließt
Panels, `prefers-reduced-motion` und `forced-colors` werden berücksichtigt, Druckansicht
ohne Navigation.
