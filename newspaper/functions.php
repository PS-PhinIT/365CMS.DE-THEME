<?php
declare(strict_types=1);

/**
 * CMS Newspaper Theme – Bootstrap & Customizer Integration
 *
 * Editorial news / magazine theme for 365CMS 4.0.0 – 4.1.0 on PHP 8.4.1.
 *
 * @package CmsNewspaper_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('CMSNEWSPAPER_THEME_VERSION')) {
    define('CMSNEWSPAPER_THEME_VERSION', '2.1.0');
}

// Marketplace-Slug (index.json); der Theme-Ordner heißt `newspaper`. URLs/Pfade daher immer über das aktive Theme.
if (!defined('CMSNEWSPAPER_THEME_SLUG')) {
    define('CMSNEWSPAPER_THEME_SLUG', 'cms-newspaper');
}

// 365CMS 4.x: header.php öffnet <main id="main-content">; Module und Fehlerseiten setzen kein zweites <main>.
if (!defined('THEME_V4_HEADER_HAS_MAIN')) {
    define('THEME_V4_HEADER_HAS_MAIN', true);
}
if (!defined('THEME_V4_MAIN_ID')) {
    define('THEME_V4_MAIN_ID', 'main-content');
}
require_once __DIR__ . '/includes/cms-v4-compat.php';

/**
 * Newspaper Theme Singleton
 */
final class CmsNewspaper_Theme
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
        // Menü-Positionen ausschließlich über den Filter (ThemeManager::registerMenuLocation() gibt es in 4.0 nicht mehr).
        \CMS\Hooks::addFilter('register_menu_locations', [$this, 'registerMenuLocations']);

        // Der Core-Mitgliederbereich (/member) ist eine eigene Shell und löst ebenfalls `head` aus:
        // dort weder Frontend-Stylesheet noch Customizer-Variablen oder Theme-Script, nur die Palette als --member-*.
        if (theme_v4_is_member_area()) {
            \CMS\Hooks::addAction('member_head', [$this, 'outputMemberTokens'], 20);
            return;
        }

        // Assets in <head>
        \CMS\Hooks::addAction('head', [$this, 'enqueueStyles'],      1);
        \CMS\Hooks::addAction('head', [$this, 'outputMetaTags'],     10);
        \CMS\Hooks::addAction('head', [$this, 'outputCustomStyles'], 15);

        // Deferred navigation script
        \CMS\Hooks::addAction('before_footer', [$this, 'enqueueScripts'], 99);
    }

    // ── Assets ───────────────────────────────────────────────────────────────

    public function enqueueStyles(): void
    {
        $themeUrl = $this->themeBaseUrl();
        $version  = rawurlencode(CMSNEWSPAPER_THEME_VERSION);
        $href     = htmlspecialchars($themeUrl . '/style.css?v=' . $version, ENT_QUOTES, 'UTF-8');
        $v4Href   = htmlspecialchars($themeUrl . '/css/cms-v4.css?v=' . $version, ENT_QUOTES, 'UTF-8');
        echo '<link rel="stylesheet" href="' . $href . '">' . "\n";
        echo '<link rel="stylesheet" href="' . $v4Href . '">' . "\n";
        // Schriften: keine Google-Fonts-Requests (DSGVO). Lokale Schriften liefert die Core-Schriftverwaltung,
        // sonst greifen die System-Fallbacks der Font-Stacks.
    }

    /** Memberbereich (inkl. Newsletter-Widget) in der Newspaper-Palette – Customizer-Werte, sanitisiert. */
    public function outputMemberTokens(): void
    {
        $c = fn(string $key, string $default): string => news_css_color($this->customizerGet('colors', $key, $default), $default);
        echo theme_v4_member_tokens_style([
            'primary'    => $c('ink_color', '#0a0b0c'),
            'accent'     => $c('accent_color', '#c22510'),
            'bg'         => $c('paper_color', '#f8f9fa'),
            'card_bg'    => $c('surface_color', '#ffffff'),
            'text'       => $c('ink_color', '#0a0b0c'),
            'text_muted' => $c('ink_dim', '#6b6c6d'),
            'border'     => $c('border_color', '#e4e5e7'),
            'focus'      => $c('accent_color', '#c22510'),
            'radius'     => news_css_number($this->customizerGet('layout', 'border_radius', 6), 6, 0, 12) . 'px',
            'font'       => $this->mapFontChoice((string) $this->customizerGet('typography', 'font_family_base', 'source-sans'), 'body'),
        ]);
    }

    public function enqueueScripts(): void
    {
        $themeUrl = $this->themeBaseUrl();
        $src      = htmlspecialchars(
            $themeUrl . '/js/navigation.js?v=' . rawurlencode(CMSNEWSPAPER_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<script src="' . $src . '" defer></script>' . "\n";
    }

    // ── Meta ─────────────────────────────────────────────────────────────────

    /**
     * Nur noch theme-color. Beschreibung, Robots, Canonical und Open Graph liefert das Core-SEO in header.php
     * (theme_v4_head_tags()) – bisher gab das Theme zusätzlich eine site-weite Beschreibung, `og:url` = Startseite
     * und `robots: index,follow` aus, auch auf Vorschau- und Fehlerseiten.
     */
    public function outputMetaTags(): void
    {
        $themeColor = news_css_color($this->customizerGet('colors', 'ink_color', '#0a0b0c'), '#0a0b0c');
        echo '<meta name="theme-color" content="' . htmlspecialchars($themeColor, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    // ── Customizer-getriebene CSS-Variablen + custom_css ─────────────────────

    public function outputCustomStyles(): void
    {
        // Werte validiert statt nur escaped: Farben als Hex/RGB/HSL, Zahlen geklemmt, Auswahl gegen Liste.
        $colorKeys = [
            'ink_color' => '#0a0b0c', 'ink_mid' => '#3a3b3c', 'ink_dim' => '#6b6c6d', 'ink_ghost' => '#6f7071',
            'paper_color' => '#f8f9fa', 'surface_color' => '#ffffff', 'accent_color' => '#c22510',
            'accent_hover' => '#9f1e0d', 'border_color' => '#e4e5e7', 'live_color' => '#22c55e',
        ];
        $numberLimits = [
            'typography' => ['font_size_base' => [16, 14, 20], 'line_height_base' => [1.6, 1.3, 2]],
            'layout'     => ['container_width' => [1040, 960, 1440], 'section_spacing' => [4, 2, 8], 'border_radius' => [6, 0, 12]],
            'buttons'    => ['button_border_radius' => [4, 0, 12], 'button_padding_x' => [1.5, 0.5, 3], 'button_padding_y' => [0.75, 0.25, 1.5]],
        ];
        $p = function (string $g, string $k, string $d) use ($colorKeys, $numberLimits): string {
            $raw = $this->customizerGet($g, $k, $d);
            if ($g === 'colors' && isset($colorKeys[$k])) {
                return news_css_color($raw, $colorKeys[$k]);
            }
            if (isset($numberLimits[$g][$k])) {
                [$default, $min, $max] = $numberLimits[$g][$k];
                return news_css_number($raw, (float) $default, (float) $min, (float) $max);
            }
            if ($g === 'buttons' && $k === 'button_transform') {
                $value = (string) $raw;
                return in_array($value, ['uppercase', 'none', 'capitalize', 'lowercase'], true) ? $value : $d;
            }

            return htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8');
        };

        $displayChoice = (string) $this->customizerGet('typography', 'font_family_display', 'playfair');
        $baseChoice    = (string) $this->customizerGet('typography', 'font_family_base',    'source-sans');
        $monoChoice    = (string) $this->customizerGet('typography', 'font_family_mono',    'plex-mono');

        // Feste Font-Stacks aus mapFontChoice() (keine Nutzereingabe). Nicht HTML-escapen: Entities wie &quot; werden
        // in <style> nicht dekodiert – bisher war --news-font-* dadurch ungültig und die Display-Schrift griff nie.
        $displayFont = $this->mapFontChoice($displayChoice, 'display');
        $baseFont    = $this->mapFontChoice($baseChoice,    'body');
        $monoFont    = $this->mapFontChoice($monoChoice,    'mono');

        echo '<style id="news-custom-vars"' . theme_csp_nonce_attr() . '>:root{';

        // Colors – editoriale Restpalette
        echo '--news-ink:'         . $p('colors', 'ink_color',     '#0a0b0c') . ';';
        echo '--news-ink-mid:'     . $p('colors', 'ink_mid',       '#3a3b3c') . ';';
        echo '--news-ink-dim:'     . $p('colors', 'ink_dim',       '#6b6c6d') . ';';
        echo '--news-ink-ghost:'   . $p('colors', 'ink_ghost',     '#6f7071') . ';';
        echo '--news-paper:'       . $p('colors', 'paper_color',   '#f8f9fa') . ';';
        echo '--news-surface:'     . $p('colors', 'surface_color', '#ffffff') . ';';
        echo '--news-accent:'      . $p('colors', 'accent_color',  '#c22510') . ';';
        echo '--news-accent-hover:'. $p('colors', 'accent_hover',  '#9f1e0d') . ';';
        echo '--news-rule:'        . $p('colors', 'border_color',  '#e4e5e7') . ';';
        echo '--news-live:'        . $p('colors', 'live_color',    '#22c55e') . ';';
        echo '--focus-ring:'       . $p('colors', 'accent_color',  '#c22510') . ';';

        // Typography
        echo '--news-font-display:'. $displayFont . ';';
        echo '--news-font-body:'   . $baseFont    . ';';
        echo '--news-font-mono:'   . $monoFont    . ';';
        echo '--news-font-size-base:' . $p('typography', 'font_size_base',   '16')   . 'px;';
        echo '--news-line-height:'    . $p('typography', 'line_height_base', '1.6')  . ';';

        // Layout
        echo '--news-max-width:'   . $p('layout', 'container_width', '1040') . 'px;';
        echo '--news-section-pad:' . $p('layout', 'section_spacing', '4')    . 'rem;';
        echo '--news-radius:'      . $p('layout', 'border_radius',   '6')    . 'px;';

        // Buttons
        echo '--news-btn-radius:'    . $p('buttons', 'button_border_radius', '4')   . 'px;';
        echo '--news-btn-padding-x:' . $p('buttons', 'button_padding_x',     '1.5') . 'rem;';
        echo '--news-btn-padding-y:' . $p('buttons', 'button_padding_y',     '0.75'). 'rem;';
        echo '--news-btn-transform:' . $p('buttons', 'button_transform',     'uppercase') . ';';

        echo '}';

        // Sticky-Header opt-out
        $stickyHeader = filter_var(
            $this->customizerGet('header', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$stickyHeader) {
            echo '.news-header{position:static;box-shadow:none;}';
        }

        // Custom CSS (defensiv gegen </style>-Injection)
        $custom = $this->sanitizeCustomCss((string) $this->customizerGet('advanced', 'custom_css', ''));
        if ($custom !== '') {
            echo $custom;
        }

        echo '</style>' . "\n";
    }

    // ── Menü-Positionen ───────────────────────────────────────────────────────

    /**
     * @param mixed $locations
     * @return array<int, mixed>
     */
    public function registerMenuLocations(mixed $locations): array
    {
        $locations = is_array($locations) ? $locations : [];
        $existing  = array_column(array_filter($locations, 'is_array'), 'slug');
        foreach ([
            'primary-nav'  => 'Hauptnavigation (Ressorts)',
            'footer-nav'   => 'Footer-Navigation',
            'footer-legal' => 'Footer Rechtliche Links',
            'topics-nav'   => 'Themen-Schnellnavigation (Sub-Bar)',
        ] as $slug => $label) {
            if (!in_array($slug, $existing, true)) {
                $locations[] = ['slug' => $slug, 'label' => $label];
            }
        }
        return $locations;
    }

    /**
     * Theme-Konfiguration aus theme.json (settings-Block) lesen.
     */
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

    // ── Internal helpers ─────────────────────────────────────────────────────

    /** URL des aktiven Theme-Ordners (`newspaper`); der Marketplace-Slug `cms-newspaper` ist kein Ordnername. */
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

    /**
     * Mapped Customizer-Auswahl auf konkreten Font-Stack inkl. Fallback-Kette.
     *
     * @param string $choice Wert aus theme.json customization.typography
     * @param string $slot   'display' | 'body' | 'mono'
     */
    private function mapFontChoice(string $choice, string $slot): string
    {
        // System-Fallbacks: Webfonts kommen nur aus der Core-Schriftverwaltung (keine Google Fonts).
        $sansStack  = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        $serifStack = 'ui-serif, Georgia, Cambria, "Times New Roman", Times, serif';
        $monoStack  = 'ui-monospace, SFMono-Regular, "JetBrains Mono", "IBM Plex Mono", Menlo, Consolas, monospace';

        return match ($choice) {
            // Display / Headlines
            'playfair'     => '"Playfair Display", ' . $serifStack,
            'source-serif' => '"Source Serif 4", ' . $serifStack,
            'eb-garamond'  => '"EB Garamond", ' . $serifStack,
            'lora'         => 'Lora, ' . $serifStack,
            'syne'         => 'Syne, ' . $sansStack,

            // Body
            'source-sans'  => '"Source Sans 3", ' . $sansStack,
            'inter'        => 'Inter, ' . $sansStack,
            'barlow'       => 'Barlow, ' . $sansStack,
            'system'       => $sansStack,

            // Mono
            'plex-mono'    => '"IBM Plex Mono", ' . $monoStack,
            'jetbrains'    => '"JetBrains Mono", ' . $monoStack,
            'system-mono'  => $monoStack,

            default        => match ($slot) {
                'display' => '"Playfair Display", ' . $serifStack,
                'mono'    => '"IBM Plex Mono", ' . $monoStack,
                default   => '"Source Sans 3", ' . $sansStack,
            },
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

CmsNewspaper_Theme::instance();

// ── Theme Helper Functions ──────────────────────────────────────────────────

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
    function theme_route_url(string $name): string
    {
        $base = rtrim((string) (defined('SITE_URL') ? SITE_URL : ''), '/');
        return match ($name) {
            'home'     => $base . '/',
            'search'   => $base . '/search',
            'login'    => $base . '/login',
            'register' => $base . '/register',
            'archive'  => $base . '/blog', // Beitragsarchiv des Cores (bisher /archiv, eine Route, die es nicht gibt)
            default    => $base . '/' . ltrim($name, '/'),
        };
    }
}

if (!function_exists('news_safe_url')) {
    /**
     * Nur sichere Linkziele: Pfad, Anker, http(s), mailto:, tel: – sonst `$fallback`
     * (FILTER_VALIDATE_URL allein ließ z. B. `javascript://…` durch).
     */
    function news_safe_url(string $url, string $fallback = '#'): string
    {
        $url = trim($url);
        // `//host` und `/\host` (Browser werten „\“ wie „/“) sind fremde, protokollrelative Adressen.
        if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url) === 1 || str_starts_with($url, '//') || str_contains($url, '\\')) {
            return $fallback;
        }
        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return $url;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return $url;
        }
        if (in_array($scheme, ['mailto', 'tel'], true)) {
            return $url;
        }

        return $fallback;
    }
}

if (!function_exists('theme_nav_menu')) {
    /**
     * Render a registered navigation menu as a <ul> list.
     *
     * Mit `$mainNavContext` (`desktop`/`mobile`) folgen direkt nach den Menüeinträgen die Einträge der Core-Module
     * (Hook `main_nav`: Downloads, Knowledgebase, Newsletter) – auch wenn das Menü leer ist.
     *
     * @return bool true, wenn eine Liste ausgegeben wurde
     */
    function theme_nav_menu(string $location, string $mainNavContext = ''): bool
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
            return false;
        }

        echo '<ul>';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            // Der Core liefert die Beschriftung in `title`; `label` bleibt für ältere Menüdaten.
            $label = trim((string) ($item['label'] ?? $item['title'] ?? ''));
            if ($label === '') {
                continue;
            }
            $sanitizedUrl = news_safe_url((string) ($item['url'] ?? '#'));
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
        echo $coreItems; // Core-Hook main_nav: fertige, escapte Links in <li>
        echo '</ul>';

        return true;
    }
}

if (!function_exists('get_header')) {
    function get_header(): void
    {
        if (is_file(__DIR__ . '/header.php')) {
            require __DIR__ . '/header.php';
        }
    }
}

if (!function_exists('get_footer')) {
    function get_footer(): void
    {
        if (is_file(__DIR__ . '/footer.php')) {
            require __DIR__ . '/footer.php';
        }
    }
}

if (!function_exists('news_get_setting')) {
    /**
     * Shorthand for ThemeCustomizer::get() with safe fallback.
     */
    function news_get_setting(string $section, string $key, mixed $default = ''): mixed
    {
        try {
            return \CMS\Services\ThemeCustomizer::instance()->get($section, $key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (!function_exists('news_get_flash')) {
    /**
     * Return and clear a session flash message array or null.
     *
     * @return array{type:string,message:string}|null
     */
    function news_get_flash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!empty($_SESSION['news_flash']) && is_array($_SESSION['news_flash'])) {
            $flash = $_SESSION['news_flash'];
            unset($_SESSION['news_flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('news_set_flash')) {
    function news_set_flash(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION['news_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('news_config')) {
    /**
     * Read a piece of theme content – first from Customizer (relevant group),
     * then from theme.json `settings` block, then static default.
     */
    function news_config(string $key, mixed $default = ''): mixed
    {
        static $heroKeys = [
            'hero_kicker'     => true,
            'hero_headline'   => true,
            'hero_lead'       => true,
            'hero_cta_label'  => true,
            'hero_cta_url'    => true,
        ];
        $group = isset($heroKeys[$key]) ? 'news_hero' : 'news_content';

        $val = news_get_setting($group, $key, null);
        if (is_string($val)) {
            if ($val !== '') {
                return $val;
            }
        } elseif ($val !== null) {
            return $val;
        }

        return CmsNewspaper_Theme::instance()->getConfig($key, $default);
    }
}

if (!function_exists('news_href')) {
    /**
     * Normalize a link target (absolute URL, mailto:, tel:, anchor or relative)
     * into an output-safe URL with leading host where appropriate.
     */
    function news_href(string $target): string
    {
        $trimmed = trim($target);
        $site    = rtrim((string) (defined('SITE_URL') ? SITE_URL : ''), '/');

        if ($trimmed === '') {
            return $site . '/';
        }
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }
        if (str_starts_with($trimmed, 'mailto:') || str_starts_with($trimmed, 'tel:')) {
            return $trimmed;
        }
        if (str_starts_with($trimmed, '#')) {
            return $site . '/' . $trimmed;
        }
        return $site . '/' . ltrim($trimmed, '/');
    }
}

if (!function_exists('news_site_title')) {
    function news_site_title(): string
    {
        try {
            return (string) \CMS\ThemeManager::instance()->getSiteTitle();
        } catch (\Throwable) {
            return 'PHINIT.DE';
        }
    }
}

if (!function_exists('news_body_class')) {
    /**
     * Server-render the <body> class string – no JS-mutation of classes.
     */
    function news_body_class(string ...$extra): string
    {
        $classes = ['news-body'];

        $sticky = filter_var(
            news_get_setting('header', 'enable_sticky_header', true),
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

if (!function_exists('news_brand_html')) {
    /**
     * Render the wordmark allowing only the inner `<span>` highlight
     * (e.g. PHIN<span>IT</span>.DE). Everything else is escaped.
     */
    function news_brand_html(string $raw): string
    {
        $escaped = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
        return str_replace(
            ['&lt;span&gt;', '&lt;/span&gt;'],
            ['<span class="news-wordmark-accent">', '</span>'],
            $escaped
        );
    }
}

if (!function_exists('news_css_color')) {
    /** Farbwert für CSS: nur Hex/RGB(A)/HSL(A), sonst `$fallback`. */
    function news_css_color(mixed $value, string $fallback): string
    {
        $candidate = trim(is_scalar($value) ? (string) $value : '');
        if (preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $candidate) === 1
            || preg_match('/^(?:rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/]+\)$/i', $candidate) === 1) {
            return $candidate;
        }

        return $fallback;
    }
}

if (!function_exists('news_css_number')) {
    /** Zahl für CSS, auf [min, max] geklemmt. */
    function news_css_number(mixed $value, float $default, float $min, float $max): string
    {
        $number = is_numeric($value) ? (float) $value : $default;
        $number = max($min, min($max, $number));

        return rtrim(rtrim(number_format($number, 3, '.', ''), '0'), '.');
    }
}

if (!function_exists('news_social_links')) {
    /**
     * Profil-Links der Utility-Bar aus dem Customizer (Header → Social-Profile). Leere Felder entfallen –
     * bisher zeigte die Leiste drei Platzhalter-Links auf `#`.
     *
     * @return list<array{label:string,url:string,icon:string}>
     */
    function news_social_links(): array
    {
        $links = [];
        foreach (['linkedin' => 'LinkedIn', 'github' => 'GitHub', 'x' => 'X (Twitter)'] as $key => $label) {
            $url = news_safe_url((string) news_get_setting('header', 'social_' . $key . '_url', ''), '');
            if ($url !== '' && preg_match('#^https?://#i', $url) === 1) {
                $links[] = ['label' => $label, 'url' => $url, 'icon' => $key];
            }
        }

        return $links;
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

if (!function_exists('news_document_title')) {
    /**
     * Dokumenttitel: `$title` der Core-Module/Fehlerseiten, sonst Beitrag/Seite über das Core-SEO, sonst Site-Titel
     * (bisher stand auf jeder Seite nur der Site-Titel im <title>).
     */
    function news_document_title(mixed $title = null): string
    {
        return theme_v4_document_title(is_string($title) ? $title : null);
    }
}
