# Audit 365CMS 4.2.0: Public Themes (Sicherheit, Bugs, Exploits, Performance, Best Practice, Kompatibilität)

> **Stand:** 2026-11-18 | **Core-Referenz:** `PS-PhinIT/365CMS.DE` `main` (`Version::CURRENT` 4.1.8, Changelog-Abschnitt „v4.2.0 – in Entwicklung“, Schema `v27`, `MIN_PHP` 8.4.1) | **Vorgänger:** [PUBLIC-THEMES-V4-UPGRADE-2026-11-15.md](../PUBLIC-THEMES-V4-UPGRADE-2026-11-15.md)
> **Ausgeschlossen:** das private Theme `cms-phinit` (anderes Repository) und `cms-default` (liegt im Core-Repository).

Dieses Verzeichnis enthält **einen Audit-Plan je Theme** (Dateien unten). Jeder Plan nennt die Angriffsfläche des Themes, die konkret zu prüfenden Stellen (mit Datei und Zeile zum Zeitpunkt der Planung), die 4.2.0-Integrationspunkte, die Tests und die Abnahmekriterien. Die Ergebnisse werden im Abschnitt **Befunde** desselben Plans nachgetragen, sobald das Theme geprüft ist.

## 1. Pull-Request-Zuschnitt (immer 5 Audits = 1 PR)

| PR | Themes | Branch |
|---|---|---|
| 1 | [academy365](academy365.md), [buildbase](buildbase.md), [business](business.md), [feder](feder.md), [kompass](kompass.md) | `claude/serene-rubin-szlbaj` |
| 2 | [kontor](kontor.md), [logilink](logilink.md), [medcarepro](medcarepro.md), [newspaper](newspaper.md), [personalflow](personalflow.md) | `claude/serene-rubin-szlbaj-pr2` |
| 3 | [PTC](PTC.md), [rundschau](rundschau.md), [technexus](technexus.md) | `claude/serene-rubin-szlbaj-pr3` |

Jeder PR ändert nur die Ordner seiner Themes und den jeweiligen Plan. Die gemeinsame Schicht (Kit, Abschnitt 4) wird pro PR in genau dessen Themes byte-gleich verteilt; nach PR 3 sind alle 13 Themes wieder auf demselben Kit-Stand.

## 2. Was 4.2.0 für Themes bedeutet

Ausgewertet wurden `Changelog.md` (4.1.9, 4.2.0), `DOC/workflow/specs/community/` (insbesondere `THEME-UI.md`, `HANDOFF-PR2-PR5.md`), `CoreModuleService`, `CMS\Slug`, `Router` (form_guard) und `TESTS/theme-contract`.

| 4.2.0-Änderung | Wirkung auf Themes | Maßnahme |
|---|---|---|
| Kernmodul `community` (standardmäßig aus, fail-closed, Routen unter `/forum` folgen in PR #2b–#5) | Links auf `/forum` dürfen nur erscheinen, wenn das Modul aktiv ist und die Route antwortet; sonst 404-Links | Kit: `theme_v4_module_enabled()`, `/forum` in `theme_v4_link_available()` an das Modul gekoppelt |
| Core-Vorlagen `CMS/views/community/*` mit Theme-Overrides `themes/<slug>/community/<name>.php`, Design-Tokens `--community-*` mit Rückfall auf `--accent`, `--ink`, `--surface`, `--ink-muted`, `--rule-heavy`, `--r` (Muster `forms-public.css`) | Ohne Brücke erscheinen Forum-Seiten in Core-Standardfarben; Themes mit gleichnamigen eigenen Variablen (`--accent` …) könnten falsche Werte durchreichen | Kit-CSS: `--community-*` **und** die Rückfallnamen nur innerhalb `.cms-community`/`[class*="cms-community"]` aus `--tv4-*` setzen (keine globale Kollision); keine Override-Dateien, solange der Core keine Vorlagen liefert |
| Core-Vorlagen bringen (wie Wissensdatenbank/Newsletter) eigenes `<main>` mit | Themes, deren `header.php` `<main>` öffnet, bekämen verschachtelte Landmarks | Kit: `theme_v4_render_module_view()` kennt das Modul `community`; Plan-Punkt K5 je Theme |
| Neue Systemrolle `moderator` (Mitgliederrechte + `community.moderate`, **kein** `/admin`) | Admin-Links dürfen nicht an Rollen-Strings, sondern nur an `isAdmin()`/Capabilities hängen; Mitglieder-Links gelten für alle angemeldeten Rollen | Prüfung K2 je Theme |
| `CMS\Slug::fromText()` (Umlaute → ae/oe/ue/ss) | Theme-eigene Slug-/Anker-Erzeugung (Inhaltsverzeichnis, Überschriften-IDs, Filter) soll dieselben Slugs wie der Core bilden | Kit: `theme_v4_slug()` mit Rückfall für 4.0/4.1 |
| `notifications.group_key`/`event_count` | Nur relevant, wenn ein Theme Benachrichtigungszähler selbst abfragt | Prüfung K7 |
| `user_role_changed`, Schema `v27`, Capabilities `capabilities_v27` | keine direkte Theme-Wirkung | nur dokumentiert |
| 4.1.9 (Kulanzzeit, Block „Vorlagen“ in KB/Hub, Rückzug) | keine Theme-Wirkung (Mitgliederbereich ist Core-Shell) | nur dokumentiert |
| Router `form_guard` (unverändert seit 3.4.15) | Jedes öffentliche POST-Formular außerhalb der Bypass-Liste braucht `csrf_token` (Aktion `form_guard`) | Prüfung K8 |

Metadaten nach dem Audit: `requires_cms` bleibt **4.0.0** (alle Helfer sind rückwärtskompatibel), `tested_up_to` **4.2.0**, `requires_php` 8.4.1; Minor-Version je Theme (+0.1.0), weil 4.2.0-Funktionen hinzukommen und keine Mindestversion steigt.

## 3. Prüfkatalog

Jeder Theme-Plan referenziert diese IDs. „Pflicht“ heißt: jede Fundstelle wird geprüft und das Ergebnis im Plan notiert.

### S – Sicherheit und Exploits
| ID | Prüfpunkt | Methode |
|---|---|---|
| S1 | Ausgabe-Escaping je Kontext (HTML, Attribut, URL, CSS, JS/JSON); jede rohe `echo`-Senke begründet | Grep `echo $…`, `<?= `, Lesen der Quelle jeder Variable |
| S2 | URL-Schemata aus Customizer, Menüs, Inhalten (`javascript:`, `data:`, `vbscript:`, `//host`); offene Weiterleitungen | Datenfluss bis `href`/`src`/`action`/`Location` |
| S3 | CSS-/HTML-Injection über Customizer-Werte in `<style>` (Farben, Zahlen, Schriften, eigenes CSS mit `</style>`) | Validierung vor Ausgabe |
| S4 | CSRF aller schreibenden Formulare (Customizer speichern/zurücksetzen, Logout-POST, Login/Registrierung, eigene POSTs gegen `form_guard`) | Formular → Handler |
| S5 | Zugriffskontrolle: Customizer nur `isAdmin()`, Direktaufruf, Rolle `moderator` | Lesen, Rollenmatrix |
| S6 | Uploads (Endung **und** `finfo`-MIME, Größe, SVG/HTML ausgeschlossen, Zielname, `is_uploaded_file`) | Lesen der Upload-Pfade |
| S7 | SQL: nur Prepared Statements, Integer-Bindung, `cms_post_publication_where()` (keine Entwürfe/geplanten/privaten Inhalte), Präfix | Grep SQL, Lesen |
| S8 | Request-Daten: Typprüfung (Arrays in `$_GET`/`$_POST` → `TypeError`/Warnung), Länge, Session-Flash | Fuzz mit Array-/Überlängen-Parametern |
| S9 | CSP: `cms_csp_runtime_tags(true)`, Nonces, keine Inline-Handler/`javascript:`, keine externen Ressourcen | Theme-Vertragstest, Grep |
| S10 | JS-Senken (`innerHTML`, `insertAdjacentHTML`, `document.write`), Storage-Werte nur als Allowlist | Lesen der JS-Dateien |
| S11 | Informationslecks: öffentlich abrufbare Dateien (README/CHANGELOG/JSON verraten Versionen), Debug-Ausgaben, Demo-Kontaktdaten, Pfade | Dateiliste, Lesen |
| S12 | Direktaufruf: `ABSPATH`-Guard in jeder PHP-Datei, keine Seiteneffekte beim Laden von `functions.php` | Grep |
| S13 | Externe Links `rel="noopener noreferrer"`, nutzergenerierte Links `nofollow ugc` | Grep `target="_blank"` |

### B – Bugs und Robustheit
| ID | Prüfpunkt |
|---|---|
| B1 | PHP 8.4: implizit nullbare Parameter, `strict_types`-Typfehler (`date()`/`strtotime()`/`number_format()` mit falschen Typen), entfernte Funktionen |
| B2 | Undefinierte Indizes/Variablen, `null` aus Core-APIs, leere Listen, fehlende Bilder |
| B3 | Template-Verträge: `error.php` (Fragment/eigenständig), `404.php`, `page.php` mit `id = 0`, Vorschau, Seitenvorlagen |
| B4 | Sprache/Locale: `html lang`, englische Slugs (`slug_en`), Datumsformate |
| B5 | Paginierung, Query-Parameter (`page` ≤ 0, riesig, nicht numerisch), Suche leer |
| B6 | JavaScript ohne erwartete Elemente, ohne JavaScript (Progressive Enhancement), doppelte Event-Bindung |

### P – Performance
| ID | Prüfpunkt |
|---|---|
| P1 | Datenbankabfragen je Request (N+1, wiederholte Abfragen ohne Request-Cache, OptionStore statt Einzel-`get_option`) |
| P2 | Dateisystem: `theme.json`/Bildmaße mehrfach je Request, `getimagesize()` auf entfernte URLs |
| P3 | Assets: `defer`, Cache-Busting `?v=`, Größe, render-blockierende Ressourcen, ungenutzte Regeln |
| P4 | Bilder: `width`/`height`, `loading="lazy"` unterhalb, `fetchpriority="high"` für LCP, `decoding` |
| P5 | Schriften lokal, `font-display: swap`, keine Drittanbieter |

### Q – Best Practice und Barrierefreiheit
| ID | Prüfpunkt |
|---|---|
| Q1 | `declare(strict_types=1)`, Typdeklarationen, Präfixe, `function_exists`-Schutz |
| Q2 | Dopplungen, toter Code, verwaiste Dateien |
| Q3 | WCAG 2.2 AA (zusätzlich zu 2.1: 2.4.11 Fokus nicht verdeckt bei Sticky-Header, 2.5.8 Zielgröße ≥ 24 px, 3.3.8 Anmeldung ohne kognitiven Test), Kontraste, Tastatur, Landmarks |
| Q4 | HTML-Validität und Semantik (Überschriftenhierarchie, `<time datetime>`, ARIA nur wo nötig) |
| Q5 | Metadaten synchron (`theme.json`, `update.json`, `style.css`, Versionskonstante), `CHANGELOG.md`, `README.md` |

### K – Kompatibilität 365CMS 4.2.0
| ID | Prüfpunkt |
|---|---|
| K1 | Metadaten `tested_up_to` 4.2.0, `requires_cms` 4.0.0, `requires_php` 8.4.1 |
| K2 | Rolle `moderator`: Admin-Einträge nur über `isAdmin()`/Capability, Mitgliederlinks für jede angemeldete Rolle, keine Rollen-String-Vergleiche |
| K3 | Community-Navigation: `/forum` nur bei aktivem Modul (`theme_v4_link_available('/forum')`), kein fester Forum-Link, Plugin `cms-forum` als Rückfall |
| K4 | Community-Token-Brücke wirkt, keine Kollision mit Theme-eigenen `--accent`/`--ink`/`--surface` |
| K5 | Landmark-Vertrag für künftige `community/`-Overrides (Theme mit Header-`<main>`), dokumentiert |
| K6 | Slugs/Anker über `theme_v4_slug()` (Core `CMS\Slug::fromText()`) |
| K7 | Benachrichtigungszähler (falls vorhanden) mit `event_count` |
| K8 | Öffentliche POST-Formulare gegen den Router-`form_guard` |
| K9 | `php TESTS/theme-contract/run.php` (Core `main`) grün, `php -l` aller Dateien |

## 4. Gemeinsame Schicht: Kit 1.1.0

Geplante Erweiterungen in `includes/cms-v4-compat.php` und `css/cms-v4.css` (alle Funktionen weiter über `function_exists()` geschützt, ohne Core-Änderung lauffähig auf 4.0.0–4.2.0):

1. `theme_v4_module_enabled(string $slug, bool $default)`: `CoreModuleService::isModuleEnabled()` mit Request-Cache; unbekannte Module/fehlender Dienst → `$default`.
2. `theme_v4_link_available()`: `/forum` und Unterpfade nur bei aktivem Modul `community` (fail-closed, wie der Core).
3. `theme_v4_slug(string $text)`: `CMS\Slug::fromText()`, sonst gleichwertiger Rückfall (Umlaute, Akzente, `[a-z0-9-]`, 100 Zeichen).
4. `theme_v4_render_module_view()`: Modul `community` aufgenommen (gleiche `<main>`-Behandlung wie Wissensdatenbank).
5. `theme_v4_user_can(string $capability)`: Capability-Prüfung mit `isAdmin()`-Rückfall, für Moderations-/Admin-Einträge.
6. CSS: `--community-*` plus gescopte Rückfallnamen innerhalb `.cms-community` aus `--tv4-*`.
7. Härtungen, die bei der Prüfung des Kits selbst auffallen (Abschnitt „Kit“ im Plan von academy365).

## 5. Werkzeuge und Prüfablauf je Theme

1. **Statischer Scan** (Skript im Sitzungs-Scratchpad, Ergebnis im Plan): Superglobals, SQL, rohe `echo`, Includes mit Variablen, gefährliche Funktionen, externe URLs, JS-Senken, Storage, fehlendes `strict_types`/`ABSPATH`, `php -l`.
2. **Lesen** aller PHP-/JS-Dateien des Themes (nicht nur der Scan-Treffer), Datenfluss jeder Senke.
3. **Fuzz/Render** der Templates gegen Core-Stubs: Array-Parameter (`?q[]=x`, `?page[]=1`), Überlängen, `page=0`/`-1`/`999999`, leere Inhalte, `id = 0`, Vorschau, englische Locale.
4. **Theme-Vertragstest** des Cores und `php -l` (lokal PHP 8.3; keine 8.4-only-Syntax).
5. **Abnahme:** alle Prüfpunkte mit Ergebnis (behoben / kein Befund / akzeptiert mit Begründung / offen), Metadaten und CHANGELOG nachgezogen.

## 6. Status

| Theme | PR | Plan | Audit | Umsetzung |
|---|---|---|---|---|
| academy365 | 1 | [x] | [x] | [x] |
| buildbase | 1 | [x] | [x] | [x] |
| business | 1 | [x] | [x] | [x] |
| feder | 1 | [x] | [x] | [x] |
| kompass | 1 | [x] | [x] | [x] |
| kontor | 2 | [x] | [x] | [x] |
| logilink | 2 | [x] | [x] | [x] |
| medcarepro | 2 | [x] | [x] | [x] |
| newspaper | 2 | [x] | [x] | [x] |
| personalflow | 2 | [x] | [x] | [x] |
| PTC | 3 | [x] | [x] | [x] |
| rundschau | 3 | [x] | [x] | [x] |
| technexus | 3 | [x] | [x] | [x] |

## 7. Gesamtergebnis (2026-11-18)

Alle 13 Themes sind geprüft und umgesetzt; je Theme stehen die Befunde im Abschnitt „Befunde“ des Plans. Versionen: academy365, buildbase, business, PTC 4.1.0; feder, kompass, kontor, logilink, medcarepro, newspaper, personalflow, rundschau, technexus 2.1.0. Alle mit Kit 1.1.0, `tested_up_to` 4.2.0, `requires_cms` 4.0.0.

### Theme-übergreifende Befunde

| Befund | Themes | Schwere |
|---|---|---|
| `/\host` (und teils `//host`) galt in URL-Prüfern als lokaler Pfad → Link auf fremde Adresse (Menüs, Customizer-Links, Logo/Favicon im Kit) | alle 13 + Kit | mittel |
| `javascript://…%0a…` passierte `FILTER_VALIDATE_URL` in der Menüausgabe | academy365 | hoch |
| Geplante Beiträge (`published_at` in der Zukunft) vorzeitig sichtbar, weil nur `status = 'published'` gefiltert wurde | PTC | hoch |
| Roh ausgegebener Admin-Code (Head/Footer) bzw. Customizer-URLs ohne Schemaprüfung | PTC, newspaper | mittel |
| Inhalte ohne JavaScript unsichtbar (`*-reveal { opacity: 0 }`) | logilink, newspaper, personalflow | mittel |
| Fest eingetragene Fremd-/Demo-Daten öffentlich (Partner-Links, Kontaktdaten, fremde Schlagzeilen) | newspaper, kompass | mittel |
| Aktiver Menüeintrag bei Unterverzeichnis-Installation, `mailto:`/`tel:` als extern oder `#` | feder, kompass, kontor, rundschau, logilink, personalflow, technexus | mittel/niedrig |
| Slugs/Anker ohne Akzente (`Café` → `caf`), abweichend von `CMS\Slug` 4.2.0 | academy365, feder, kompass, kontor, rundschau, medcarepro | mittel |
| Customizer-Uploads mit Array-Struktur (`feld[]`) → Warnungen; vorhersagbarer Logo-Name | feder, kompass, kontor, rundschau, PTC, newspaper | niedrig/mittel |
| WCAG 2.2 2.4.11 (Fokus unter festem/Sticky-Header) | academy365, buildbase, business, logilink, medcarepro, newspaper, personalflow, PTC, technexus | mittel |
| `strict_types` fehlte in Vorlagen | logilink, medcarepro, newspaper, personalflow, technexus | niedrig |

### Offene Punkte

- **Laufzeitprüfung:** Browser-, axe- und Lighthouse-Läufe gegen eine echte 4.2.0-Installation stehen aus (es gibt noch kein 4.2.0-Paket). Geprüft wurde statisch, mit Render-/Fuzz-Harness gegen Core-Stubs und mit `TESTS/theme-contract` des Cores.
- **Community-Vorlagen:** Der Core liefert `CMS/views/community/` noch nicht. Die Themes verlinken die Community deshalb noch nicht (fail-closed); Override-Dateien unter `community/` entstehen erst, wenn die Core-Vorlagen und ihre Variablen feststehen. Die Token-Brücke `--community-*` nimmt das Namensschema aus `THEME-UI.md` an und setzt zusätzlich die Rückfallnamen (`--accent`, `--ink`, `--surface` …) im Community-Bereich.
- **medcarepro:** Großschrift/Hochkontrast werden erst nach dem `defer`-Script gesetzt (Aufblitzen), weil die Regeln an `body`-Klassen hängen.
- **rundschau:** Ressort-Badges auf Flächen ohne vollständiges Kontrast-Audit; eine Abfrage je Ressort-Block.
- **PTC/newspaper:** Admin-Customizer laden das Core-Asset `admin.js` ohne `defer`.
- **Bestehende Installationen:** kompass (Demo-Kontaktdaten entfallen) und newspaper (Partner-Leiste nur noch mit gepflegten Links) brauchen nach dem Update ggf. Pflege im Customizer; steht im jeweiligen CHANGELOG.
- **cms-default** (Core-Repository) und **`index.json`** (veraltete Einträge `cms-showcase`, `cms-newspaper`) wie im 4.0/4.1-Protokoll offen.

## 8. Folgeaudit je Theme (ein Pull Request je Theme)

Nach dem Erstaudit wird jedes Theme noch einmal vollständig gegen seinen Plan geprüft: offene und „akzeptierte“ Punkte, Browser-/axe-Prüfung mit [`tools/`](tools/README.md), Kit-Stand. Ergebnisse stehen im Abschnitt „Folgeaudit“ des jeweiligen Plans.

| Theme | Version | Folgeaudit |
|---|---|---|
| academy365 | 4.1.1 | [x] [Abschnitt 8](academy365.md#8-folgeaudit-2026-11-18-umgesetzt-in-411-kit-120) |
| buildbase | 4.1.1 | [x] [Abschnitt 7](buildbase.md#7-folgeaudit-2026-11-18-umgesetzt-in-411-kit-120) |
| business, feder, kompass, kontor, logilink, medcarepro, newspaper, personalflow, PTC, rundschau, technexus | – | [ ] |
