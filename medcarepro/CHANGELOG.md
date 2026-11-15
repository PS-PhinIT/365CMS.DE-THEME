# MedCare Pro – Changelog

## 2.0.0 – 2026-11-15

### Kompatibilität mit 365CMS 4.0.0 und 4.1.0

Setzt 365CMS 4.0.0 und PHP 8.4.1 voraus (`requires_cms`, `tested_up_to: 4.1.0`, `requires_php`).

#### Core-Verträge 4.0.0
- `error.php` wertet `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`, `$error_standalone` und `$error_retry_after` aus; 403/429/503 behalten ihren Status. Die Vorlage ruft `get_header()`/`get_footer()` nicht mehr selbst auf (doppelter Seitenrahmen) und lädt im Fatal-Handler nur noch `includes/cms-v4-compat.php` statt `functions.php`. Eigenständig: vollständiges Dokument mit `style.css` und `css/cms-v4.css`.
- Hauptnavigation löst den Core-Hook `main_nav` aus (eine responsive Navigation → einmal Kontext `desktop`); Menüeinträge zeigen `title`, aktive Einträge tragen `aria-current="page"`.
- `ThemeManager::registerMenuLocation()` auf `init`/`cms_init` entfernt; Menü-Positionen nur über `register_menu_locations`.
- `theme_use_local_fonts()` über den `OptionStore` statt per SQL; Theme-URL über den Theme-Ordner.
- Login/Registrierung (Legacy-Modus): Formulare posten an den Core, der ausschließlich `\CMS\Auth::instance()->login()`/`register()` nutzt und neutrale Meldungen liefert. Neu: Feld `redirect`, Vorbelegung aus `auth_form_old` (nie Passwörter), Login per E-Mail oder Benutzername, Registrierungslinks/-formular nur bei aktivierter Registrierung, Hinweise zur Benutzernamen- und Passwort-Richtlinie, Umschalter Patient/Arzt mit `aria-current` statt Tab-Rollen.
- Logout als CSRF-geschütztes POST-Formular (`theme_v4_logout_form()`) im Header und im Mobilmenü.

#### Vorlagen und Core-Module
- Neu: `contact.php` (Core-Formular, Praxiskontakt aus dem Customizer-Bereich „Kontakt“, Telefon-Fallback Termin-Telefon, Notrufnummer).
- Neu: `knowledgebase/*.php`, `newsletter/*.php`, `includes/cms-v4-compat.php`, `css/cms-v4.css`; Token-Brücke `--tv4-*` (auch für `body.mc-high-contrast`) und Rahmen `.theme-v4-module` in `style.css`.
- Footer, 404, Startseite und Beitragsseite verlinken `/aerzte`, `/termin`, `/fachgebiete`, `/fachgebiet/…`, `/pricing` und Rechtsseiten nur, wenn Route bzw. veröffentlichte Seite existieren; leere Spalten entfallen; Hero-Suche fällt ohne Arztsuche auf `/search` zurück. Neu im Footer: Ratgeber, Newsletter, Kontakt.
- Blog-Pagination auf dem aktuellen Pfad statt immer `/blog`; Datumsangaben ohne TypeError bei ungültigen Werten, `datetime` im ISO-Format.
- Logo nur als lokale URL, Fallback `site_logo`, mit `width`/`height`; Favicon aus `site_favicon`.

#### 4.1.0
- Memberbereich und Newsletter-Widget in der MedCare-Palette über `member_head`; Stylesheet, Customizer-Variablen und Script werden in der Member-Shell nicht geladen.
- `login_page.colors` in `theme.json`; Login-Showcase in `login.php`/`register.php` über `CmsAuthPageService::renderShowcase(…, 'legacy')`.
- Beitrags- und Kartenbilder mit Fokus-Punkt; Vorschau-Links mit `noindex`, ohne JSON-LD, Hinweisleiste.

#### Sicherheit, Datenschutz, Performance
- Keine Google-Fonts-Requests mehr; Font-Stacks enden auf System-Schriften. Die Font-Stacks werden nicht mehr HTML-escaped (`&quot;` im `<style>` machte `--mc-font-*` ungültig).
- Core-SEO über `theme_v4_head_tags()`; entfernt: site-weite Description, `og:url` = Startseite, `robots: index,follow`. Dokumenttitel nach Seite/Modul.
- `cms_csp_runtime_tags(true)` direkt nach `<meta charset>`; Script weiter `defer`, Stylesheet-`preload` entfällt.
- Customizer-Werte im CSS validiert (Farben, geklemmte Zahlen, Auswahlwerte); Copyright-Text escaped (Entities bleiben). URLs aus Menü, Suche und Arztkarte über `mc_safe_url()`.
- Beitragsbild als LCP mit `fetchpriority="high"`, Karten-/Avatarbilder `loading="lazy"` mit `width`/`height`.

#### Barrierefreiheit
- `theme.json`-Standardfarben entsprechen jetzt der Teal-Palette des Stylesheets. Bisher galten die alten Sky-Blau-Werte (Primär `#0ea5e9` mit weißer Schrift 2,8:1, Akzent `#10b981` 2,5:1, Warnung `#f59e0b` 2,2:1, Muted auf Hintergrund 4,5:1 knapp darunter). Akzent `#047857` (5,5:1 mit weißer Schrift, bisher im CSS `#0f9d6a` 3,5:1).
- Fokusrahmen auf dunklen Flächen (Hero, CTA, Footer, Notfallbanner) hellgelb statt Teal (1,9:1 → 11,7:1).
- Desktop-Hauptnavigation nicht mehr per `aria-hidden="true"` vor Screenreadern versteckt; Mobilmenü fokussiert den ersten Eintrag und gibt den Fokus zurück; Kontolinks im Mobilmenü (die Header-Buttons sind mobil ausgeblendet).
- Footer-Überschriften `h2` statt `h3`, Notfallhinweise als `note` statt `alert` bei jedem Seitenaufruf, `html lang` nach Inhaltssprache.
- `supports` ohne `dark-mode`: Das Theme hat keinen Dark Mode (nur einen Hochkontrast-Schalter).

## 1.1.0 – 2026-09-26

### Kompatibilität mit 365CMS 3.4.00 (CSP, Trusted Types, Layout-Hooks)

- Kein doppelter Seitenrahmen mehr: Templates binden `header.php`/`footer.php` nicht mehr selbst ein – der Core rendert beide in `ThemeManager::render()`. `header.php`/`footer.php` sind zusätzlich gegen Doppel-Einbindung geschützt.
- `header.php` lädt die CSP-Runtime des Cores (`cms_csp_runtime_tags()`: DOMPurify + Trusted-Types-Policy) als erstes Script.
- Customizer-CSS (`<style id="…-custom-vars">`) trägt den CSP-Nonce (`theme_csp_nonce_attr()`); die Produktiv-CSP hatte den Block bisher verworfen, Farben/Typografie/Layout aus dem Customizer blieben wirkungslos.
- `footer.php` löst `body_end` vor `</body>` aus (Core-Cookie-Consent, Web-Vitals, PhotoSwipe). Das Navigations-Script wird nur noch einmal geladen (bisher doppelt über `before_footer`, wodurch Toggles sofort wieder schlossen).
- Google Fonts werden nicht mehr geladen, wenn im Core „Schriften lokal einbinden“ (`privacy_use_local_fonts`) aktiv ist (DSGVO).
- `error.php` lädt die Theme-Helfer nach, wenn der Fatal-Handler (`index.php`) die Datei direkt einbindet.
- Manifest: `requires_cms`/`min_cms_version` 3.4.00, `tested_up_to` 3.4.00.

## 1.0.3 – 2026-05-19 Nachtrag

### Abschlussdokumentation v3/PHP 8.4

- `README.md`, `CHANGELOG.md`, `functions.php`, Templates, `partials/doctor-card.php`, `style.css`, `theme.json`, `update.json` und `js/navigation.js` sind als finaler Healthcare-Re-Audit-Stand dokumentiert.
- Nachgetragen sind die Detailänderungen an Arztkarte, Blog-/Such-/Login-/Register-Templates, Notfallbanner, Search-/Mobile-Drawer-A11y, Font-Size-/Kontrast-Toggles und vollständigem Customizer-Mapping.
- Der Update-Feed trägt das Nachtragsdatum vom 19.05.2026, ohne die Version `1.0.3` erneut zu erhöhen.

## 1.0.3 – 2026-05-18

### Re-Audit und medizinische Trust-Identität

- Klinik-Anker-Teal (`#0e5b6b` / `#082f38`) ersetzt generisches SaaS-Sky-Blue; medizinisches Rot (`#b91c1c`) bleibt ausschließlich Notfall- und Warnzuständen vorbehalten.
- Spezialgebiet-Badges besitzen eigene, WCAG-AA-geprüfte Vordergrund-/Hintergrund-Paare.
- `enqueueStyles()` und `enqueueScripts()` liefern versionierte Assets mit Preload/defer; hartcodierte Stylesheet-Links wurden entfernt.
- Customizer-Verkabelung für Header, Footer, Buttons, Layout, Typografie, Accessibility und Spezialgebiet-Farben vollständig nachgezogen.
- Google-Fonts-URL lädt nur tatsächlich gewählte Familien; bei System-Stacks entfällt der externe Request.
- Inline-Styles und Inline-`<style>`-Hacks wurden durch semantische CSS-Klassen und Body-Klassen ersetzt.
- Mobile-Menü und Suchpanel nutzen `aria-controls`, `aria-hidden`, Escape-/Outside-Click-Close und Focus-Restore.
- `mc_tel_sanitize()`, `mc_safe_headline()`, `mc_href()`, `mc_body_class()` und weitere Helper sichern URL-, Headline-, Telefon- und Body-Class-Ausgaben ab.
- `theme.json`, `style.css`, `update.json` und `MEDCAREPRO_THEME_VERSION` sind auf `1.0.3` synchronisiert.

## 1.0.2 – 2026-05-17

- v3-Kompatibilität: Menü-Registrierung zusätzlich über `cms_init` abgesichert.
- Customizer-Key-Mapping in `functions.php` auf die tatsächlichen `theme.json`-Keys korrigiert.
- Google-Fonts-URL, Header-/CTA-Links und Suchpanel-ARIA nachgezogen.
- Sichtbare `:focus-visible`-States und Reduced-Motion-Fallbacks ergänzt.

## 1.0.1 – 2026-03-01

- Helper-Funktionen implementiert, optionaler Notfall-Banner, vier-spaltiger Footer und zusätzliche Healthcare-Templates ergänzt.

## 1.0.0 – 2026-02-21

- Erstveröffentlichung: Healthcare- und Praxis-Theme.
