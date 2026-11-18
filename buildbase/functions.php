<?php
declare(strict_types=1);

/**
 * BuildBase Theme – Bootstrap & Customizer Integration
 *
 * Construction / trade / project-delivery theme for 365CMS 4.0.0 / 4.1.0 on PHP 8.4.1.
 *
 * @package BuildBase_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('BUILDBASE_THEME_VERSION')) {
    define('BUILDBASE_THEME_VERSION', '4.1.0');
}
if (!defined('BUILDBASE_THEME_SLUG')) {
    define('BUILDBASE_THEME_SLUG', 'buildbase');
}
if (!defined('THEME_VERSION')) {
    define('THEME_VERSION', BUILDBASE_THEME_VERSION);
}

// 365CMS 4.x: header.php öffnet kein <main>; die Templates setzen <main id="main"> selbst.
if (!defined('THEME_V4_HEADER_HAS_MAIN')) {
    define('THEME_V4_HEADER_HAS_MAIN', false);
}
if (!defined('THEME_V4_MAIN_ID')) {
    define('THEME_V4_MAIN_ID', 'main');
}
require_once __DIR__ . '/includes/cms-v4-compat.php';

final class BuildBase_Theme {
    private static ?self $instance = null;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->registerHooks();
    }

    private function registerHooks(): void {
        \CMS\Hooks::addFilter('register_menu_locations', [$this, 'registerMenuLocations']);

        // Der Core-Mitgliederbereich (/member) ist eine eigene Tabler-Shell und löst ebenfalls `head` aus:
        // dort kein Frontend-Stylesheet und keine Theme-Scripts, sondern nur die Theme-Palette als --member-*.
        if (theme_v4_is_member_area()) {
            \CMS\Hooks::addAction('member_head', [$this, 'outputMemberTokens'], 20);
            return;
        }

        \CMS\Hooks::addAction('head',          [$this, 'enqueueStyles'],            1);
        \CMS\Hooks::addAction('head',          [$this, 'outputCustomStyles'],       15);
        \CMS\Hooks::addAction('before_footer', [$this, 'outputNavigationScript'],   99);
    }

    /** Memberdashboard (inkl. Newsletter-Widget) in der BuildBase-Palette; das Theme hat keinen Dark Mode. */
    public function outputMemberTokens(): void {
        try {
            $c = \CMS\Services\ThemeCustomizer::instance();
        } catch (\Throwable) {
            $c = null;
        }
        $color = static fn(string $key, string $default): string =>
            buildbase_css_color($c !== null ? (string) $c->get('colors', $key, $default) : $default, $default);

        echo theme_v4_member_tokens_style([
            'primary'    => $color('primary_color', '#b45309'),
            'accent'     => $color('accent_color', '#d97706'),
            'bg'         => $color('bg_color', '#fafaf9'),
            'card_bg'    => $color('card_bg_color', '#ffffff'),
            'text'       => $color('text_color', '#1c1917'),
            'text_muted' => $color('muted_color', '#6b645f'),
            'border'     => $color('border_color', '#d6d3d1'),
            'focus'      => $color('secondary_color', '#92400e'),
            'radius'     => buildbase_css_number($c !== null ? $c->get('layout', 'border_radius', '4') : '4', 4, 0, 16) . 'px',
            'font'       => $this->mapFontChoice($c !== null ? (string) $c->get('typography', 'font_family_base', 'roboto') : 'roboto', self::FONT_SANS),
        ]);
    }

    /**
     * Menüpositionen über den Core-Filter register_menu_locations anmelden
     * (ThemeManager::registerMenuLocation() existiert in 365CMS nicht).
     *
     * @param array<int, array{slug:string,label:string}> $locations
     * @return array<int, array{slug:string,label:string}>
     */
    public function registerMenuLocations(array $locations): array {
        $locations[] = ['slug' => 'primary-nav',  'label' => 'Hauptmenü'];
        $locations[] = ['slug' => 'footer-nav',   'label' => 'Fußzeilen-Navigation'];
        $locations[] = ['slug' => 'footer-legal', 'label' => 'Rechtliche Links'];
        return $locations;
    }

    public function enqueueStyles(): void {
        $themeUrl = rtrim(theme_v4_theme_url(__DIR__), '/');
        $ver      = rawurlencode(BUILDBASE_THEME_VERSION);
        echo '<link rel="stylesheet" href="' . htmlspecialchars($themeUrl . '/style.css?v=' . $ver, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        // Gemeinsame 4.x-Integrationsstile (Downloads, Feeds, Formulare, Knowledgebase, Newsletter …) nach style.css.
        echo '<link rel="stylesheet" href="' . htmlspecialchars($themeUrl . '/css/cms-v4.css?v=' . $ver, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        // Schriften: keine Google-Fonts-Requests (DSGVO). Lokale Schriften liefert die Core-Schriftverwaltung,
        // sonst greift der System-Font-Stack.
    }

    /** System-Fallbacks hinter jeder Schriftwahl (keine externen Schriften). */
    private const FONT_SANS = 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
    private const FONT_CONDENSED = '"Arial Narrow", system-ui, -apple-system, "Segoe UI", sans-serif';

    private function mapFontChoice(string $fontChoice, string $fallback): string {
        return match ($fontChoice) {
            'roboto'           => 'Roboto, ' . self::FONT_SANS,
            'open-sans'        => '"Open Sans", ' . self::FONT_SANS,
            'lato'             => 'Lato, ' . self::FONT_SANS,
            'inter'            => 'Inter, ' . self::FONT_SANS,
            'roboto-condensed' => '"Roboto Condensed", ' . self::FONT_CONDENSED,
            'bebas-neue'       => '"Bebas Neue", ' . self::FONT_CONDENSED,
            'oswald'           => 'Oswald, ' . self::FONT_CONDENSED,
            default            => $fallback,
        };
    }

    private function sanitizeCustomCss(string $raw): string {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return '';
        }

        $clean = preg_replace('#<\/?\s*style\b[^>]*>#i', '', $trimmed) ?? '';
        $clean = preg_replace('/@import\b[^;]*;?/i', '', $clean) ?? '';
        $clean = preg_replace('/expression\s*\([^)]*\)/i', '', $clean) ?? '';
        $protocolSinkPattern = '/url\s*\(\s*[\'\"]?\s*java' . 'script:[^)]+\)/i';
        $clean = preg_replace($protocolSinkPattern, '', $clean) ?? '';
        $clean = preg_replace('/\b(?:behavior|binding)\s*:[^;]+;?/i', '', $clean) ?? '';

        return str_replace('</', '<\/', $clean);
    }

    public function outputCustomStyles(): void {
        try {
            $c = \CMS\Services\ThemeCustomizer::instance();
        } catch (\Throwable $e) {
            return;
        }

        $color = fn(string $s, string $k, string $d): string =>
            buildbase_css_color((string) $c->get($s, $k, $d), $d);
        $num = fn(string $s, string $k, string $d, float $min, float $max): string =>
            buildbase_css_number($c->get($s, $k, $d), (float) $d, $min, $max);
        $choice = fn(string $s, string $k, array $allowed, string $d): string =>
            buildbase_css_choice((string) $c->get($s, $k, $d), $allowed, $d);

        $baseFontChoice    = (string) $c->get('typography', 'font_family_base',    'roboto');
        $headingFontChoice = (string) $c->get('typography', 'font_family_heading', 'roboto-condensed');
        $badgeFontChoice   = (string) $c->get('typography', 'font_family_badge',   'oswald');

        // Feste Stacks aus mapFontChoice() – nicht HTML-escapen: &quot; im <style> machte die Deklaration
        // ungültig (die Überschriften fielen auf die geerbte Schrift zurück).
        $baseFont    = $this->mapFontChoice($baseFontChoice,    'Roboto, ' . self::FONT_SANS);
        $headingFont = $this->mapFontChoice($headingFontChoice, '"Roboto Condensed", ' . self::FONT_CONDENSED);
        $badgeFont   = $this->mapFontChoice($badgeFontChoice,   'Oswald, ' . self::FONT_CONDENSED);

        echo '<style id="bb-custom-vars"' . theme_csp_nonce_attr() . '>:root{';
        // Colors
        echo '--primary-color:'     . $color('colors', 'primary_color',    '#b45309') . ';';
        echo '--primary-dark:'      . $color('colors', 'secondary_color',  '#92400e') . ';';
        echo '--secondary-color:'   . $color('colors', 'secondary_color',  '#92400e') . ';';
        echo '--accent-color:'      . $color('colors', 'accent_color',     '#d97706') . ';';
        echo '--safety-color:'      . $color('colors', 'safety_color',     '#f59e0b') . ';';
        echo '--bg-primary:'        . $color('colors', 'bg_color',         '#fafaf9') . ';';
        // section_dark_bg ist die dunkle Sektionsfläche; bisher landete sie in --bg-secondary (helle Alt-Fläche)
        // und machte .bb-section--alt dunkel bei dunkler Schrift (1,2:1).
        echo '--section-dark:'      . $color('colors', 'section_dark_bg',  '#292524') . ';';
        echo '--bg-card:'           . $color('colors', 'card_bg_color',    '#ffffff') . ';';
        echo '--text-primary:'      . $color('colors', 'text_color',       '#1c1917') . ';';
        echo '--text-secondary:'    . $color('colors', 'muted_color',      '#6b645f') . ';';
        echo '--muted-color:'       . $color('colors', 'muted_color',      '#6b645f') . ';';
        echo '--border-color:'      . $color('colors', 'border_color',     '#d6d3d1') . ';';
        echo '--link-color:'        . $color('colors', 'link_color',       '#b45309') . ';';
        echo '--success-color:'     . $color('colors', 'success_color',    '#16a34a') . ';';
        echo '--error-color:'       . $color('colors', 'error_color',      '#dc2626') . ';';
        // Fokusrahmen auf hellen Flächen in Dunkelbraun (6,8:1 auf --bg-primary; Akzent-Gold nur 3,1:1),
        // auf dunklen Flächen in Sicherheits-Amber (--focus-ring-on-dark, style.css).
        echo '--focus-ring:'        . $color('colors', 'secondary_color',  '#92400e') . ';';
        echo '--focus-ring-on-dark:' . $color('colors', 'safety_color',    '#f59e0b') . ';';

        // Header
        echo '--header-bg:'         . $color('header', 'header_bg_color',   '#292524') . ';';
        echo '--header-text:'       . $color('header', 'header_text_color', '#fafaf9') . ';';
        echo '--header-height:'     . $num('header', 'header_height',     '68', 48, 120) . 'px;';
        echo '--logo-max-height:'   . $num('header', 'logo_max_height',   '44', 24, 100) . 'px;';

        // Footer
        echo '--footer-bg:'         . $color('footer', 'footer_bg_color',   '#1c1917') . ';';
        echo '--footer-text:'       . $color('footer', 'footer_text_color', '#a8a29e') . ';';
        echo '--footer-link:'       . $color('footer', 'footer_link_color', '#d6d3d1') . ';';

        // Layout
        echo '--container-max:'     . $num('layout', 'container_width',  '1200', 800, 1920) . 'px;';
        echo '--content-padding:'   . $num('layout', 'content_padding',  '2', 0.5, 6)    . 'rem;';
        echo '--radius-sm:'         . $num('layout', 'border_radius',    '4', 0, 16)    . 'px;';
        echo '--section-spacing:'   . $num('layout', 'section_spacing',  '4', 1, 12)    . 'rem;';

        // Hero gradient
        echo '--hero-gradient-start:' . $color('build_hero', 'hero_gradient_start', '#292524') . ';';
        echo '--hero-gradient-end:'   . $color('build_hero', 'hero_gradient_end',   '#44403c') . ';';

        // Buttons
        echo '--btn-radius:'         . $num('buttons', 'button_border_radius', '4', 0, 50)    . 'px;';
        echo '--btn-padding-x:'      . $num('buttons', 'button_padding_x',     '1.75', 0.5, 4) . 'rem;';
        echo '--btn-padding-y:'      . $num('buttons', 'button_padding_y',     '0.75', 0.25, 2) . 'rem;';
        echo '--btn-font-weight:'    . $choice('buttons', 'button_font_weight', ['600', '700'], '700')  . ';';
        echo '--btn-transform:'      . $choice('buttons', 'button_transform', ['none', 'uppercase'], 'uppercase') . ';';

        // Typography
        echo '--font-body:'          . $baseFont    . ';';
        echo '--font-heading:'       . $headingFont . ';';
        echo '--font-condensed:'     . $badgeFont   . ';';
        echo '--font-size-base:'     . $num('typography', 'font_size_base',     '16', 14, 20)  . 'px;';
        echo '--line-height-base:'   . $num('typography', 'line_height_base',   '1.6', 1.2, 2.5) . ';';
        echo '--font-weight-heading:'. $choice('typography', 'font_weight_heading', ['700', '800', '900'], '800') . ';';
        echo '}';

        $custom = (string) $c->get('advanced', 'custom_css', '');
        $safeCustom = $this->sanitizeCustomCss($custom);
        if ($safeCustom !== '') {
            echo $safeCustom;
        }
        echo '</style>' . "\n";
    }

    public function outputNavigationScript(): void {
        $themeUrl = theme_v4_theme_url(__DIR__);
        $src = htmlspecialchars(
            rtrim($themeUrl, '/') . '/js/navigation.js?v=' . BUILDBASE_THEME_VERSION,
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<script src="' . $src . '" defer></script>' . "\n";
    }
}

BuildBase_Theme::instance();

function buildbase_css_color(string $value, string $fallback): string
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

function buildbase_css_number(mixed $value, float $default, float $min, float $max): string
{
    $number = is_numeric($value) ? (float) $value : $default;
    $number = max($min, min($max, $number));

    return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
}

function buildbase_css_choice(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function buildbase_safe_url(string $url, string $fallback = ''): string
{
    if (function_exists('theme_safe_url')) {
        return theme_safe_url($url, $fallback);
    }

    $candidate = trim($url);
    if ($candidate === '') {
        return $fallback;
    }
    // `//host` und `/\\host` (Browser werten „\\“ wie „/“) sind fremde, protokollrelative Adressen.
    if (str_starts_with($candidate, '//') || str_contains($candidate, '\\') || preg_match('/[\x00-\x1F\x7F]/', $candidate) === 1) {
        return $fallback;
    }

    if (str_starts_with($candidate, '/')) {
        return $candidate;
    }

    $scheme = parse_url($candidate, PHP_URL_SCHEME);
    if (in_array(strtolower((string) $scheme), ['http', 'https'], true) && filter_var($candidate, FILTER_VALIDATE_URL)) {
        return $candidate;
    }

    return $fallback;
}

// ── Theme template helpers ───────────────────────────────────────────────────

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

if (!function_exists('theme_route_url')) {
    function theme_route_url(string $name, array $params = [], array $query = [], string $fallback = ''): string
    {
        $base = rtrim((string) (defined('SITE_URL') ? SITE_URL : ''), '/');
        $path = match ($name) {
            'home'       => '/',
            'search'     => '/search',
            'login'      => '/login',
            'register'   => '/register',
            'member'     => '/member',
            'handwerker' => '/handwerker',
            'projekte'   => '/projekte',
            'angebot'    => '/angebot',
            default      => '/' . ltrim($name, '/'),
        };
        $url = $base . ($path === '/' ? '/' : $path);
        $allQuery = array_merge($params, $query);
        if ($allQuery !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($allQuery, '', '&', PHP_QUERY_RFC3986);
        }

        // Rückfall nie die ungeprüfte URL selbst.
        return buildbase_safe_url($url, $fallback !== '' ? $fallback : $base . '/');
    }
}

if (!function_exists('theme_nav_menu')) {
    /**
     * Menü einer Position als Liste. Mit `$mainNavContext` (`desktop`/`mobile`) folgen direkt nach den
     * Menüeinträgen die Einträge der Core-Module (Hook `main_nav`: Downloads, Knowledgebase, Newsletter) –
     * auch wenn im Admin (noch) kein Menü gepflegt ist.
     */
    function theme_nav_menu(string $location, string $mainNavContext = ''): void
    {
        try {
            $items = \CMS\ThemeManager::instance()->getMenu($location);
        } catch (\Throwable) {
            $items = [];
        }
        $items = is_array($items) ? $items : [];

        $coreItems = '';
        if ($mainNavContext !== '') {
            ob_start();
            theme_v4_main_nav($mainNavContext, ['wrap' => 'li', 'active_item_class' => 'current-menu-item']);
            $coreItems = (string) ob_get_clean();
        }
        if ($items === [] && $coreItems === '') {
            return;
        }

        echo '<ul>';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $rawUrl = (string) ($item['url'] ?? '#');
            // Der Core liefert die Beschriftung im Feld `title`, ältere Menüs in `label`.
            $label  = (string) ($item['label'] ?? $item['title'] ?? '');

            $trimmedUrl   = trim($rawUrl);
            $sanitizedUrl = preg_match('/^(?:mailto:[^\s@<>"]+@[^\s@<>"]+|tel:\+?[0-9][0-9\s().-]{2,31})$/i', $trimmedUrl) === 1
                ? $trimmedUrl
                : buildbase_safe_url($trimmedUrl, str_starts_with($trimmedUrl, '#') ? $trimmedUrl : '#');
            if (str_starts_with($sanitizedUrl, '#') && preg_match('/^#[A-Za-z][A-Za-z0-9_-]*$/', $sanitizedUrl) !== 1) {
                $sanitizedUrl = '#';
            }
            // Aktiv: gleicher Pfad (Startseite nur exakt); Anker, mailto: und tel: nie.
            $isActive = !str_contains($sanitizedUrl, '#')
                && preg_match('/^(?:mailto|tel):/i', $sanitizedUrl) !== 1
                && theme_v4_is_current_path($sanitizedUrl);

            $target = (!empty($item['target']) && $item['target'] === '_blank')
                ? ' target="_blank" rel="noopener noreferrer"'
                : '';

            $cls = $isActive ? ' class="current-menu-item"' : '';
            echo '<li' . $cls . '><a href="'
                . htmlspecialchars($sanitizedUrl, ENT_QUOTES, 'UTF-8')
                . '"' . $target . ($isActive ? ' aria-current="page"' : '') . '>'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                . '</a></li>';
        }
        echo $coreItems;
        echo '</ul>';
    }
}

if (!function_exists('get_header')) {
    function get_header(): void
    {
        $path = __DIR__ . '/header.php';
        try {
            $tp = \CMS\ThemeManager::instance()->getThemePath(BUILDBASE_THEME_SLUG) . '/header.php';
            if (file_exists($tp)) {
                $path = $tp;
            }
        } catch (\Throwable) {
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
            $tp = \CMS\ThemeManager::instance()->getThemePath(BUILDBASE_THEME_SLUG) . '/footer.php';
            if (file_exists($tp)) {
                $path = $tp;
            }
        } catch (\Throwable) {
        }
        if (file_exists($path)) {
            require $path;
        }
    }
}

// ── 365CMS-Laufzeit-Helfer (CSP-Nonce, lokale Schriften) ──────────────────────

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
