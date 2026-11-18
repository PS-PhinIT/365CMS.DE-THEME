# Prüfwerkzeug für die Public Themes

Wiederholbare Prüfungen, mit denen die Audits unter `DOC/audit/v4.2.0/` durchgeführt wurden. Sie laufen **ohne** laufende 365CMS-Installation: Die Themes werden gegen Attrappen der Core-Klassen (`stubs.php`) gerendert, das Ergebnis läuft in Chromium.

## Voraussetzungen

- PHP 8.3+ und Node 20+
- Ein Checkout des Cores (`PS-PhinIT/365CMS.DE`) als Nachbarverzeichnis `../365cms.de` des Theme-Repositories **oder** `CMS_CORE_DIR=/pfad/zu/365cms.de/CMS`
- für den Browserlauf: `npm install` in diesem Verzeichnis (axe-core, Playwright; Chromium muss installiert sein, z. B. `npx playwright install chromium`)

## Prüfungen

| Aufruf | Prüft |
|---|---|
| `php <repo>/TESTS/theme-contract/run.php <theme-repo>` (im Core) | Theme-Vertrag des Cores (CSP, Hooks, Versionen) |
| `./run.sh <themedir>` | Rendert alle Vorlagen mit manipulierten Eingaben (`?q[]=`, `?p[]=`, `id = 0`, Entwurf, Fehlerseiten): je ein `<main>`, keine PHP-Warnungen, keine Inline-Tags ohne Nonce, keine `javascript:`-/Backslash-Links, keine doppelten IDs |
| `./gen.sh <themedir>` + `node run.js <themedir>` | Lädt die gerenderten Seiten mit Theme-CSS/-JS in Chromium (Desktop 1280 px, Mobil 375 px, mit und ohne reduzierte Bewegung): axe-core (WCAG 2.0–2.2 A/AA, Best Practice, u. a. Kontrast, `target-size` 2.5.8, `link-in-text-block`), horizontales Scrollen, Fokus nicht verdeckt (Tab-Durchlauf), JS-Fehler |
| `node menu.js <themedir>` | Mobilmenü bei 320/375/600/700/1024 px: Menüknopf im Bild, Panel öffnet/schließt, kein horizontales Scrollen (academy365-Selektoren; für andere Themes anpassen) |
| `node shot.js <themedir> <seite> <breite> <datei.png> [höhe]` | Screenshot einer gerenderten Seite |
| `php kit-tests/*.php <themedir>/includes/cms-v4-compat.php` | Kit-Helfer: Seitenprüfung (`links.php`, `links-large-and-failure.php`), Slugs/Pfade/Rückfälle (`smoke.php`) |

`gen.sh` schreibt nach `pages/<theme>/` (nicht versioniert). Die Seitenvarianten (Beispielinhalte aus, englische Seite, angemeldet) stehen in `gen.sh`.

## Grenzen

- Die Core-Klassen sind Attrappen: SQL, Router und `ThemeManager::render()` laufen nicht. Datenbankabfragen werden nur gezählt (`STATS=1 php render.php <themedir> <vorlage>` meldet z. B. `page_queries`).
- Kein Ersatz für Läufe gegen eine echte Installation (CSP-Header, Core-Stylesheets, Plugins, echte Inhalte).
- Die Kontrastprüfung gilt für die Standardpalette; eigene Customizer-Farben werden nicht geprüft.
