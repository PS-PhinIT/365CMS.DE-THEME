<?php
declare(strict_types=1);

/**
 * PTC Theme – Personalvermittlung & Schulungen (365CMS 4.0.0 / 4.1.0, PHP 8.4.1)
 *
 * @package PTC_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('PTC_THEME_VERSION')) {
    define('PTC_THEME_VERSION', '4.1.0');
}

if (!defined('PTC_THEME_SLUG')) {
    define('PTC_THEME_SLUG', 'PTC');
}

// 365CMS 4.x: header.php öffnet <main id="main-content">, footer.php schließt es.
if (!defined('THEME_V4_HEADER_HAS_MAIN')) {
    define('THEME_V4_HEADER_HAS_MAIN', true);
}
if (!defined('THEME_V4_MAIN_ID')) {
    define('THEME_V4_MAIN_ID', 'main-content');
}
require_once __DIR__ . '/includes/cms-v4-compat.php';

/**
 * Theme bootstrap (singleton).
 */
final class PTC_Theme
{
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
        $this->registerHooks();
    }

    private function registerHooks(): void
    {
        // Menüpositionen nur über den Filter (ThemeManager::registerMenuLocation() gibt es in 4.0.0 nicht mehr).
        \CMS\Hooks::addFilter('register_menu_locations', [$this, 'registerMenuLocations']);

        // Der Core-Mitgliederbereich (/member) ist eine eigene Tabler-Shell und löst ebenfalls `head` aus:
        // dort kein Frontend-Stylesheet, keine Theme-Scripts und kein eigener Code, nur die Theme-Palette.
        if (theme_v4_is_member_area()) {
            \CMS\Hooks::addAction('member_head', [$this, 'outputMemberTokens'], 20);
            return;
        }

        \CMS\Hooks::addAction('head', [$this, 'enqueueStyles'],      1);
        \CMS\Hooks::addAction('head', [$this, 'outputMetaTags'],     10);
        \CMS\Hooks::addAction('head', [$this, 'outputCustomStyles'], 15);

        \CMS\Hooks::addAction('before_footer', [$this, 'enqueueScripts'], 99);
        \CMS\Hooks::addAction('body_end', [$this, 'outputCustomFooterCode'], 99);
    }

    /** Memberbereich (inkl. Newsletter-Widget) in der PTC-Palette; das Theme hat keinen Dark Mode. */
    public function outputMemberTokens(): void
    {
        $color = fn(string $group, string $key, string $default): string =>
            ptc_css_color((string) $this->customizerGet($group, $key, $default), $default);

        echo theme_v4_member_tokens_style([
            'primary'    => $color('colors', 'primary_color', '#1a4d5c'),
            'accent'     => $color('colors', 'accent_color', '#2d7a5f'),
            'bg'         => $color('colors', 'bg_secondary', '#f1f5f9'),
            'card_bg'    => '#ffffff',
            'text'       => $color('colors', 'text_color', '#334155'),
            'text_muted' => $color('colors', 'muted_color', '#5b6b78'),
            'border'     => $color('colors', 'border_color', '#e2e8f0'),
            'focus'      => $color('colors', 'primary_color', '#1a4d5c'),
            'radius'     => ptc_css_number($this->customizerGet('layout', 'border_radius', '10'), 10, 0, 32) . 'px',
            'font'       => $this->mapFontChoice((string) $this->customizerGet('typography', 'font_family_base', 'system'), 'system'),
        ]);
    }

    public function enqueueStyles(): void
    {
        $base = $this->themeBaseUrl();
        $ver  = rawurlencode(PTC_THEME_VERSION);
        echo '<link rel="stylesheet" href="' . htmlspecialchars($base . '/style.css?v=' . $ver, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        // Gemeinsame 4.x-Integrationsstile (Downloads, Feeds, Formulare, Knowledgebase, Newsletter …) nach style.css.
        echo '<link rel="stylesheet" href="' . htmlspecialchars($base . '/css/cms-v4.css?v=' . $ver, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        // Schriften: keine Google-Fonts-Requests (DSGVO). Lokale Schriften liefert die Core-Schriftverwaltung,
        // sonst greift der System-Font-Stack.
    }

    public function enqueueScripts(): void
    {
        $src = htmlspecialchars(
            $this->themeBaseUrl() . '/js/navigation.js?v=' . rawurlencode(PTC_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<script src="' . $src . '" defer></script>' . "\n";
    }

    /**
     * Nur noch `theme-color`: Beschreibung, Canonical, Robots und Open Graph kommen aus dem Core-SEO
     * (theme_v4_head_tags() in header.php); eigene Ausgaben wären doppelt und im Vorschaufall falsch.
     */
    public function outputMetaTags(): void
    {
        $themeColor = htmlspecialchars(
            ptc_css_color((string) $this->customizerGet('colors', 'primary_color', '#1a4d5c'), '#1a4d5c'),
            ENT_QUOTES,
            'UTF-8'
        );

        echo '<meta name="theme-color" content="' . $themeColor . '">' . "\n";
    }

    public function outputCustomStyles(): void
    {
        $p = fn(string $g, string $k, string $d): string =>
            htmlspecialchars(
                ptc_css_color((string) $this->customizerGet($g, $k, $d), $d),
                ENT_QUOTES,
                'UTF-8'
            );
        $num = fn(string $g, string $k, string $d, float $min, float $max): string =>
            ptc_css_number($this->customizerGet($g, $k, $d), (float) $d, $min, $max);
        // Auswahlwerte nur aus einer festen Liste in das CSS übernehmen (kein Ausbruch aus der Deklaration).
        $choice = function (string $g, string $k, array $allowed, string $d): string {
            $value = strtolower(trim((string) $this->customizerGet($g, $k, $d)));
            return in_array($value, $allowed, true) ? $value : $d;
        };

        $baseChoice    = (string) $this->customizerGet('typography', 'font_family_base',    'system');
        $headingChoice = (string) $this->customizerGet('typography', 'font_family_heading', 'system');
        // Feste Stacks aus mapFontChoice() – nicht HTML-escapen: &quot; im <style> machte die Deklaration ungültig.
        $baseFont      = $this->mapFontChoice($baseChoice, 'system');
        $headingFont   = $this->mapFontChoice($headingChoice, 'system');

        echo '<style id="ptc-custom-vars"' . theme_csp_nonce_attr() . '>:root{';

        echo '--color-primary:'         . $p('colors', 'primary_color', '#1a4d5c') . ';';
        echo '--color-primary-hover:'   . $p('colors', 'primary_hover', '#123a47') . ';';
        echo '--color-primary-light:'   . $p('colors', 'primary_light', '#e8f2f4') . ';';
        echo '--color-accent:'          . $p('colors', 'accent_color', '#2d7a5f') . ';';
        echo '--color-accent-hover:'    . $p('colors', 'accent_hover', '#236349') . ';';
        echo '--color-accent-light:'    . $p('colors', 'accent_light', '#e6f4ef') . ';';
        echo '--color-secondary:'       . $p('colors', 'secondary_color', '#546e7a') . ';';
        echo '--color-text:'            . $p('colors', 'text_color', '#334155') . ';';
        echo '--color-heading:'         . $p('colors', 'heading_color', '#1a4d5c') . ';';
        echo '--color-on-dark:'         . $p('colors', 'text_light', '#f4f7f8') . ';';
        echo '--color-muted:'           . $p('colors', 'muted_color', '#5b6b78') . ';';
        echo '--color-bg:'              . $p('colors', 'bg_color', '#f6f8f9') . ';';
        echo '--color-bg-alt:'          . $p('colors', 'bg_secondary', '#eef2f4') . ';';
        echo '--color-link:'            . $p('colors', 'link_color', '#1a4d5c') . ';';
        echo '--color-link-hover:'      . $p('colors', 'link_hover_color', '#2d7a5f') . ';';
        echo '--color-border:'          . $p('colors', 'border_color', '#d8e0e5') . ';';
        echo '--color-success:'         . $p('colors', 'success_color', '#2e7d4f') . ';';
        echo '--color-error:'           . $p('colors', 'error_color', '#b42318') . ';';

        echo '--color-cta-bg:'           . $p('colors', 'cta_bg_color', '#1a4d5c') . ';';
        echo '--color-cta-bg-end:'       . $p('colors', 'cta_bg_to', '#123a47') . ';';
        echo '--color-cta-text:'         . $p('colors', 'cta_text_color', '#ffffff') . ';';
        echo '--color-side-margin:'      . $p('colors', 'side_margin_color', '#edf1f3') . ';';
        echo '--color-side-accent:'      . $p('colors', 'side_accent_color', '#1a4d5c') . ';';
        echo '--color-side-width:'       . $num('colors', 'side_accent_width', '0', 0, 12) . 'px;';

        echo '--color-hero-bg:'          . $p('colors', 'hero_bg_color', '#f6f8f9') . ';';
        echo '--color-hero-bg-end:'      . $p('colors', 'hero_bg_to', '#eef2f4') . ';';
        echo '--color-services-bg:'      . $p('colors', 'services_bg_color', '#ffffff') . ';';
        echo '--color-service-card-bg:'  . $p('colors', 'services_card_bg', '#ffffff') . ';';
        echo '--color-events-bg:'        . $p('colors', 'events_bg_color', '#eef2f4') . ';';
        echo '--color-event-card-bg:'    . $p('colors', 'events_card_bg', '#ffffff') . ';';
        echo '--color-faq-bg:'           . $p('colors', 'faq_bg_color', '#ffffff') . ';';
        echo '--color-faq-item-bg:'      . $p('colors', 'faq_item_bg', '#ffffff') . ';';
        echo '--color-network-bar-bg:'   . $p('colors', 'network_bar_bg', '#0f2d38') . ';';
        echo '--color-network-bar-text:' . $p('colors', 'network_bar_text', '#ffffff') . ';';

        echo '--color-blog-hero-bg:'     . $p('colors', 'blog_hero_bg', '#1a4d5c') . ';';
        echo '--color-blog-hero-text:'   . $p('colors', 'blog_hero_text', '#ffffff') . ';';
        echo '--color-blog-content-bg:'  . $p('colors', 'blog_content_bg', '#f6f8f9') . ';';
        echo '--color-blog-card-bg:'     . $p('colors', 'blog_card_bg', '#ffffff') . ';';
        echo '--color-blog-card-title:'  . $p('colors', 'blog_card_title', '#1a4d5c') . ';';
        echo '--color-blog-cat-bg:'      . $p('colors', 'blog_category_bg', '#e6f4ef') . ';';
        echo '--color-blog-cat-text:'    . $p('colors', 'blog_category_text', '#236349') . ';';

        echo '--font-body:'              . $baseFont . ';';
        echo '--font-heading:'           . $headingFont . ';';
        echo '--font-size-base:'          . $num('typography', 'font_size_base', '16', 12, 24) . 'px;';
        echo '--line-height-base:'        . $num('typography', 'line_height_base', '1.6', 1.2, 2.5) . ';';
        echo '--font-weight-heading:'     . $choice('typography', 'font_weight_heading', ['400', '500', '600', '700', '800', '900'], '700') . ';';

        echo '--layout-container:'        . $num('layout', 'container_width', '1280', 800, 1920) . 'px;';
        echo '--layout-content-pad:'      . $num('layout', 'content_padding', '2', 0.5, 6) . 'rem;';
        echo '--layout-radius:'           . $num('layout', 'border_radius', '10', 0, 32) . 'px;';
        echo '--layout-section-gap:'      . $num('layout', 'section_spacing', '5', 1, 10) . 'rem;';

        echo '--header-bg:'               . $p('header', 'header_bg_color', '#1a4d5c') . ';';
        echo '--header-text:'             . $p('header', 'header_text_color', '#f4f7f8') . ';';
        echo '--header-accent:'           . $p('header', 'header_accent_color', '#2d7a5f') . ';';
        echo '--header-height:'           . $num('header', 'header_height', '72', 48, 120) . 'px;';
        echo '--logo-max-height:'         . $num('header', 'logo_max_height', '48', 24, 100) . 'px;';
        echo '--nav-font-size:'           . $num('header', 'nav_font_size', '14', 11, 20) . 'px;';
        echo '--nav-sub-font-size:'       . $num('header', 'nav_sub_font_size', '13', 10, 18) . 'px;';

        echo '--footer-bg:'               . $p('footer', 'footer_bg_color', '#0f2d38') . ';';
        echo '--footer-text:'             . $p('footer', 'footer_text_color', '#94a8b3') . ';';
        echo '--footer-link:'             . $p('footer', 'footer_link_color', '#f4f7f8') . ';';

        echo '--btn-radius:'              . $num('buttons', 'button_border_radius', '8', 0, 50) . 'px;';
        echo '--btn-padding-x:'           . $num('buttons', 'button_padding_x', '2', 0.5, 4) . 'rem;';
        echo '--btn-padding-y:'           . $num('buttons', 'button_padding_y', '0.875', 0.25, 2) . 'rem;';
        echo '--btn-font-weight:'         . $choice('buttons', 'button_font_weight', ['400', '500', '600', '700', '800'], '600') . ';';
        echo '--btn-text-transform:'      . $choice('buttons', 'button_transform', ['none', 'uppercase', 'capitalize', 'lowercase'], 'none') . ';';

        // Fokusrahmen auf hellen Flächen (Akzent: 5,2:1 auf Weiß); dunkle Flächen nutzen --focus-ring-on-dark (style.css).
        echo '--focus-ring:'              . $p('colors', 'accent_color', '#2d7a5f') . ';';

        echo '}';

        $stickyHeader = filter_var(
            $this->customizerGet('layout', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$stickyHeader) {
            echo '.ptc-header{position:static;}.ptc-content{padding-top:0;}';
        }

        $custom = $this->sanitizeCustomCss((string) $this->customizerGet('advanced', 'custom_css', ''));
        if ($custom !== '') {
            echo $custom;
        }

        echo '</style>' . "\n";

        $headCode = ptc_custom_head_code((string) $this->customizerGet('advanced', 'custom_head_code', ''));
        if ($headCode !== '') {
            echo $headCode . "\n";
        }
    }

    public function outputCustomFooterCode(): void
    {
        $footerCode = ptc_custom_footer_code((string) $this->customizerGet('advanced', 'custom_footer_code', ''));
        if ($footerCode !== '') {
            echo $footerCode . "\n";
        }
    }

    public function registerMenuLocations(array $locations): array
    {
        $locations[] = ['slug' => 'primary',         'label' => 'Hauptnavigation (Header)'];
        $locations[] = ['slug' => 'footer-nav',      'label' => 'Footer-Navigation'];
        $locations[] = ['slug' => 'footer-services', 'label' => 'Footer Leistungen'];
        $locations[] = ['slug' => 'footer-legal',    'label' => 'Footer Rechtliche Links'];
        return $locations;
    }

    /**
     * Standardeinträge je Menüposition, solange im Admin kein Menü gepflegt ist.
     *
     * Bis 3.1.0 wurden sie bei jedem Request (cms_init) in die Datenbank geschrieben; seit 4.0.0 gelten sie
     * nur noch zur Laufzeit und zeigen interne Ziele nur, wenn Route bzw. veröffentlichte Seite existieren.
     *
     * @return list<array{label:string,url:string}>
     */
    public static function defaultMenu(string $location): array
    {
        $defaults = [
            'primary' => [
                ['label' => 'Start',           'url' => '/'],
                ['label' => 'Für Kandidaten',  'url' => '/#kandidaten'],
                ['label' => 'Für Arbeitgeber', 'url' => '/#arbeitgeber'],
                ['label' => 'Weiterbildung',   'url' => '/#dienstleistungen'],
                ['label' => 'Termine',         'url' => '/#termine'],
                ['label' => 'Kontakt',         'url' => '/contact'],
            ],
            'footer-nav' => [
                ['label' => 'Startseite', 'url' => '/'],
                ['label' => 'Leistungen', 'url' => '/#dienstleistungen'],
                ['label' => 'FAQ',        'url' => '/#faq'],
                ['label' => 'Newsletter', 'url' => '/newsletter'],
                // 365CMS 4.2.0: nur bei aktivem Community-Modul (sonst herausgefiltert).
                ['label' => 'Community',  'url' => '/' . theme_v4_community_base_path()],
                ['label' => 'Kontakt',    'url' => '/contact'],
            ],
            'footer-services' => [
                ['label' => 'Personalvermittlung',     'url' => '/#dienstleistungen'],
                ['label' => 'Arbeitnehmerüberlassung', 'url' => '/#dienstleistungen'],
                ['label' => 'Weiterbildung',           'url' => '/#dienstleistungen'],
                ['label' => 'Qualifizierung',          'url' => '/#dienstleistungen'],
            ],
            'footer-legal' => [
                ['label' => 'Impressum',   'url' => '/impressum'],
                ['label' => 'Datenschutz', 'url' => '/datenschutz'],
            ],
        ];

        $links = array_map(
            static fn(array $link): array => ['label' => $link['label'], 'url' => theme_v4_site_url($link['url'])],
            $defaults[$location] ?? []
        );

        return theme_v4_filter_links($links);
    }

    public function getConfig(string $key, mixed $default = ''): mixed
    {
        static $config = null;

        if ($config === null) {
            $jsonFile = __DIR__ . '/theme.json';
            if (file_exists($jsonFile)) {
                $decoded = json_decode((string) file_get_contents($jsonFile), true);
                $config  = is_array($decoded) ? ($decoded['settings'] ?? []) : [];
            } else {
                $config = [];
            }
        }

        return $config[$key] ?? $default;
    }

    private function themeBaseUrl(): string
    {
        return theme_v4_theme_url(__DIR__);
    }

    private function customizerGet(string $group, string $key, mixed $default = null): mixed
    {
        try {
            return \CMS\Services\ThemeCustomizer::instance()->get($group, $key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    private function mapFontChoice(string $choice, string $fallback): string
    {
        $systemStack = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        $serifStack  = 'Georgia, ui-serif, "Times New Roman", serif';

        return match ($choice) {
            'system'     => $systemStack,
            'inter'      => 'Inter, ' . $systemStack,
            'roboto'     => 'Roboto, ' . $systemStack,
            'open-sans'  => '"Open Sans", ' . $systemStack,
            'lato'       => 'Lato, ' . $systemStack,
            'montserrat' => 'Montserrat, ' . $systemStack,
            'poppins'    => 'Poppins, ' . $systemStack,
            'raleway'    => 'Raleway, ' . $systemStack,
            'georgia'    => $serifStack,
            default      => $fallback === 'system' ? $systemStack : $fallback,
        };
    }

    private function sanitizeCustomCss(string $raw): string
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return '';
        }
        // Wiederholt bereinigen (verhindert z. B. `</</style>style>`) und jedes `</` neutralisieren,
        // damit eigenes CSS den <style>-Block nicht verlassen kann.
        $clean = $trimmed;
        do {
            $previous = $clean;
            $clean = (string) preg_replace('#<\/?\s*style\b[^>]*>#i', '', $clean);
        } while ($clean !== $previous);

        return str_replace('</', '<\/', $clean);
    }
}

PTC_Theme::instance();

// ── Theme helpers ─────────────────────────────────────────────────────────────

if (!function_exists('ptc_css_color')) {
    function ptc_css_color(string $value, string $fallback): string
    {
        $candidate = trim($value);
        if (preg_match('/^#[0-9a-f]{3,8}$/i', $candidate) === 1) {
            return $candidate;
        }
        if (preg_match('/^(rgb|rgba|hsl|hsla)\(\s*[0-9.%\s,]+\)$/i', $candidate) === 1) {
            return $candidate;
        }
        if (preg_match('/^var\(--[a-z0-9_-]+\)$/i', $candidate) === 1) {
            return $candidate;
        }

        return $fallback;
    }
}

if (!function_exists('ptc_css_number')) {
    function ptc_css_number(mixed $value, float $default, float $min, float $max): string
    {
        $number = is_numeric($value) ? (float) $value : $default;
        $number = max($min, min($max, $number));

        return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
    }
}

if (!function_exists('ptc_strip_active_html')) {
    /** Entfernt Script-/Einbettungs-Elemente, Event-Handler und `javascript:`/`data:`-Ziele aus Admin-HTML. */
    function ptc_strip_active_html(string $html): string
    {
        $html = (string) preg_replace('#<(script|style|iframe|object|embed|template|base|form)\b[^>]*>.*?</\1\s*>#is', '', $html);
        $html = (string) preg_replace('#<\/?(script|style|iframe|object|embed|template|base|form|meta\s+http-equiv)\b[^>]*>#i', '', $html);
        $html = (string) preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
        // URL-Attribute dekodiert prüfen (`&#106;avascript:` wäre sonst durchgerutscht).
        $html = (string) preg_replace_callback(
            '#\s+(href|src|action|formaction|xlink:href|srcset)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i',
            static function (array $m): string {
                $value = html_entity_decode(trim($m[2], '"\''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $value = strtolower((string) preg_replace('/[\x00-\x20\x7F]+/', '', $value));

                return preg_match('#^(?:javascript|vbscript|data):#', $value) === 1 ? '' : $m[0];
            },
            $html
        );

        return trim($html);
    }
}

if (!function_exists('ptc_custom_head_code')) {
    /**
     * „Custom Head Code“: nur `<meta>` und `<link>` (ohne Event-Handler). Scripts blockiert die 365CMS-CSP ohnehin;
     * bis 4.0.0 wurde der Inhalt roh ausgegeben (Stored XSS bei abgeschalteter CSP).
     */
    function ptc_custom_head_code(string $code): string
    {
        if (trim($code) === '' || preg_match_all('#<(?:meta|link)\b[^>]*>#i', $code, $tags) < 1) {
            return '';
        }
        $out = [];
        foreach ($tags[0] as $tag) {
            if (preg_match('#\bhttp-equiv\s*=#i', $tag) === 1) {
                continue; // z. B. refresh/Set-Cookie/CSP-Überschreibung
            }
            $tag = ptc_strip_active_html($tag);
            if ($tag !== '') {
                $out[] = $tag;
            }
        }

        return implode("\n", $out);
    }
}

if (!function_exists('ptc_custom_footer_code')) {
    /** „Custom Footer Code“: statisches HTML ohne Scripts, Einbettungen und Event-Handler (CSP-konform). */
    function ptc_custom_footer_code(string $code): string
    {
        return trim($code) === '' ? '' : ptc_strip_active_html($code);
    }
}

if (!function_exists('ptc_safe_url')) {
    function ptc_safe_url(string $url, string $fallback = ''): string
    {
        $candidate = trim($url);
        // `//host` und `/\host` (Browser werten „\“ wie „/“) sind fremde, protokollrelative Adressen.
        if ($candidate === '' || str_starts_with($candidate, '//') || str_contains($candidate, '\\') || preg_match('/[\x00-\x1F\x7F]/', $candidate) === 1) {
            return $fallback;
        }
        if (str_starts_with($candidate, '/')) {
            return $candidate;
        }
        if (str_starts_with($candidate, '#')) {
            return preg_match('/^#[A-Za-z][A-Za-z0-9_-]*$/', $candidate) === 1 ? $candidate : $fallback;
        }
        if (preg_match('/^mailto:[^\s@]+@[^\s@]+\.[^\s@]+$/i', $candidate) === 1) {
            return $candidate;
        }
        if (preg_match('/^tel:\+?[0-9][0-9\s().-]{2,31}$/i', $candidate) === 1) {
            return $candidate;
        }

        $scheme = parse_url($candidate, PHP_URL_SCHEME);
        if (in_array(strtolower((string) $scheme), ['http', 'https'], true) && filter_var($candidate, FILTER_VALIDATE_URL)) {
            return $candidate;
        }

        return $fallback;
    }
}

if (!function_exists('ptc_get_setting')) {
    function ptc_get_setting(string $section, string $key, mixed $default = ''): mixed
    {
        try {
            return \CMS\Services\ThemeCustomizer::instance()->get($section, $key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (!function_exists('ptc_customizer_get')) {
    function ptc_customizer_get(string $section, string $key, mixed $default = ''): mixed
    {
        return ptc_get_setting($section, $key, $default);
    }
}

if (!function_exists('ptc_customizer_category')) {
    function ptc_customizer_category(string $section): array
    {
        try {
            return \CMS\Services\ThemeCustomizer::instance()->getCategory($section) ?: [];
        } catch (\Throwable) {
            return [];
        }
    }
}

if (!function_exists('ptc_customizer_echo')) {
    function ptc_customizer_echo(string $section, string $key, mixed $default = ''): void
    {
        echo htmlspecialchars((string) ptc_get_setting($section, $key, $default), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('ptc_nav_menu')) {
    function ptc_nav_menu(string $location = 'primary', string $mainNavContext = ''): void
    {
        theme_nav_menu($location, $mainNavContext);
    }
}

if (!function_exists('ptc_menu_item_active')) {
    /** Aktiver Menüeintrag: gleicher Pfad (Startseite nur exakt); Anker-Links (/#…) gelten nie als aktiv. */
    function ptc_menu_item_active(string $url): bool
    {
        if ($url === '' || str_contains($url, '#')) {
            return false;
        }

        return theme_v4_is_current_path($url);
    }
}

if (!function_exists('theme_nav_menu')) {
    /**
     * Menü einer Position als Liste. Ist im Admin nichts gepflegt, greifen die Standardeinträge des Themes
     * (PTC_Theme::defaultMenu(), nur vorhandene Ziele). Mit `$mainNavContext` (`desktop`/`mobile`) folgen
     * direkt nach den Menüeinträgen die Einträge der Core-Module (Hook `main_nav`: Downloads, Knowledgebase,
     * Newsletter) – auch wenn das Menü leer ist.
     */
    function theme_nav_menu(string $location = 'primary', string $mainNavContext = ''): void
    {
        try {
            $items = \CMS\ThemeManager::instance()->getMenu($location);
        } catch (\Throwable) {
            $items = [];
        }
        if (!is_array($items) || $items === []) {
            $items = PTC_Theme::defaultMenu($location);
        }

        $coreItems = '';
        if ($mainNavContext !== '') {
            ob_start();
            theme_v4_main_nav($mainNavContext, ['wrap' => 'li', 'active_item_class' => 'active']);
            $coreItems = (string) ob_get_clean();
        }
        if ($items === [] && $coreItems === '') {
            return;
        }

        echo '<ul>' . "\n";
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $url      = ptc_safe_url((string) ($item['url'] ?? '#'), '#');
            $isActive = ptc_menu_item_active($url);
            $children = (!empty($item['children']) && is_array($item['children'])) ? $item['children'] : [];
            $classes  = [];
            if ($isActive) {
                $classes[] = 'active';
            }
            if ($children !== []) {
                $classes[] = 'has-children';
            }
            $classAttr = $classes !== [] ? ' class="' . implode(' ', $classes) . '"' : '';
            $target = (($item['target'] ?? '') === '_blank') ? ' target="_blank" rel="noopener noreferrer"' : '';
            // Der Core liefert die Beschriftung im Feld `title`, ältere Menüs/Standardeinträge in `label`.
            $label = htmlspecialchars((string) ($item['label'] ?? $item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
            echo '<li' . $classAttr . '><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $target
                . ($isActive ? ' aria-current="page"' : '') . '>' . $label . '</a>' . "\n";

            if ($children !== []) {
                echo '<ul class="ptc-submenu">' . "\n";
                foreach ($children as $child) {
                    if (!is_array($child)) {
                        continue;
                    }
                    $cUrl    = ptc_safe_url((string) ($child['url'] ?? '#'), '#');
                    $cActive = ptc_menu_item_active($cUrl);
                    $cLabel  = htmlspecialchars((string) ($child['label'] ?? $child['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $cTarget = (($child['target'] ?? '') === '_blank') ? ' target="_blank" rel="noopener noreferrer"' : '';
                    echo '<li' . ($cActive ? ' class="active"' : '') . '><a href="' . htmlspecialchars($cUrl, ENT_QUOTES, 'UTF-8') . '"'
                        . $cTarget . ($cActive ? ' aria-current="page"' : '') . '>' . $cLabel . '</a></li>' . "\n";
                }
                echo '</ul>' . "\n";
            }

            echo '</li>' . "\n";
        }
        echo $coreItems;
        echo '</ul>' . "\n";
    }
}

if (!function_exists('ptc_config')) {
    function ptc_config(string $key, mixed $default = ''): mixed
    {
        return PTC_Theme::instance()->getConfig($key, $default);
    }
}

if (!function_exists('ptc_site_url')) {
    function ptc_site_url(): string
    {
        return rtrim(ptc_safe_url((string) (defined('SITE_URL') ? SITE_URL : '/'), '/'), '/');
    }
}

if (!function_exists('ptc_site_title')) {
    function ptc_site_title(): string
    {
        return htmlspecialchars(\CMS\ThemeManager::instance()->getSiteTitle(), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('ptc_href')) {
    function ptc_href(string $target): string
    {
        $trimmed = trim($target);
        $site    = ptc_site_url();
        if ($trimmed === '') {
            return $site . '/';
        }
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return ptc_safe_url($trimmed, $site . '/');
        }
        if (str_starts_with($trimmed, 'mailto:') || str_starts_with($trimmed, 'tel:')) {
            return ptc_safe_url($trimmed, $site . '/');
        }
        if (str_starts_with($trimmed, '#')) {
            // Anker der Startseite (bis 3.1.0 fiel das # weg und führte auf /<anker>).
            return preg_match('/^#[A-Za-z][A-Za-z0-9_-]*$/', $trimmed) === 1 ? $site . '/' . $trimmed : $site . '/';
        }
        return ptc_safe_url($site . '/' . ltrim($trimmed, '/'), $site . '/');
    }
}

if (!function_exists('theme_route_url')) {
    function theme_route_url(string $name): string
    {
        return match ($name) {
            'home'     => ptc_site_url() . '/',
            'login'    => ptc_site_url() . '/login',
            'register' => ptc_site_url() . '/register',
            'member'   => ptc_site_url() . '/member',
            'blog'     => ptc_site_url() . '/blog',
            'search'   => ptc_site_url() . '/search',
            default    => ptc_site_url() . '/' . ltrim($name, '/'),
        };
    }
}

if (!function_exists('ptc_safe_headline')) {
    function ptc_safe_headline(string $raw): string
    {
        $escaped = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
        return str_replace(
            ['&lt;span class=&quot;highlight&quot;&gt;', '&lt;/span&gt;'],
            ['<span class="highlight">', '</span>'],
            $escaped
        );
    }
}

if (!function_exists('ptc_body_class')) {
    function ptc_body_class(string ...$extra): string
    {
        $classes = ['ptc-body'];
        $sticky = filter_var(
            ptc_get_setting('layout', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if ($sticky) {
            $classes[] = 'has-sticky-header';
        }
        if (theme_is_logged_in()) {
            $classes[] = 'is-logged-in';
        }
        foreach ($extra as $c) {
            $c = trim($c);
            if ($c !== '') {
                $classes[] = $c;
            }
        }
        return htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_header')) {
    function get_header(): void
    {
        $path = __DIR__ . '/header.php';
        try {
            $tp = \CMS\ThemeManager::instance()->getThemePath(PTC_THEME_SLUG) . '/header.php';
            if (file_exists($tp)) {
                $path = $tp;
            }
        } catch (\Throwable) {
            // __DIR__ fallback
        }
        if (file_exists($path)) {
            require $path;
        }
    }
}

if (!function_exists('get_footer')) {
    function get_footer(): void
    {
        $path = __DIR__ . '/footer.php';
        try {
            $tp = \CMS\ThemeManager::instance()->getThemePath(PTC_THEME_SLUG) . '/footer.php';
            if (file_exists($tp)) {
                $path = $tp;
            }
        } catch (\Throwable) {
            // __DIR__ fallback
        }
        if (file_exists($path)) {
            require $path;
        }
    }
}

if (!function_exists('theme_csrf_token')) {
    function theme_csrf_token(string $action = 'form'): string
    {
        return \CMS\Security::instance()->generateToken($action);
    }
}

if (!function_exists('theme_csrf_field')) {
    function theme_csrf_field(string $action = 'form'): void
    {
        $token = theme_csrf_token($action);
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }
}

if (!function_exists('theme_get_flash')) {
    function theme_get_flash(string $type = 'error'): string
    {
        $key = ($type === 'success') ? 'success' : 'error';
        $msg = $_SESSION[$key] ?? '';
        unset($_SESSION[$key]);
        return is_string($msg) ? $msg : '';
    }
}

if (!function_exists('theme_is_logged_in')) {
    function theme_is_logged_in(): bool
    {
        try {
            return \CMS\Auth::instance()->isLoggedIn();
        } catch (\Throwable) {
            return false;
        }
    }
}

// ── 365CMS 3.4 Laufzeit-Helfer (CSP-Nonce, lokale Schriften) ──────────────────

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
    /** true, wenn im Core „Schriften lokal einbinden“ (privacy_use_local_fonts) aktiv ist (OptionStore statt SQL). */
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
        if (($data['_type'] ?? '') === 'post') {
            return theme_post_url($data);
        }
        $slug = ltrim((string) ($data['slug'] ?? ''), '/');

        return rtrim((string) SITE_URL, '/') . '/' . implode('/', array_map('rawurlencode', explode('/', $slug)));
    }
}

if (!function_exists('ptc_archive_url')) {
    /** Kanonische Kategorie-/Schlagwort-URL des Cores (/blog?category=… leitet dorthin per 301 um). */
    function ptc_archive_url(string $type, string $slug): string
    {
        $type = $type === 'tag' ? 'tag' : 'category';
        try {
            if (function_exists('cms_get_archive_path')) {
                return theme_v4_site_url((string) cms_get_archive_path($type, $slug));
            }
        } catch (\Throwable) {
        }

        return theme_v4_site_url('blog') . '?' . $type . '=' . rawurlencode($slug);
    }
}

if (!function_exists('ptc_format_date')) {
    /** Datum aus einem DB-Wert; leer bei fehlendem oder ungültigem Wert (statt Warnung/TypeError). */
    function ptc_format_date(mixed $value, string $format = 'd.m.Y'): string
    {
        $timestamp = is_string($value) && trim($value) !== '' ? strtotime($value) : false;

        return $timestamp !== false ? date($format, $timestamp) : '';
    }
}

// Abmelden: nur noch per POST-Formular mit CSRF-Token (theme_v4_logout_form()); der frühere GET-Link
// theme_logout_url() mit Token in der Adresse ist entfernt.
