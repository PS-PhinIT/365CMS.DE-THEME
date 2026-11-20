<?php
declare(strict_types=1);

/**
 * Feder – Blog-Theme für 365CMS
 *
 * Lesefokussiertes Autoren- und Essay-Blog: Serifen-Typografie, Papier-Palette
 * mit Bordeaux-Akzent, Hell-/Dunkelmodus, Lesefortschritt und Autorenbox.
 *
 * @package Feder_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('FEDER_THEME_VERSION')) {
    define('FEDER_THEME_VERSION', '2.1.1');
}

// 365CMS 4.x: header.php öffnet <main id="main-content">; Templates und Core-Module geben kein eigenes <main> aus.
if (!defined('THEME_V4_HEADER_HAS_MAIN')) {
    define('THEME_V4_HEADER_HAS_MAIN', true);
}
if (!defined('THEME_V4_MAIN_ID')) {
    define('THEME_V4_MAIN_ID', 'main-content');
}
require_once __DIR__ . '/includes/cms-v4-compat.php';

final class Feder_Theme
{
    public const SLUG = 'feder';

    /**
     * Schriftwahl → [CSS-Stack, Font-Manager-Slug (leer = Systemschrift)].
     *
     * Webfonts liefert ausschließlich die Core-Schriftverwaltung (Filter local_font_slugs); ohne lokale
     * Schriftdatei greift der System-Fallback des Stacks. Keine Requests an Google Fonts.
     *
     * @var array<string, array{0:string,1:string}>
     */
    private const FONTS = [
        'fraunces'         => ['"Fraunces", ui-serif, Georgia, Cambria, "Times New Roman", serif', 'fraunces'],
        'playfair-display' => ['"Playfair Display", ui-serif, Georgia, Cambria, "Times New Roman", serif', 'playfair-display'],
        'literata'         => ['"Literata", ui-serif, Georgia, Cambria, "Times New Roman", serif', 'literata'],
        'source-serif'     => ['"Source Serif 4", ui-serif, Georgia, Cambria, "Times New Roman", serif', 'source-serif-4'],
        'lora'             => ['"Lora", ui-serif, Georgia, Cambria, "Times New Roman", serif', 'lora'],
        'merriweather'     => ['"Merriweather", ui-serif, Georgia, Cambria, "Times New Roman", serif', 'merriweather'],
        'inter'            => ['"Inter", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif', 'inter'],
        'source-sans'      => ['"Source Sans 3", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif', 'source-sans'],
        'system-serif'     => ['ui-serif, Charter, "Bitstream Charter", Cambria, Georgia, serif', ''],
        'system'           => ['system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif', ''],
    ];

    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        \CMS\Hooks::addFilter('register_menu_locations', [$this, 'registerMenuLocations']);
        \CMS\Hooks::addFilter('local_font_slugs', [$this, 'localFontSlugs']);

        // Der Core-Mitgliederbereich (/member) ist eine eigene Shell und löst ebenfalls `head` aus:
        // dort keine Frontend-Assets, nur die Feder-Palette als --member-*-Variablen.
        if (theme_v4_is_member_area()) {
            \CMS\Hooks::addAction('member_head', [$this, 'outputMemberTokens'], 20);
            return;
        }

        \CMS\Hooks::addAction('head', [$this, 'enqueueStyles'], 1);
        \CMS\Hooks::addAction('head', [$this, 'outputColorSchemeBootstrap'], 2);
        \CMS\Hooks::addAction('head', [$this, 'outputCustomHeaderCode'], 8);
        \CMS\Hooks::addAction('head', [$this, 'outputMetaTags'], 10);
        \CMS\Hooks::addAction('head', [$this, 'outputCustomStyles'], 16);
        \CMS\Hooks::addAction('before_footer', [$this, 'enqueueScripts'], 99);
    }

    // ── Assets ───────────────────────────────────────────────────────────────

    public function enqueueStyles(): void
    {
        $version = rawurlencode(FEDER_THEME_VERSION);
        echo '<link rel="stylesheet" href="' . feder_e($this->themeUrl() . '/style.css?v=' . $version) . '">' . "\n";
        // Gemeinsame 4.x-Integrationsstile (Downloads, Feeds, Formulare, KB, Newsletter) nach dem Theme-Stylesheet.
        echo '<link rel="stylesheet" href="' . feder_e($this->themeUrl() . '/css/cms-v4.css?v=' . $version) . '">' . "\n";
    }

    public function enqueueScripts(): void
    {
        $src = $this->themeUrl() . '/js/theme.js?v=' . rawurlencode(FEDER_THEME_VERSION);
        echo '<script src="' . feder_e($src) . '" defer></script>' . "\n";
    }

    /**
     * Setzt das gespeicherte Farbschema vor dem ersten Rendern (kein Aufblitzen).
     * Inline-Script mit CSP-Nonce, ohne DOM-Sinks (Trusted-Types-konform).
     */
    public function outputColorSchemeBootstrap(): void
    {
        if (!feder_flag('header', 'show_darkmode_toggle', true)) {
            return;
        }

        echo '<script' . theme_csp_nonce_attr() . '>(function(){try{var s=localStorage.getItem("feder-color-scheme");'
            . 'if(s==="dark"||s==="light"){document.documentElement.setAttribute("data-theme",s);}}catch(e){}})();</script>' . "\n";
    }

    /**
     * Lokale Schriften der Core-Schriftverwaltung für die gewählten Schriften (keine Google-Fonts-Requests).
     *
     * @param mixed $slugs
     * @return array<int, string>
     */
    public function localFontSlugs(mixed $slugs): array
    {
        $slugs = is_array($slugs) ? $slugs : [];
        foreach ($this->selectedFontKeys() as $key) {
            $slug = self::FONTS[$key][1] ?? '';
            if ($slug !== '') {
                $slugs[] = $slug;
            }
        }

        return array_values(array_unique(array_map('strval', $slugs)));
    }

    /**
     * Eigener Header-Code aus SEO → Technik (Altbestand; laut Core weiterhin in jedem <head>).
     * Beschreibung, Robots, Canonical, Open Graph und JSON-LD gibt header.php über theme_v4_head_tags() aus.
     */
    public function outputCustomHeaderCode(): void
    {
        try {
            if (class_exists('\\CMS\\Services\\SEOService')) {
                $custom = trim((string) \CMS\Services\SEOService::getInstance()->getCustomHeaderCode());
                if ($custom !== '') {
                    echo $custom . "\n"; // Vom Administrator gepflegter Head-Code, bewusst unverändert.
                }
            }
        } catch (\Throwable) {
        }
    }

    /**
     * Memberbereich (inkl. Newsletter-Widget) in der Feder-Palette.
     *
     * Bewusst nur die helle Palette: Die Tabler-Shell des Cores hat keinen Dunkelmodus (ihre light-dark()-Farben
     * bleiben ohne `color-scheme: dark` hell). Dunkle --member-*-Flächen ergäben dort z. B. `.text-secondary`
     * mit rund 3,6:1 – unter WCAG AA. Den Fokusrahmen leitet der Core aus --member-primary ab.
     */
    public function outputMemberTokens(): void
    {
        $c = static fn(string $key, string $default): string => feder_css_color((string) feder_setting('colors', $key, $default), $default);

        echo theme_v4_member_tokens_style([
            'primary'    => $c('accent_color', '#8e2c3a'),
            'accent'     => $c('accent_hover', '#6e1f2b'),
            'bg'         => $c('paper_color', '#fbf8f3'),
            'card_bg'    => $c('surface_color', '#ffffff'),
            'text'       => $c('text_color', '#322b26'),
            'text_muted' => $c('muted_color', '#6d6258'),
            'border'     => $c('border_color', '#e6ddd0'),
            'radius'     => feder_css_number(feder_setting('layout', 'border_radius', 6), 6, 0, 18) . 'px',
            'font'       => $this->fontStack((string) feder_setting('typography', 'font_ui', 'inter'), 'inter'),
        ]);
    }

    public function outputMetaTags(): void
    {
        $scheme = feder_color_scheme();
        $paper = feder_css_color((string) feder_setting('colors', 'paper_color', '#fbf8f3'), '#fbf8f3');
        $darkPaper = feder_css_color((string) feder_setting('colors', 'dark_paper_color', '#15120f'), '#15120f');

        echo '<meta name="color-scheme" content="' . ($scheme === 'auto' ? 'light dark' : $scheme) . '">' . "\n";
        if ($scheme === 'dark') {
            echo '<meta name="theme-color" content="' . feder_e($darkPaper) . '">' . "\n";
        } else {
            echo '<meta name="theme-color" content="' . feder_e($paper) . '"' . ($scheme === 'auto' ? ' media="(prefers-color-scheme: light)"' : '') . '>' . "\n";
            if ($scheme === 'auto') {
                echo '<meta name="theme-color" content="' . feder_e($darkPaper) . '" media="(prefers-color-scheme: dark)">' . "\n";
            }
        }
    }

    public function outputCustomStyles(): void
    {
        $c = static fn(string $key, string $default): string => feder_css_color((string) feder_setting('colors', $key, $default), $default);
        $n = static fn(string $group, string $key, float $default, float $min, float $max): string => feder_css_number(feder_setting($group, $key, $default), $default, $min, $max);

        $light = [
            '--fdr-accent'  => $c('accent_color', '#8e2c3a'),
            '--fdr-accent-hover' => $c('accent_hover', '#6e1f2b'),
            '--fdr-ink'     => $c('ink_color', '#211c19'),
            '--fdr-text'    => $c('text_color', '#322b26'),
            '--fdr-muted'   => $c('muted_color', '#6d6258'),
            '--fdr-paper'   => $c('paper_color', '#fbf8f3'),
            '--fdr-surface' => $c('surface_color', '#ffffff'),
            '--fdr-border'  => $c('border_color', '#e6ddd0'),
        ];
        $dark = [
            '--fdr-accent'  => $c('dark_accent_color', '#e8909b'),
            '--fdr-accent-hover' => $c('dark_accent_color', '#e8909b'),
            '--fdr-ink'     => $c('dark_text_color', '#ece4d8'),
            '--fdr-text'    => $c('dark_text_color', '#ece4d8'),
            '--fdr-muted'   => $c('dark_muted_color', '#a99d8f'),
            '--fdr-paper'   => $c('dark_paper_color', '#15120f'),
            '--fdr-surface' => $c('dark_surface_color', '#1f1a16'),
            '--fdr-border'  => $c('dark_border_color', '#352d27'),
        ];

        $root = $light + [
            '--fdr-font-heading' => $this->fontStack((string) feder_setting('typography', 'font_heading', 'fraunces'), 'fraunces'),
            '--fdr-font-body'    => $this->fontStack((string) feder_setting('typography', 'font_body', 'literata'), 'literata'),
            '--fdr-font-ui'      => $this->fontStack((string) feder_setting('typography', 'font_ui', 'inter'), 'inter'),
            '--fdr-size-body'    => $n('typography', 'font_size_base', 19, 16, 22) . 'px',
            '--fdr-leading'      => $n('typography', 'line_height_base', 1.72, 1.4, 2.0),
            '--fdr-measure'      => $n('layout', 'content_width', 690, 580, 820) . 'px',
            '--fdr-wide'         => $n('layout', 'wide_width', 1120, 900, 1320) . 'px',
            '--fdr-radius'       => $n('layout', 'border_radius', 6, 0, 18) . 'px',
            // Core-Variablen (editorjs-content.css) nach dem Core-Customizer-Block erneut an die Palette binden.
            '--primary-color'   => 'var(--fdr-accent)',
            '--accent'          => 'var(--fdr-accent)',
            '--border-color'    => 'var(--fdr-border)',
            '--bg-primary'      => 'var(--fdr-paper)',
            '--bg-secondary'    => 'var(--fdr-surface)',
            '--text-primary'    => 'var(--fdr-text)',
            '--text-secondary'  => 'var(--fdr-muted)',
        ];

        $declarations = static function (array $vars): string {
            $out = '';
            foreach ($vars as $name => $value) {
                $out .= $name . ':' . $value . ';';
            }

            return $out;
        };

        $scheme = feder_color_scheme();
        $css = ':root{' . $declarations($root) . '}';
        $darkCss = $declarations($dark) . 'color-scheme:dark;';
        if ($scheme === 'dark') {
            $css .= ':root{' . $darkCss . '}';
            $css .= ':root[data-theme="light"]{' . $declarations($light) . 'color-scheme:light;}';
        } else {
            $css .= ':root[data-theme="dark"]{' . $darkCss . '}';
            if ($scheme === 'auto') {
                $css .= '@media (prefers-color-scheme: dark){:root:not([data-theme="light"]){' . $darkCss . '}}';
            }
        }

        if (feder_flag('header', 'enable_sticky_header', false)) {
            $css .= '.fdr-header{position:sticky;top:0;}';
        }

        $css .= feder_sanitize_custom_css((string) feder_setting('advanced', 'custom_css', ''));

        echo '<style id="feder-custom-vars"' . theme_csp_nonce_attr() . '>' . $css . '</style>' . "\n";
    }

    // ── Menüs ────────────────────────────────────────────────────────────────

    /**
     * @param mixed $locations
     * @return array<int, array{slug:string,label:string}>
     */
    public function registerMenuLocations(mixed $locations): array
    {
        $locations = is_array($locations) ? $locations : [];
        $existing = array_column($locations, 'slug');
        foreach ([
            'primary'      => 'Hauptnavigation (Header)',
            'footer-nav'   => 'Footer-Navigation',
            'footer-legal' => 'Rechtliche Links (Footer)',
        ] as $slug => $label) {
            if (!in_array($slug, $existing, true)) {
                $locations[] = ['slug' => $slug, 'label' => $label];
            }
        }

        return $locations;
    }

    // ── Konfiguration ────────────────────────────────────────────────────────

    /** Liest Werte aus dem settings-Block der theme.json. */
    public function getConfig(string $key, mixed $default = ''): mixed
    {
        static $config = null;
        if ($config === null) {
            $config = [];
            $file = __DIR__ . '/theme.json';
            if (is_file($file)) {
                $decoded = json_decode((string) file_get_contents($file), true);
                $config = is_array($decoded['settings'] ?? null) ? $decoded['settings'] : [];
            }
        }

        return $config[$key] ?? $default;
    }

    public function themeUrl(): string
    {
        try {
            $url = (string) \CMS\ThemeManager::instance()->getThemeUrl();
        } catch (\Throwable) {
            $url = rtrim((string) SITE_URL, '/') . '/themes/' . self::SLUG;
        }

        return rtrim($url, '/');
    }

    private function fontStack(string $choice, string $fallback): string
    {
        return self::FONTS[$choice][0] ?? self::FONTS[$fallback][0];
    }

    /** @return array<int, string> */
    private function selectedFontKeys(): array
    {
        $keys = [];
        foreach ([['font_heading', 'fraunces'], ['font_body', 'literata'], ['font_ui', 'inter']] as [$setting, $default]) {
            $choice = (string) feder_setting('typography', $setting, $default);
            $keys[] = isset(self::FONTS[$choice]) ? $choice : $default;
        }

        return array_values(array_unique($keys));
    }
}

Feder_Theme::instance();

// ── Einstellungen & Ausgabe-Helfer ─────────────────────────────────────────────

if (!function_exists('feder_setting')) {
    /** Customizer-Wert mit Fallback auf theme.json-Default bzw. $default. */
    function feder_setting(string $group, string $key, mixed $default = null): mixed
    {
        try {
            return \CMS\Services\ThemeCustomizer::instance()->get($group, $key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (!function_exists('feder_flag')) {
    function feder_flag(string $group, string $key, bool $default): bool
    {
        $value = feder_setting($group, $key, $default);
        if (is_bool($value)) {
            return $value;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $parsed ?? $default;
    }
}

if (!function_exists('feder_text')) {
    function feder_text(string $group, string $key, string $default = ''): string
    {
        $value = feder_setting($group, $key, $default);

        return is_scalar($value) ? trim((string) $value) : $default;
    }
}

if (!function_exists('feder_text_t')) {
    /**
     * Customizer-Text mit zweisprachigem Standard: Ist der Wert noch der deutsche Standardtext (oder nie
     * gespeichert), erscheint auf englischen Seiten (/en/…) der englische Text; eigene Texte und bewusst
     * geleerte Felder bleiben unverändert.
     */
    function feder_text_t(string $group, string $key, string $de, string $en): string
    {
        $value = feder_text($group, $key, $de);

        return $value === $de ? feder_t($de, $en) : $value;
    }
}

if (!function_exists('feder_e')) {
    function feder_e(mixed $value): string
    {
        return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('feder_color_scheme')) {
    function feder_color_scheme(): string
    {
        $scheme = (string) feder_setting('header', 'color_scheme', 'auto');

        return in_array($scheme, ['auto', 'light', 'dark'], true) ? $scheme : 'auto';
    }
}

if (!function_exists('feder_css_color')) {
    function feder_css_color(string $value, string $fallback): string
    {
        $candidate = trim($value);
        if (preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $candidate) === 1) {
            return $candidate;
        }
        if (preg_match('/^(?:rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/]+\)$/i', $candidate) === 1) {
            return $candidate;
        }

        return $fallback;
    }
}

if (!function_exists('feder_css_number')) {
    function feder_css_number(mixed $value, float $default, float $min, float $max): string
    {
        $number = is_numeric($value) ? (float) $value : $default;
        $number = max($min, min($max, $number));

        return rtrim(rtrim(number_format($number, 3, '.', ''), '0'), '.');
    }
}

if (!function_exists('feder_sanitize_custom_css')) {
    function feder_sanitize_custom_css(string $raw): string
    {
        $css = trim($raw);
        if ($css === '') {
            return '';
        }

        $css = preg_replace('#<\s*/?\s*style\b[^>]*>#i', '', $css) ?? '';
        $css = preg_replace('/@import\b[^;]*;?/i', '', $css) ?? '';
        $css = preg_replace('/expression\s*\([^)]*\)/i', '', $css) ?? '';
        $css = preg_replace('/url\s*\(\s*[\'"]?\s*(?:java|vb)script:[^)]*\)/i', '', $css) ?? '';
        $css = preg_replace('/\b(?:behavior|-moz-binding)\s*:[^;}]+;?/i', '', $css) ?? '';

        return str_replace('</', '<\/', $css);
    }
}

if (!function_exists('feder_safe_url')) {
    /**
     * Lässt nur sichere Ziele zu: relative Pfade, Anker, http(s), mailto: und tel:.
     */
    function feder_safe_url(string $url, string $fallback = ''): string
    {
        $candidate = trim($url);
        // `//host` und `/\host` (Browser werten „\“ wie „/“) sind fremde, protokollrelative Adressen.
        if ($candidate === '' || preg_match('/[\x00-\x1F\x7F]/', $candidate) === 1 || str_starts_with($candidate, '//') || str_contains($candidate, '\\')) {
            return $fallback;
        }
        if (str_starts_with($candidate, '/') || str_starts_with($candidate, '?')) {
            return $candidate;
        }
        if (str_starts_with($candidate, '#')) {
            return preg_match('/^#[A-Za-z][A-Za-z0-9_:.-]*$/', $candidate) === 1 ? $candidate : $fallback;
        }
        if (preg_match('/^mailto:[^\s@<>"]+@[^\s@<>"]+\.[^\s@<>"]+$/i', $candidate) === 1) {
            return $candidate;
        }
        if (preg_match('/^tel:\+?[0-9][0-9\s().\/-]{2,31}$/i', $candidate) === 1) {
            return $candidate;
        }

        $scheme = strtolower((string) parse_url($candidate, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https'], true) && filter_var($candidate, FILTER_VALIDATE_URL) !== false) {
            return $candidate;
        }

        return $fallback;
    }
}

if (!function_exists('feder_url')) {
    /** Baut aus relativen Pfaden absolute Site-URLs; externe/Spezial-URLs werden geprüft durchgereicht. */
    function feder_url(string $target = '/'): string
    {
        $base = rtrim((string) SITE_URL, '/');
        $safe = feder_safe_url($target, '');
        if ($safe === '') {
            return $base . '/';
        }
        if (str_starts_with($safe, '/')) {
            return $base . $safe;
        }
        if (str_starts_with($safe, '#') || str_starts_with($safe, '?')) {
            return $base . '/' . $safe;
        }

        return $safe;
    }
}

if (!function_exists('feder_media_url')) {
    /** Normalisiert Medienreferenzen (Uploads, Media-Delivery) zu sicheren, absoluten URLs. */
    function feder_media_url(mixed $reference): string
    {
        $url = is_scalar($reference) ? trim((string) $reference) : '';
        if ($url === '') {
            return '';
        }

        try {
            if (class_exists('\\CMS\\Services\\MediaDeliveryService')) {
                $url = (string) \CMS\Services\MediaDeliveryService::getInstance()->normalizeUrl($url, true);
            }
        } catch (\Throwable) {
        }

        if (!str_starts_with($url, '/') && !preg_match('#^https?://#i', $url)) {
            $url = '/' . ltrim($url, '/');
        }

        $safe = feder_safe_url($url, '');
        if ($safe === '' || str_starts_with($safe, '#') || str_starts_with($safe, 'mailto:') || str_starts_with($safe, 'tel:')) {
            return '';
        }

        return str_starts_with($safe, '/') ? rtrim((string) SITE_URL, '/') . $safe : $safe;
    }
}

if (!function_exists('feder_site_title')) {
    function feder_site_title(): string
    {
        try {
            $title = trim((string) \CMS\ThemeManager::instance()->getSiteTitle());
        } catch (\Throwable) {
            $title = '';
        }

        return $title !== '' ? $title : (defined('SITE_NAME') ? (string) SITE_NAME : '365CMS');
    }
}

if (!function_exists('feder_site_description')) {
    function feder_site_description(): string
    {
        try {
            return trim((string) \CMS\ThemeManager::instance()->getSiteDescription());
        } catch (\Throwable) {
            return '';
        }
    }
}

if (!function_exists('feder_request_locale')) {
    function feder_request_locale(): string
    {
        try {
            $path = (string) (parse_url(theme_v4_site_url(theme_v4_request_path()), PHP_URL_PATH) ?? '/'); // normalisiert statt REQUEST_URI
            $context = \CMS\Services\ContentLocalizationService::getInstance()->resolveRequestContext($path);
            $locale = strtolower((string) ($context['locale'] ?? 'de'));

            return preg_match('/^[a-z]{2}(?:-[a-z]{2})?$/', $locale) === 1 ? $locale : 'de';
        } catch (\Throwable) {
            return 'de';
        }
    }
}

if (!function_exists('feder_initials')) {
    function feder_initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $initials = '';
        foreach (array_slice(array_values(array_filter($parts)), 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
        }

        return $initials !== '' ? $initials : '•';
    }
}

if (!function_exists('feder_format_date')) {
    /** Deutsches Datum ohne intl-Abhängigkeit: 'long' = 27. September 2026, 'short' = 27.09.2026, 'day' = 27. Sep. */
    function feder_format_date(mixed $value, string $format = 'long'): string
    {
        $ts = is_string($value) && trim($value) !== '' ? strtotime($value) : false;
        if ($ts === false) {
            return '';
        }

        $month = (int) date('n', $ts) - 1;

        // Englische Seiten (/en/…): Monatsnamen und Reihenfolge englisch (Nov 2 · November 2, 2026 · 2026-11-02).
        if (theme_v4_locale() === 'en') {
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            return match ($format) {
                'short' => date('Y-m-d', $ts),
                'day'   => date('M j', $ts),
                'year'  => date('Y', $ts),
                'iso'   => date('c', $ts),
                default => $months[$month] . ' ' . date('j, Y', $ts),
            };
        }

        $months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
        $short = ['Jan.', 'Feb.', 'März', 'Apr.', 'Mai', 'Juni', 'Juli', 'Aug.', 'Sep.', 'Okt.', 'Nov.', 'Dez.'];

        return match ($format) {
            'short' => date('d.m.Y', $ts),
            'day'   => date('j', $ts) . '. ' . $short[$month],
            'year'  => date('Y', $ts),
            'iso'   => date('c', $ts),
            default => date('j', $ts) . '. ' . $months[$month] . ' ' . date('Y', $ts),
        };
    }
}

if (!function_exists('feder_plain_text')) {
    /**
     * Klartext aus gespeichertem Inhalt: Editor.js-JSON (Rohdaten in Listen) oder gerendertes HTML.
     */
    function feder_plain_text(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        if (str_starts_with($content, '{')) {
            $decoded = json_decode($content, true);
            if (is_array($decoded) && is_array($decoded['blocks'] ?? null)) {
                $parts = [];
                $collect = static function (mixed $value) use (&$collect, &$parts): void {
                    if (is_string($value)) {
                        $parts[] = $value;
                    } elseif (is_array($value)) {
                        foreach ($value as $key => $item) {
                            if (in_array($key, ['id', 'type', 'url', 'file', 'style', 'level', 'alignment', 'tunes'], true)) {
                                continue;
                            }
                            $collect($item);
                        }
                    }
                };
                foreach ($decoded['blocks'] as $block) {
                    if (is_array($block)) {
                        $collect($block['data'] ?? []);
                    }
                }
                $content = implode(' ', $parts);
            }
        }

        // Script-/Style-Blöcke (z. B. das Inhaltsverzeichnis des Cores) vor strip_tags() entfernen,
        // sonst verschluckt der Tag-Parser bei Vergleichsoperatoren im JavaScript ganze Absätze.
        $content = preg_replace('#<(script|style|template|noscript)\b[^>]*>.*?</\1\s*>#is', ' ', $content) ?? $content;
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', $content)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }
}

if (!function_exists('feder_reading_time')) {
    function feder_reading_time(string $content): int
    {
        $text = feder_plain_text($content);
        if ($text === '') {
            return 1;
        }
        $words = count(preg_split('/\s+/u', $text) ?: []);

        return max(1, (int) ceil($words / 220));
    }
}

if (!function_exists('feder_excerpt')) {
    /** Teasertext: Auszug → Meta-Description → optional Inhaltsanfang. */
    function feder_excerpt(object|array $post, int $length = 220, bool $allowContent = true): string
    {
        $data = is_array($post) ? $post : get_object_vars($post);
        $text = trim(strip_tags((string) ($data['excerpt'] ?? '')));
        if ($text === '') {
            $text = trim(strip_tags((string) ($data['meta_description'] ?? '')));
        }
        if ($text === '' && $allowContent) {
            $text = feder_plain_text((string) ($data['content'] ?? ''));
        }
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (mb_strlen($text, 'UTF-8') > $length) {
            $text = rtrim(mb_substr($text, 0, $length, 'UTF-8'));
            $text = preg_replace('/\s+\S*$/u', '', $text) ?? $text;
            $text .= ' …';
        }

        return $text;
    }
}

// ── URLs ───────────────────────────────────────────────────────────────────────

if (!function_exists('feder_post_link')) {
    function feder_post_link(object|array $post): string
    {
        return feder_safe_url(theme_post_url($post), feder_url('/blog'));
    }
}

if (!function_exists('feder_archive_url')) {
    function feder_archive_url(string $type, string $slug = ''): string
    {
        try {
            if (function_exists('cms_get_archive_url')) {
                return (string) cms_get_archive_url($type, $slug);
            }
        } catch (\Throwable) {
        }

        $base = $type === 'category' ? 'kategorie' : 'tag';

        return feder_url('/' . $base . ($slug !== '' ? '/' . rawurlencode($slug) : ''));
    }
}

if (!function_exists('feder_author_url')) {
    function feder_author_url(int $authorId): string
    {
        if ($authorId <= 0) {
            return '';
        }

        try {
            if (class_exists('\\CMS\\Services\\MemberService')) {
                return feder_url(\CMS\Services\MemberService::getInstance()->buildPublicAuthorPath($authorId));
            }
        } catch (\Throwable) {
        }

        return feder_url('/author/user-' . $authorId);
    }
}

if (!function_exists('feder_search_url')) {
    function feder_search_url(string $query = ''): string
    {
        return feder_url('/search') . ($query !== '' ? '?q=' . rawurlencode($query) : '');
    }
}

if (!function_exists('feder_feed_url')) {
    function feder_feed_url(): string
    {
        return feder_url('/feed');
    }
}

// ── Daten ──────────────────────────────────────────────────────────────────────

if (!function_exists('feder_get_posts')) {
    /**
     * Veröffentlichte Beiträge für Startseite, Seitenleisten und „Weiterlesen“.
     *
     * @param array{limit?:int,offset?:int,exclude?:array<int,int>,category_id?:int,order?:string,before?:string,after?:string} $args
     * @return array<int, object>
     */
    function feder_get_posts(array $args = []): array
    {
        $limit = max(1, min(50, (int) ($args['limit'] ?? 6)));
        $offset = max(0, (int) ($args['offset'] ?? 0));

        try {
            $db = \CMS\Database::instance();
            $prefix = $db->getPrefix();
            $where = [cms_post_publication_where('p')];
            $params = [];

            $exclude = array_values(array_filter(array_map('intval', (array) ($args['exclude'] ?? [])), static fn(int $id): bool => $id > 0));
            if ($exclude !== []) {
                $where[] = 'p.id NOT IN (' . implode(',', array_fill(0, count($exclude), '?')) . ')';
                array_push($params, ...$exclude);
            }

            $categoryId = (int) ($args['category_id'] ?? 0);
            if ($categoryId > 0) {
                $where[] = '(p.category_id = ? OR EXISTS (SELECT 1 FROM ' . $prefix . 'post_category_rel pcr WHERE pcr.post_id = p.id AND pcr.category_id = ?))';
                $params[] = $categoryId;
                $params[] = $categoryId;
            }

            if (!empty($args['before'])) {
                $where[] = 'COALESCE(p.published_at, p.created_at) < ?';
                $params[] = (string) $args['before'];
            }
            if (!empty($args['after'])) {
                $where[] = 'COALESCE(p.published_at, p.created_at) > ?';
                $params[] = (string) $args['after'];
            }

            $order = match ((string) ($args['order'] ?? 'newest')) {
                'oldest'  => 'COALESCE(p.published_at, p.created_at) ASC',
                'popular' => 'p.views DESC, COALESCE(p.published_at, p.created_at) DESC',
                default   => 'COALESCE(p.published_at, p.created_at) DESC',
            };

            // Der volle Inhalt wird nur für die Lesezeit gebraucht; Vor/Zurück-Links laden ihn nicht.
            $contentColumn = ($args['with_content'] ?? true) === false ? "'' AS content" : 'p.content';
            $rows = $db->get_results(
                "SELECT p.id, p.title, p.slug, p.slug_en, p.excerpt, p.meta_description, p.featured_image, p.published_at,
                        p.created_at, p.updated_at, p.category_id, p.author_id, p.views, {$contentColumn},
                        c.name AS category_name, c.slug AS category_slug,
                        COALESCE(NULLIF(p.author_display_name, ''), NULLIF(u.display_name, ''), NULLIF(u.username, ''), 'Redaktion') AS author_name
                 FROM {$prefix}posts p
                 LEFT JOIN {$prefix}post_categories c ON c.id = p.category_id
                 LEFT JOIN {$prefix}users u ON u.id = p.author_id
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY {$order}
                 LIMIT {$limit} OFFSET {$offset}",
                $params
            );

            return is_array($rows) ? $rows : [];
        } catch (\Throwable) {
            return [];
        }
    }
}

if (!function_exists('feder_count_posts')) {
    function feder_count_posts(): int
    {
        try {
            $db = \CMS\Database::instance();

            return (int) $db->get_var('SELECT COUNT(*) FROM ' . $db->getPrefix() . 'posts p WHERE ' . cms_post_publication_where('p'));
        } catch (\Throwable) {
            return 0;
        }
    }
}

if (!function_exists('feder_get_categories')) {
    /**
     * Kategorien mit Anzahl veröffentlichter Beiträge (nur Kategorien mit Beiträgen).
     *
     * @return array<int, array{id:int,name:string,slug:string,description:string,count:int,url:string}>
     */
    function feder_get_categories(int $limit = 0): array
    {
        // Eine Abfrage je Request (Startseite, Suche und Fallback-Übersicht teilen das Ergebnis):
        // JOIN + GROUP BY statt einer Zählabfrage je Kategorie; nur veröffentlichte Beiträge (4.1.0: `pending` zählt nicht).
        static $rows = null;
        if ($rows === null) {
            try {
                $db = \CMS\Database::instance();
                $prefix = $db->getPrefix();
                $rows = $db->get_results(
                    "SELECT c.id, c.name, c.slug, c.description, c.sort_order, COUNT(p.id) AS post_count
                     FROM {$prefix}post_categories c
                     INNER JOIN {$prefix}posts p ON p.category_id = c.id AND " . cms_post_publication_where('p') . "
                     GROUP BY c.id, c.name, c.slug, c.description, c.sort_order
                     ORDER BY c.sort_order ASC, c.name ASC",
                    []
                ) ?: [];
            } catch (\Throwable) {
                $rows = [];
            }
        }

        $items = [];
        foreach ($rows as $row) {
            $count = (int) ($row->post_count ?? 0);
            $slug = trim((string) ($row->slug ?? ''));
            if ($count <= 0 || $slug === '') {
                continue;
            }
            $items[] = [
                'id' => (int) ($row->id ?? 0),
                'name' => trim((string) ($row->name ?? '')),
                'slug' => $slug,
                'description' => trim((string) ($row->description ?? '')),
                'count' => $count,
                'url' => feder_archive_url('category', $slug),
            ];
        }

        return $limit > 0 ? array_slice($items, 0, $limit) : $items;
    }
}

if (!function_exists('feder_post_tags')) {
    /** @return array<int, array{name:string,url:string}> */
    function feder_post_tags(object|array $post): array
    {
        $data = is_array($post) ? $post : get_object_vars($post);
        $items = is_array($data['tag_items'] ?? null) ? $data['tag_items'] : [];
        if ($items === []) {
            foreach (array_filter(array_map('trim', explode(',', (string) ($data['tags'] ?? '')))) as $name) {
                $items[] = ['name' => $name, 'slug' => ''];
            }
        }

        $tags = [];
        foreach ($items as $item) {
            $name = trim((string) (is_array($item) ? ($item['name'] ?? '') : $item));
            if ($name === '') {
                continue;
            }
            $slug = trim((string) (is_array($item) ? ($item['slug'] ?? '') : ''));
            if ($slug === '') {
                $slug = theme_v4_slug($name); // wie CMS\Slug::fromText() (4.2.0), auch für Akzente
            }
            $tags[] = ['name' => $name, 'url' => $slug !== '' ? feder_archive_url('tag', $slug) : feder_search_url($name)];
        }

        return $tags;
    }
}

if (!function_exists('feder_adjacent_posts')) {
    /** @return array{prev:?object,next:?object} */
    function feder_adjacent_posts(object $post): array
    {
        $date = (string) ($post->published_at ?? $post->created_at ?? '');
        $id = (int) ($post->id ?? 0);
        if ($date === '' || $id <= 0) {
            return ['prev' => null, 'next' => null];
        }

        $prev = feder_get_posts(['limit' => 1, 'before' => $date, 'exclude' => [$id], 'with_content' => false]);
        $next = feder_get_posts(['limit' => 1, 'after' => $date, 'exclude' => [$id], 'order' => 'oldest', 'with_content' => false]);

        return ['prev' => $prev[0] ?? null, 'next' => $next[0] ?? null];
    }
}

if (!function_exists('feder_related_posts')) {
    /** @return array<int, object> */
    function feder_related_posts(object $post, int $limit = 3): array
    {
        $id = (int) ($post->id ?? 0);
        $related = feder_get_posts(['limit' => $limit, 'category_id' => (int) ($post->category_id ?? 0), 'exclude' => [$id]]);
        if (count($related) < $limit) {
            $exclude = array_merge([$id], array_map(static fn(object $p): int => (int) ($p->id ?? 0), $related));
            $related = array_merge($related, feder_get_posts(['limit' => $limit - count($related), 'exclude' => $exclude]));
        }

        return $related;
    }
}

// ── Komponenten ────────────────────────────────────────────────────────────────

if (!function_exists('feder_nav_menu')) {
    /**
     * Gibt ein Menü als Liste aus. Ohne gepflegtes Menü greift $fallback (Label → URL); Standardlinks
     * erscheinen nur, wenn ihr Ziel als Route oder veröffentlichte Seite existiert (365CMS 4.0.0).
     *
     * Mit `$mainNavContext` (`desktop`/`mobile`) folgen direkt nach den Menüeinträgen die Einträge der
     * Core-Module (Hook `main_nav`: Downloads, Knowledgebase, Newsletter) – auch bei leerem Menü.
     *
     * @param array<int, array{label:string,url:string}> $fallback
     */
    function feder_nav_menu(string $location, string $class = '', array $fallback = [], string $mainNavContext = ''): void
    {
        try {
            $items = \CMS\ThemeManager::instance()->getMenu($location);
        } catch (\Throwable) {
            $items = [];
        }
        if (!is_array($items) || $items === []) {
            $items = theme_v4_filter_links($fallback);
        }

        $coreItems = '';
        if ($mainNavContext !== '') {
            ob_start();
            theme_v4_main_nav($mainNavContext, ['wrap' => 'li', 'active_item_class' => 'is-active']);
            $coreItems = (string) ob_get_clean();
        }
        if ($items === [] && $coreItems === '') {
            return;
        }

        echo '<ul' . ($class !== '' ? ' class="' . feder_e($class) . '"' : '') . '>';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? $item['title'] ?? ''));
            $rawUrl = (string) ($item['url'] ?? '#');
            if ($label === '') {
                continue;
            }

            $href = feder_url($rawUrl);
            $isExternal = preg_match('#^https?://#i', feder_safe_url($rawUrl, '')) === 1
                && parse_url($href, PHP_URL_HOST) !== parse_url((string) SITE_URL, PHP_URL_HOST);
            // Kit-Helfer: gleicher Pfad oder Unterpfad, Unterverzeichnis-Installation berücksichtigt (vorher wurde
            // der Anfragepfad mit, der Menüpfad ohne Unterverzeichnis verglichen – dort war nie ein Eintrag aktiv).
            $isActive = !$isExternal && !str_contains($rawUrl, '#') && preg_match('/^(?:mailto|tel):/i', $href) !== 1
                && theme_v4_is_current_path($href);

            $attrs = $isActive ? ' aria-current="page"' : '';
            if (($item['target'] ?? '') === '_blank' || $isExternal) {
                $attrs .= ' target="_blank" rel="noopener noreferrer"';
            }

            echo '<li' . ($isActive ? ' class="is-active"' : '') . '><a href="' . feder_e($href) . '"' . $attrs . '>' . feder_e($label) . '</a></li>';
        }
        echo $coreItems; // Core-HTML aus theme_v4_main_nav() (Links des Cores, bereits escaped)
        echo '</ul>';
    }
}

if (!function_exists('feder_t')) {
    /** Zweisprachiger Textwähler (Seitensprache aus Kontext oder Pfad `/en/…`). */
    function feder_t(string $de, string $en, mixed $contentLocale = null): string
    {
        return theme_v4_t($de, $en, $contentLocale);
    }
}

if (!function_exists('feder_default_menu')) {
    /** @return array<int, array{label:string,url:string}> */
    function feder_default_menu(string $location): array
    {
        return match ($location) {
            'primary' => [
                ['label' => feder_t('Start', 'Home'), 'url' => '/'],
                ['label' => feder_t('Alle Texte', 'All posts'), 'url' => '/blog'],
                ['label' => feder_t('Themen', 'Topics'), 'url' => feder_archive_url('category')],
                ['label' => feder_t('Über mich', 'About me'), 'url' => '/ueber-uns'],
            ],
            'footer-nav' => [
                ['label' => feder_t('Alle Texte', 'All posts'), 'url' => '/blog'],
                ['label' => feder_t('Themen', 'Topics'), 'url' => feder_archive_url('category')],
                ['label' => feder_t('Schlagwörter', 'Tags'), 'url' => feder_archive_url('tag')],
                ['label' => feder_t('Suche', 'Search'), 'url' => '/search'],
                ['label' => 'Newsletter', 'url' => '/newsletter'],
                // 365CMS 4.2.0: nur bei aktivem Community-Modul (sonst herausgefiltert).
                ['label' => 'Community', 'url' => '/' . theme_v4_community_base_path()],
                ['label' => feder_t('Kontakt', 'Contact'), 'url' => '/kontakt'],
            ],
            'footer-legal' => [
                ['label' => feder_t('Impressum', 'Legal notice'), 'url' => '/impressum'],
                ['label' => feder_t('Datenschutz', 'Privacy policy'), 'url' => '/datenschutz'],
            ],
            default => [],
        };
    }
}

if (!function_exists('feder_social_links')) {
    /** @return array<int, array{label:string,url:string,icon:string}> */
    function feder_social_links(): array
    {
        $links = [];
        $map = [
            'social_mastodon' => ['Mastodon', 'mastodon'],
            'social_linkedin' => ['LinkedIn', 'linkedin'],
            'social_github'   => ['GitHub', 'github'],
        ];
        foreach ($map as $key => [$label, $icon]) {
            $url = feder_safe_url(feder_text('feder_author', $key), '');
            if ($url !== '' && preg_match('#^https?://#i', $url) === 1) {
                $links[] = ['label' => $label, 'url' => $url, 'icon' => $icon];
            }
        }

        $email = feder_text('feder_author', 'social_email');
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $links[] = ['label' => 'E-Mail', 'url' => 'mailto:' . $email, 'icon' => 'mail'];
        }
        if (feder_flag('feder_author', 'show_rss', true)) {
            $links[] = ['label' => 'RSS-Feed', 'url' => feder_feed_url(), 'icon' => 'rss'];
        }

        return $links;
    }
}

if (!function_exists('feder_icon')) {
    /** Dekorative Inline-SVG-Icons (aria-hidden, currentColor). */
    function feder_icon(string $name): string
    {
        $paths = [
            'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
            'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
            'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
            'moon'     => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z"/>',
            'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
            'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
            'up'       => '<path d="M12 19V5M6 11l6-6 6 6"/>',
            'link'     => '<path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1.5 1.5"/><path d="M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1.5-1.5"/>',
            'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
            'rss'      => '<path d="M5 5a14 14 0 0 1 14 14M5 11a8 8 0 0 1 8 8"/><circle cx="6" cy="18" r="1.5"/>',
            'mastodon' => '<path d="M20 9.5c0-4-2.6-5.2-2.6-5.2C16 3.7 13.9 3.5 12 3.5s-4 .2-5.4.8C6.6 4.3 4 5.5 4 9.5c0 3.4-.2 7.3 3.2 8.3 1.9.6 3.5.7 4.8.6 2.3-.1 3.6-.8 3.6-.8l-.1-1.7s-1.6.5-3.4.4c-1.8-.1-3.7-.2-4-2.4v-.6s1.8.4 4 .5c1.4.1 2.7-.1 4-.2 2.5-.3 4.7-1.9 4.9-3.3.4-2.3.3-5.6.3-5.6"/><path d="M9 13V9.7c0-1 .8-1.7 1.7-1.7.9 0 1.3.6 1.3 1.6v1.6m0 0V9.6c0-1 .5-1.6 1.4-1.6.9 0 1.6.7 1.6 1.7V13"/>',
            'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
            'github'   => '<path d="M9 19c-4 1.5-4-2-6-2.5m12 5v-3.5a3 3 0 0 0-.8-2.3c2.7-.3 5.6-1.3 5.6-6a4.6 4.6 0 0 0-1.3-3.2 4.3 4.3 0 0 0-.1-3.2s-1-.3-3.4 1.3a11.6 11.6 0 0 0-6 0C6.6 1.7 5.6 2 5.6 2a4.3 4.3 0 0 0-.1 3.2A4.6 4.6 0 0 0 4.2 8.4c0 4.6 2.8 5.7 5.5 6a3 3 0 0 0-.8 2.3V20"/>',
            'bluesky'  => '<path d="M6.3 4.5C8.6 6.2 11 9.8 12 11.8c1-2 3.4-5.6 5.7-7.3 1.7-1.2 4.3-2.1 4.3.8 0 .6-.3 4.8-.5 5.5-.7 2.4-3.2 3-5.4 2.7 3.9.7 4.9 2.9 2.8 5.1-4 4.1-5.8-1-6.2-2.4l-.7-1.9-.7 1.9c-.4 1.4-2.2 6.5-6.2 2.4-2.1-2.2-1.1-4.4 2.8-5.1-2.2.3-4.7-.3-5.4-2.7C2.3 10.1 2 5.9 2 5.3c0-2.9 2.6-2 4.3-.8z"/>',
            'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'share'    => '<circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="m8.2 10.8 7.6-4.4M8.2 13.2l7.6 4.4"/>',
        ];
        $body = $paths[$name] ?? '';
        if ($body === '') {
            return '';
        }

        return '<svg class="fdr-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $body . '</svg>';
    }
}

if (!function_exists('feder_pagination')) {
    /** Seitennavigation für Archive; erhält vorhandene Query-Parameter (z. B. q). */
    function feder_pagination(int $current, int $total, string $param = 'p'): void
    {
        if ($total <= 1) {
            return;
        }

        $path = (string) (parse_url(theme_v4_site_url(theme_v4_request_path()), PHP_URL_PATH) ?? '/'); // normalisiert statt REQUEST_URI
        $build = static function (int $number) use ($path, $param): string {
            $query = array_filter($_GET, static fn(mixed $v): bool => is_scalar($v));
            unset($query['p'], $query['page']);
            if ($number > 1) {
                $query[$param] = $number;
            }
            $qs = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

            return $path . ($qs !== '' ? '?' . $qs : '');
        };

        $pages = [1, $total];
        for ($i = max(1, $current - 1); $i <= min($total, $current + 1); $i++) {
            $pages[] = $i;
        }
        $pages = array_values(array_unique($pages));
        sort($pages);

        echo '<nav class="fdr-pagination" aria-label="' . feder_e(feder_t('Seitennavigation', 'Pagination')) . '">';
        if ($current > 1) {
            echo '<a class="fdr-pagination__step" href="' . feder_e($build($current - 1)) . '" rel="prev">' . feder_icon('arrow-left') . '<span>' . feder_e(feder_t('Neuere', 'Newer')) . '</span></a>';
        }
        echo '<ol class="fdr-pagination__pages">';
        $last = 0;
        foreach ($pages as $number) {
            if ($last > 0 && $number - $last > 1) {
                echo '<li class="fdr-pagination__gap" aria-hidden="true">…</li>';
            }
            if ($number === $current) {
                echo '<li><span aria-current="page">' . $number . '</span></li>';
            } else {
                echo '<li><a href="' . feder_e($build($number)) . '">' . $number . '</a></li>';
            }
            $last = $number;
        }
        echo '</ol>';
        if ($current < $total) {
            echo '<a class="fdr-pagination__step" href="' . feder_e($build($current + 1)) . '" rel="next"><span>' . feder_e(feder_t('Ältere', 'Older')) . '</span>' . feder_icon('arrow') . '</a>';
        }
        echo '</nav>';
    }
}

if (!function_exists('feder_search_form')) {
    function feder_search_form(string $id, string $value = '', string $class = ''): void
    {
        ?>
        <form role="search" method="get" action="<?php echo feder_e(feder_search_url()); ?>" class="fdr-searchform<?php echo $class !== '' ? ' ' . feder_e($class) : ''; ?>">
            <label for="<?php echo feder_e($id); ?>" class="fdr-visually-hidden"><?php echo feder_e(feder_t('Suchbegriff', 'Search term')); ?></label>
            <input id="<?php echo feder_e($id); ?>" type="search" name="q" value="<?php echo feder_e($value); ?>" placeholder="<?php echo feder_e(feder_t('Wonach suchen Sie?', 'What are you looking for?')); ?>" maxlength="120" autocomplete="off">
            <?php $submitLabel = feder_t('Suchen', 'Search'); // aria-label: mobil blendet CSS den sichtbaren Text aus ?>
            <button type="submit" class="fdr-button" aria-label="<?php echo feder_e($submitLabel); ?>"><?php echo feder_icon('search'); ?><span><?php echo feder_e($submitLabel); ?></span></button>
        </form>
        <?php
    }
}

if (!function_exists('feder_page_title')) {
    /**
     * Dokumenttitel: übergebener Seitentitel (`$title` der Core-Module, Kontakt- und Fehlerseiten),
     * sonst SEO-Titel für Inhalte, sonst „Bezeichnung – Seitentitel“ (über den Filter page_title).
     */
    function feder_page_title(array $context): string
    {
        if (isset($context['title']) && is_string($context['title']) && trim($context['title']) !== '') {
            return theme_v4_document_title($context['title']);
        }

        $site = feder_site_title();
        $label = '';

        if (isset($context['post']) || isset($context['page'])) {
            try {
                $payload = \CMS\Services\SEOService::getInstance()->getCurrentSeoPayload();
                $label = trim((string) ($payload['title'] ?? ''));
                if ($label !== '') {
                    return (string) \CMS\Hooks::applyFilters('page_title', $label);
                }
            } catch (\Throwable) {
            }
            $source = $context['post'] ?? $context['page'];
            $label = trim((string) (is_array($source) ? ($source['title'] ?? '') : ($source->title ?? '')));
        } elseif (isset($context['author']) && is_array($context['author'])) {
            $label = feder_t('Texte von ', 'Posts by ', $context['contentLocale'] ?? null) . trim((string) ($context['author']['display_name'] ?? ''));
        } elseif (isset($context['category']) && is_array($context['category'])) {
            $label = trim((string) ($context['category']['name'] ?? ''));
        } elseif (isset($context['tag']) && is_array($context['tag'])) {
            $label = feder_t('Schlagwort: ', 'Tag: ', $context['contentLocale'] ?? null) . trim((string) ($context['tag']['name'] ?? ''));
        } elseif (array_key_exists('results', $context)) {
            $query = trim((string) ($context['query'] ?? ''));
            $label = $query !== '' ? feder_t('Suche: ', 'Search: ', $context['contentLocale'] ?? null) . $query : feder_t('Suche', 'Search', $context['contentLocale'] ?? null);
        } elseif (isset($context['posts'])) {
            $label = feder_t('Alle Texte', 'All posts', $context['contentLocale'] ?? null);
        } elseif (http_response_code() === 404) {
            $label = feder_t('Seite nicht gefunden', 'Page not found', $context['contentLocale'] ?? null);
        }

        $separator = '–';
        try {
            $sep = trim((string) \CMS\Services\SEOService::getInstance()->getTitleSeparator());
            if ($sep !== '') {
                $separator = $sep;
            }
        } catch (\Throwable) {
        }

        $title = $label !== '' ? $label . ' ' . $separator . ' ' . $site : $site;

        return (string) \CMS\Hooks::applyFilters('page_title', $title);
    }
}

if (!function_exists('feder_img')) {
    /**
     * Bild über theme_v4_img(): width/height (echte Maße lokaler Uploads, sonst die Layout-Maße),
     * `loading="lazy"` bzw. für das LCP-Bild `fetchpriority="high"` und Fokus-Punkt (4.1.0).
     *
     * @param array{class?:string,lcp?:bool,item?:mixed,sizes?:string} $options
     */
    function feder_img(string $src, string $alt, array $options = [], int $width = 0, int $height = 0): string
    {
        if ($width > 0 && $height > 0) {
            [$options['width'], $options['height']] = theme_v4_image_dimensions($src) ?? [$width, $height];
        }

        return theme_v4_img($src, $alt, $options);
    }
}

if (!function_exists('feder_body_class')) {
    function feder_body_class(string ...$extra): string
    {
        $classes = ['feder'];
        foreach ($extra as $class) {
            $class = trim($class);
            if ($class !== '' && preg_match('/^[a-z0-9_-]+$/i', $class) === 1) {
                $classes[] = $class;
            }
        }

        return implode(' ', array_unique($classes));
    }
}

// ── 365CMS 3.4 Laufzeit-Helfer (CSP-Nonce, lokale Schriften, Permalinks) ──────

if (!function_exists('theme_csp_nonce_attr')) {
    /** Liefert ` nonce="…"` für Inline-<style>/<script> unter der 365CMS-CSP. */
    function theme_csp_nonce_attr(): string
    {
        try {
            $attr = class_exists('\\CMS\\Security') ? (string) \CMS\Security::instance()->nonceAttr() : '';
        } catch (\Throwable) {
            $attr = '';
        }

        return $attr !== '' ? ' ' . $attr : '';
    }
}

if (!function_exists('theme_use_local_fonts')) {
    /**
     * true, wenn im Core „Schriften lokal einbinden“ (privacy_use_local_fonts) aktiv ist (OptionStore statt SQL).
     * Feder lädt in keinem Fall Google Fonts; die Funktion bleibt für Plugins/Child-Code erhalten.
     */
    function theme_use_local_fonts(): bool
    {
        return theme_v4_use_local_fonts();
    }
}

if (!function_exists('theme_post_url')) {
    /** Permalink eines Beitrags (Core-PermalinkService, Fallback /blog/{slug}). */
    function theme_post_url(object|array $post): string
    {
        try {
            if (class_exists('\\CMS\\Services\\PermalinkService')) {
                return \CMS\Services\PermalinkService::getInstance()->buildPostUrl($post);
            }
        } catch (\Throwable) {
        }
        $slug = is_array($post) ? (string) ($post['slug'] ?? '') : (string) ($post->slug ?? '');

        return rtrim((string) SITE_URL, '/') . '/blog/' . rawurlencode($slug);
    }
}

if (!function_exists('theme_search_result_url')) {
    /** URL eines Suchtreffers aus ThemeRouter::renderSearch() (_type + slug). */
    function theme_search_result_url(object|array $result): string
    {
        $data = is_array($result) ? $result : get_object_vars($result);
        $type = (string) ($data['_type'] ?? '');
        $slug = ltrim((string) ($data['slug'] ?? ''), '/');

        if ($type === 'post') {
            return theme_post_url($data);
        }
        if (($type === 'category' || $type === 'tag') && function_exists('cms_get_archive_url')) {
            $plain = rawurldecode((string) preg_replace('#^(?:category|kategorie|tag)/#', '', $slug));
            try {
                return (string) cms_get_archive_url($type, $plain);
            } catch (\Throwable) {
            }
        }

        return rtrim((string) SITE_URL, '/') . '/' . implode('/', array_map('rawurlencode', array_map('rawurldecode', explode('/', $slug))));
    }
}
