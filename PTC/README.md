# PTC – Personalvermittlung & Schulungen

**Slug:** `PTC` · **Version:** 4.0.0 · **PHP:** 8.4.1+ · **365CMS:** 4.0.0 – 4.1.0

Branchentheme für Anbieter von **Personalvermittlung**, **Arbeitnehmerüberlassung** und **beruflicher Weiterbildung**. Es richtet sich an Vermittler und Bildungsträger – nicht an ein einzelnes Unternehmen.

## Branche & Zielgruppe

- Personalvermittlung und Zeitarbeit
- Akademien, Qualifizierung, Kurse und Termine
- Dualer Fokus: **Kandidaten** und **Arbeitgeber**

## Design

- **Primärfarbe (Tiefblau-Teal):** Vertrauen, Stabilität
- **Akzent (Wachstumsgrün):** Kompetenz, Entwicklung
- Zentrale CSS Custom Properties (`--color-primary`, `--color-accent`, …) – ein Token-Block im Customizer

## Homepage-Sektionen

1. Hero mit zwei CTAs (Kandidaten / Arbeitgeber)
2. Vermittlungsprozess (Pipeline: Bewerbung → Vorauswahl → Vermittlung → Einstellung)
3. Leistungen (bis zu 12 Karten)
4. Termine / Kurse (Plugin oder manuell)
5. FAQ
6. Kontakt-CTA

## Wichtige Dateien

| Datei | Zweck |
|-------|--------|
| `functions.php` | Singleton, Assets, `outputCustomStyles()`, Hilfsfunktionen |
| `theme.json` | Metadaten + Customizer-Schema |
| `style.css` | Layout & Komponenten |
| `home.php` | Startseite |
| `admin/customizer.php` | Admin-Customizer |
| `contact.php` | Kontaktseite mit Core-Formular (`/contact`), Kontaktdaten aus dem Footer-Customizer |
| `login.php`, `register.php` | Anmeldung/Registrierung im Legacy-Modus (POST an den Core, Login-Showcase) |
| `knowledgebase/*.php`, `newsletter/*.php` | Core-Views der Knowledgebase und des Newsletters im Theme-Rahmen |
| `includes/cms-v4-compat.php`, `css/cms-v4.css` | Gemeinsame 365CMS-4.x-Schicht aller Public Themes (Fehlerseiten-Vertrag, `main_nav`, Standardlinks, SEO/Vorschau, Logout-Formular, Memberbereich, Token-Brücke `--tv4-*`) |

## Anpassung für einen neuen Betreiber

1. Site-Titel und Logo im Customizer setzen
2. Farben unter **Farben** anpassen (Primär + Akzent reichen oft)
3. Texte unter **Startseite**, **Dienstleistungen**, **Footer**
4. Menüs: Hauptnavigation, Footer-Navigation, Footer-Leistungen, Rechtliches (ohne gepflegtes Menü zeigt das Theme Standardeinträge, nur zu vorhandenen Seiten)

Keine firmenspezifischen Daten sind im Code hinterlegt.
