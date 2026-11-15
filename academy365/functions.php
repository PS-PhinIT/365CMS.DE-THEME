<?php
declare(strict_types=1);
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('ACADEMY365_THEME_VERSION')) {
    define('ACADEMY365_THEME_VERSION', '4.0.0');
}
if (!defined('ACADEMY365_THEME_SLUG')) {
    define('ACADEMY365_THEME_SLUG', 'academy365');
}
if (!defined('THEME_VERSION')) {
    define('THEME_VERSION', ACADEMY365_THEME_VERSION);
}

// 365CMS 4.x: header.php öffnet kein <main>; die Templates setzen <main id="main"> selbst.
if (!defined('THEME_V4_HEADER_HAS_MAIN')) {
    define('THEME_V4_HEADER_HAS_MAIN', false);
}
if (!defined('THEME_V4_MAIN_ID')) {
    define('THEME_V4_MAIN_ID', 'main');
}
require_once __DIR__ . '/includes/cms-v4-compat.php';

final class Academy365_Theme {
    private static ?self $instance = null;
    public static function instance(): self { if (self::$instance === null) self::$instance = new self(); return self::$instance; }
    private function __construct() { $this->registerHooks(); }

    private function registerHooks(): void {
        \CMS\Hooks::addFilter('register_menu_locations', [$this, 'registerMenuLocations']);

        // Der Core-Mitgliederbereich (/member) ist eine eigene Tabler-Shell und löst ebenfalls `head` aus:
        // dort kein Frontend-Stylesheet, sondern nur die Theme-Palette als --member-*-Variablen.
        if (theme_v4_is_member_area()) {
            \CMS\Hooks::addAction('member_head', [$this, 'outputMemberTokens'], 20);
            return;
        }

        \CMS\Hooks::addAction('head',          [$this, 'enqueueStyles'],         3);
        \CMS\Hooks::addAction('head',          [$this, 'outputCustomStyles'],   15);
        \CMS\Hooks::addAction('before_footer', [$this, 'outputNavigationScript'],99);
    }

    /** Memberdashboard (inkl. Newsletter-Widget) in der Academy365-Palette. */
    public function outputMemberTokens(): void {
        try { $c = \CMS\Services\ThemeCustomizer::instance(); } catch (\Throwable) { $c = null; }
        $color = static function (string $key, string $default) use ($c): string {
            return academy365_css_color($c ? (string) $c->get('colors', $key, $default) : $default, $default);
        };
        echo theme_v4_member_tokens_style([
            'primary'    => $color('primary_color', '#7c3aed'),
            'accent'     => $color('accent_color', '#f59e0b'),
            'bg'         => $color('bg_color', '#faf5ff'),
            'card_bg'    => $color('card_bg_color', '#ffffff'),
            'text'       => $color('text_color', '#1e1b4b'),
            'text_muted' => $color('muted_color', '#6b7280'),
            'border'     => $color('border_color', '#e9d5ff'),
            'focus'      => '#5b21b6',
            'radius'     => '12px',
            'font'       => '"Open Sans", system-ui, -apple-system, "Segoe UI", sans-serif',
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
        $baseUrl   = \CMS\ThemeManager::instance()->getThemeUrl();
        $styleUrl  = htmlspecialchars($baseUrl . '/style.css?v=' . ACADEMY365_THEME_VERSION, ENT_QUOTES, 'UTF-8');
        $v4Url     = htmlspecialchars($baseUrl . '/css/cms-v4.css?v=' . ACADEMY365_THEME_VERSION, ENT_QUOTES, 'UTF-8');
        echo '<link rel="stylesheet" href="' . $styleUrl . '">' . "\n";
        echo '<link rel="stylesheet" href="' . $v4Url . '">' . "\n";
        // Schriften: keine Google-Fonts-Requests (DSGVO). Lokale Schriften liefert die Core-Schriftverwaltung,
        // sonst greift der System-Font-Stack aus style.css.
    }

    private function mapFontChoice(string $fontChoice, string $fallback): string
    {
        return match ($fontChoice) {
            'open-sans'         => '"Open Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'inter'             => 'Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'lato'              => 'Lato, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'nunito-sans'       => '"Nunito Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'raleway'           => 'Raleway, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'poppins'           => 'Poppins, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'nunito'            => 'Nunito, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'montserrat'        => 'Montserrat, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'plus-jakarta-sans' => '"Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            default             => $fallback,
        };
    }

    public function outputCustomStyles(): void {
        try { $c = \CMS\Services\ThemeCustomizer::instance(); } catch (\Throwable $e) { return; }

        $safe = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $color  = static function (string $section, string $key, string $default) use ($c): string {
            return academy365_css_color((string) $c->get($section, $key, $default), $default);
        };
        $num  = static function (string $section, string $key, string $default, float $min, float $max) use ($c): string {
            return academy365_css_number($c->get($section, $key, $default), (float) $default, $min, $max);
        };
        $choice = static function (string $section, string $key, array $allowed, string $default) use ($c): string {
            return academy365_css_choice((string) $c->get($section, $key, $default), $allowed, $default);
        };
        $bool = static function (string $section, string $key, bool $default) use ($c): bool {
            return filter_var($c->get($section, $key, $default), FILTER_VALIDATE_BOOLEAN);
        };

        // Typography
        $bodyFontChoice    = (string) $c->get('typography', 'font_family_base',    'open-sans');
        $headingFontChoice = (string) $c->get('typography', 'font_family_heading', 'raleway');
        $displayFontChoice = (string) $c->get('typography', 'font_family_display', 'plus-jakarta-sans');
        $bodyFont    = $safe($this->mapFontChoice($bodyFontChoice,    '"Open Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif'));
        $headingFont = $safe($this->mapFontChoice($headingFontChoice, 'Raleway, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif'));
        $displayFont = $safe($this->mapFontChoice($displayFontChoice, '"Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif'));

        // Layout switches
        $stickyHeader  = $bool('layout', 'enable_sticky_header',  true);
        $headerShadow  = $bool('header', 'show_header_shadow',    true);
        $courseColumns = (int) $num('layout', 'course_grid_columns', '3', 2, 4);
        if ($courseColumns < 2) { $courseColumns = 2; }
        if ($courseColumns > 4) { $courseColumns = 4; }
        // Card minimum width derived from column setting (visual fallback for narrow viewports)
        $cardMin = match ($courseColumns) { 2 => 320, 4 => 240, default => 280 };

        echo '<style id="ac-custom-vars"' . theme_csp_nonce_attr() . '>:root{';
        // Colors
        echo '--primary-color:'    . $color('colors', 'primary_color',   '#7c3aed') . ';';
        echo '--primary-dark:'     . $color('colors', 'secondary_color', '#5b21b6') . ';';
        echo '--secondary-color:'  . $color('colors', 'secondary_color', '#5b21b6') . ';';
        echo '--accent-color:'     . $color('colors', 'accent_color',    '#f59e0b') . ';';
        echo '--bg-primary:'       . $color('colors', 'bg_color',        '#faf5ff') . ';';
        echo '--bg-secondary:'     . $color('colors', 'card_bg_color',   '#ffffff') . ';';
        echo '--bg-card:'          . $color('colors', 'card_bg_color',   '#ffffff') . ';';
        echo '--text-primary:'     . $color('colors', 'text_color',      '#1e1b4b') . ';';
        echo '--link-color:'       . $color('colors', 'link_color',      '#7c3aed') . ';';
        echo '--muted-color:'      . $color('colors', 'muted_color',     '#6b7280') . ';';
        echo '--border-color:'     . $color('colors', 'border_color',    '#e9d5ff') . ';';
        echo '--badge-free:'       . $color('colors', 'success_color',   '#10b981') . ';';
        echo '--badge-cert:'       . $color('colors', 'accent_color',    '#f59e0b') . ';';
        echo '--badge-new:'        . $color('colors', 'progress_color',  '#3b82f6') . ';';
        echo '--badge-best:'       . $color('colors', 'secondary_color', '#5b21b6') . ';';
        echo '--progress-color:'   . $color('colors', 'progress_color',  '#3b82f6') . ';';
        echo '--success-color:'    . $color('colors', 'success_color',   '#10b981') . ';';
        echo '--cat-tech:'         . $color('colors', 'category_tech',     '#0ea5e9') . ';';
        echo '--cat-business:'     . $color('colors', 'category_business', '#059669') . ';';
        echo '--cat-design:'       . $color('colors', 'category_design',   '#ec4899') . ';';
        echo '--cat-language:'     . $color('colors', 'category_language', '#f59e0b') . ';';
        // Header & Footer chrome
        echo '--header-bg:'        . $color('header', 'header_bg_color',     '#ffffff') . ';';
        echo '--header-text:'      . $color('header', 'header_text_color',   '#1e1b4b') . ';';
        echo '--header-height:'    . $num('header', 'header_height',       '72', 48, 120)      . 'px;';
        echo '--logo-max-height:'  . $num('header', 'logo_max_height',     '44', 24, 100)      . 'px;';
        echo '--footer-bg:'        . $color('footer', 'footer_bg_color',     '#1e1b4b') . ';';
        echo '--footer-text:'      . $color('footer', 'footer_text_color',   '#c4b5fd') . ';';
        echo '--footer-link:'      . $color('footer', 'footer_link_color',   '#ede9fe') . ';';
        // Typography
        echo '--font-body:'        . $bodyFont    . ';';
        echo '--font-heading:'     . $headingFont . ';';
        echo '--font-ui:'          . $displayFont . ';';
        echo '--font-size-base:'   . $num('typography', 'font_size_base',   '16', 12, 24)   . 'px;';
        echo '--line-height-base:' . $num('typography', 'line_height_base', '1.7', 1.2, 2.5) . ';';
        echo '--font-weight-heading:' . $choice('typography', 'font_weight_heading', ['600', '700', '800'], '700') . ';';
        // Layout
        echo '--container-max:'    . $num('layout', 'container_width',  '1240', 800, 1920) . 'px;';
        echo '--content-padding:'  . $num('layout', 'content_padding',  '2', 0.5, 6)    . 'rem;';
        echo '--radius-md:'        . $num('layout', 'border_radius',    '12', 0, 32)   . 'px;';
        echo '--section-spacing:'  . $num('layout', 'section_spacing',  '4.5', 1, 12)  . 'rem;';
        echo '--course-grid-min:'  . $cardMin . 'px;';
        // Buttons
        echo '--btn-radius:'       . $num('buttons', 'button_border_radius', '8', 0, 50)   . 'px;';
        echo '--btn-padding-x:'    . $num('buttons', 'button_padding_x',    '1.75', 0.5, 4) . 'rem;';
        echo '--btn-padding-y:'    . $num('buttons', 'button_padding_y',    '0.75', 0.25, 2) . 'rem;';
        echo '--btn-weight:'       . $choice('buttons', 'button_font_weight', ['600', '700'], '700') . ';';
        echo '--btn-transform:'    . $choice('buttons', 'button_transform', ['none', 'capitalize'], 'none') . ';';
        // Tokens used by focus rings & sticky header
        echo '--focus-ring:'       . $color('colors', 'secondary_color', '#5b21b6') . ';'; // Gold erreicht auf Weiß nur 2,2:1
        echo '--header-shadow:'    . ($headerShadow ? '0 1px 4px rgba(124,58,237,.08)' : 'none') . ';';
        echo '--header-position:'  . ($stickyHeader ? 'fixed' : 'absolute') . ';';
        echo '}';

        $custom = academy365_sanitize_custom_css((string) $c->get('advanced', 'custom_css', ''));
        if (trim($custom) !== '') {
            echo $custom;
        }
        echo '</style>' . "\n";
    }

    public function outputNavigationScript(): void {
        $baseUrl = \CMS\ThemeManager::instance()->getThemeUrl();
        $jsUrl   = htmlspecialchars($baseUrl . '/js/navigation.js?v=' . ACADEMY365_THEME_VERSION, ENT_QUOTES, 'UTF-8');
        echo '<script src="' . $jsUrl . '" defer></script>' . "\n";
    }
}
Academy365_Theme::instance();

function academy365_css_color(string $value, string $fallback): string
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

function academy365_css_number(mixed $value, float $default, float $min, float $max): string
{
    $number = is_numeric($value) ? (float) $value : $default;
    $number = max($min, min($max, $number));

    return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
}

function academy365_css_choice(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function academy365_safe_url(string $url, string $fallback = ''): string
{
    if (function_exists('theme_safe_url')) {
        return theme_safe_url($url, $fallback);
    }

    $candidate = trim($url);
    if ($candidate === '') {
        return $fallback;
    }

    if (str_starts_with($candidate, '//')) {
        return $fallback;
    }

    if (str_starts_with($candidate, '/')) {
        return preg_replace('/[\x00-\x1F\x7F]+/u', '', $candidate) ?? $fallback;
    }

    $scheme = parse_url($candidate, PHP_URL_SCHEME);
    if (in_array(strtolower((string) $scheme), ['http', 'https'], true) && filter_var($candidate, FILTER_VALIDATE_URL)) {
        return $candidate;
    }

    return $fallback;
}

function academy365_sanitize_custom_css(string $css): string
{
    $clean = preg_replace('/<\/?style\b[^>]*>/i', '', $css) ?? '';
    $clean = preg_replace('/@import\b[^;]*;?/i', '', $clean) ?? '';
    $clean = preg_replace('/expression\s*\([^)]*\)/i', '', $clean) ?? '';
    $protocolSinkPattern = '/url\s*\(\s*[\'\"]?\s*java' . 'script:[^)]+\)/i';
    $clean = preg_replace($protocolSinkPattern, '', $clean) ?? '';
    $clean = preg_replace('/\b(?:behavior|binding)\s*:[^;]+;?/i', '', $clean) ?? '';

    return str_replace('</', '<\/', $clean);
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
    /**
     * @param array<string, scalar> $params  Query parameters (merged with $query)
     * @param array<string, scalar> $query
     */
    function theme_route_url(string $name, array $params = [], array $query = [], string $fallback = ''): string
    {
        $base = rtrim((string) (defined('SITE_URL') ? SITE_URL : ''), '/');
        $path = match ($name) {
            'home'             => '/',
            'search'           => '/search',
            'login'            => '/login',
            'register'         => '/register',
            'member',
            'member-dashboard' => '/member',
            'courses'          => '/courses',
            'tutors'           => '/tutors',
            'tutor-register'   => '/tutor-register',
            'pro'              => '/pro',
            default            => '/' . ltrim($name, '/'),
        };
        $url = $base . ($path === '/' ? '/' : $path);
        $allQuery = array_merge($params, $query);
        if ($allQuery !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($allQuery, '', '&', PHP_QUERY_RFC3986);
        }

        return academy365_safe_url($url, $fallback !== '' ? $fallback : $url);
    }
}

if (!function_exists('theme_nav_menu')) {
    /**
     * Menü einer Position als Liste. Mit `$mainNavContext` (`desktop`/`mobile`) folgen direkt nach den
     * Menüeinträgen die optionalen Einträge der Core-Module (Hook `main_nav`: Downloads, Knowledgebase, Newsletter).
     */
    function theme_nav_menu(string $location, string $mainNavContext = ''): void
    {
        try {
            $items = \CMS\ThemeManager::instance()->getMenu($location);
        } catch (\Throwable) {
            $items = [];
        }

        $coreItems = '';
        if ($mainNavContext !== '' && function_exists('theme_v4_main_nav')) {
            ob_start();
            theme_v4_main_nav($mainNavContext, ['wrap' => 'li', 'active_item_class' => 'current-menu-item']);
            $coreItems = (string) ob_get_clean();
        }
        if ($items === [] && $coreItems === '') {
            return;
        }

        echo '<ul>';
        foreach ($items as $item) {
            $rawUrl = (string) ($item['url'] ?? '#');
            $label  = (string) ($item['label'] ?? $item['title'] ?? '');

            $sanitizedUrl = filter_var($rawUrl, FILTER_VALIDATE_URL)
                ? $rawUrl
                : (str_starts_with($rawUrl, '/') || str_starts_with($rawUrl, '#')
                    ? $rawUrl
                    : '#');
            $isActive = $sanitizedUrl !== '#' && !str_starts_with($sanitizedUrl, '#') && theme_v4_is_current_path($sanitizedUrl);

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
            $tp = \CMS\ThemeManager::instance()->getThemePath(ACADEMY365_THEME_SLUG) . '/header.php';
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
            $tp = \CMS\ThemeManager::instance()->getThemePath(ACADEMY365_THEME_SLUG) . '/footer.php';
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

// ── 365CMS-Laufzeit-Helfer (CSP-Nonce, lokale Schriften) ──────────────────

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
