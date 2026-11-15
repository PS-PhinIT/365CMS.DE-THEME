<?php
declare(strict_types=1);

/**
 * LogiLink Theme – functions.php
 *
 * Logistik & Transport Theme für 365CMS 4.0.0 – 4.1.0 auf PHP 8.4.1.
 *
 * @package LogiLink_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('LOGILINK_THEME_VERSION')) {
    define('LOGILINK_THEME_VERSION', '2.0.0');
}

if (!defined('LOGILINK_THEME_SLUG')) {
    define('LOGILINK_THEME_SLUG', 'logilink');
}

// 365CMS 4.x: header.php öffnet kein <main>; die Templates setzen <main id="main"> selbst.
if (!defined('THEME_V4_HEADER_HAS_MAIN')) {
    define('THEME_V4_HEADER_HAS_MAIN', false);
}
if (!defined('THEME_V4_MAIN_ID')) {
    define('THEME_V4_MAIN_ID', 'main');
}
require_once __DIR__ . '/includes/cms-v4-compat.php';

if (!function_exists('ll_css_color')) {
    /** Customizer-Farbe für CSS: nur #rgb/#rrggbb(aa), rgb(a)/hsl(a) – sonst Fallback. */
    function ll_css_color(string $value, string $fallback): string
    {
        $value = trim($value);

        return preg_match('/^(#[0-9a-f]{3,8}|(?:rgb|rgba|hsl|hsla)\([0-9.,%\s\/]+\))$/i', $value) === 1 ? $value : $fallback;
    }
}

if (!function_exists('ll_css_number')) {
    /** Customizer-Zahl für CSS (ohne Einheit) – sonst Fallback. */
    function ll_css_number(string $value, string $fallback): string
    {
        $value = trim($value);

        return preg_match('/^\d{1,4}(?:\.\d{1,3})?$/', $value) === 1 ? $value : $fallback;
    }
}

/**
 * Theme bootstrap (singleton).
 */
final class LogiLink_Theme
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
        // Menüpositionen ausschließlich über den Filter (ThemeManager::registerMenuLocation() gibt es im Core nicht).
        \CMS\Hooks::addFilter('register_menu_locations', [$this, 'registerMenuLocations']);

        // Der Core-Mitgliederbereich (/member) ist eine eigene Tabler-Shell und löst ebenfalls `head` aus:
        // dort kein Frontend-Stylesheet, keine Custom-Vars und keine Scripts, nur die Theme-Palette als --member-*.
        if (theme_v4_is_member_area()) {
            \CMS\Hooks::addAction('member_head', [$this, 'outputMemberTokens'], 20);
            return;
        }

        \CMS\Hooks::addAction('head', [$this, 'enqueueStyles'],      1);
        \CMS\Hooks::addAction('head', [$this, 'outputMetaTags'],     10);
        \CMS\Hooks::addAction('head', [$this, 'outputCustomStyles'], 15);

        \CMS\Hooks::addAction('before_footer', [$this, 'enqueueScripts'], 99);
    }

    /** Memberdashboard (inkl. Newsletter-Widget) in der LogiLink-Palette. LogiLink hat keinen Dark Mode. */
    public function outputMemberTokens(): void
    {
        $color = fn(string $key, string $default): string =>
            ll_css_color((string) $this->customizerGet('colors', $key, $default), $default);

        echo theme_v4_member_tokens_style([
            'primary'    => $color('primary_color', '#0e6ba8'),
            'accent'     => $color('accent_color', '#f59e0b'),
            'bg'         => $color('bg_color', '#f3f5f8'),
            'card_bg'    => $color('card_bg_color', '#ffffff'),
            'text'       => $color('text_color', '#0f172a'),
            'text_muted' => $color('muted_color', '#5b6b80'),
            'border'     => $color('border_color', '#d4dbe3'),
            'focus'      => $color('secondary_color', '#0a558a'),
            'radius'     => ll_css_number((string) $this->customizerGet('layout', 'border_radius', '6'), '6') . 'px',
            'font'       => 'Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
        ]);
    }

    // ── Assets ───────────────────────────────────────────────────────────────

    public function enqueueStyles(): void
    {
        $href = htmlspecialchars(
            $this->themeBaseUrl() . '/style.css?v=' . rawurlencode(LOGILINK_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        $v4Href = htmlspecialchars(
            $this->themeBaseUrl() . '/css/cms-v4.css?v=' . rawurlencode(LOGILINK_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<link rel="stylesheet" href="' . $href . '">' . "\n";
        echo '<link rel="stylesheet" href="' . $v4Href . '">' . "\n";
        // Schriften: keine Google-Fonts-Requests (DSGVO). Lokale Schriften liefert die Core-Schriftverwaltung,
        // sonst greift der System-Font-Stack (mapFontChoice()/style.css).
    }

    public function enqueueScripts(): void
    {
        $src = htmlspecialchars(
            $this->themeBaseUrl() . '/js/navigation.js?v=' . rawurlencode(LOGILINK_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<script src="' . $src . '" defer></script>' . "\n";
    }

    // ── Meta ────────────────────────────────────────────────────────────────

    /**
     * Nur noch `theme-color`. Beschreibung, Robots, Canonical und Open Graph kommen aus der Core-SEO
     * (theme_v4_head_tags() in header.php); die bisherigen festen Angaben (`index,follow`, `og:url` = Startseite)
     * hätten Vorschau-Seiten indexierbar gemacht und jede Unterseite als Startseite ausgewiesen.
     */
    public function outputMetaTags(): void
    {
        $themeColor = ll_css_color((string) $this->customizerGet('header', 'header_bg_color', '#0c1a2e'), '#0c1a2e');
        echo '<meta name="theme-color" content="' . htmlspecialchars($themeColor, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    // ── Customizer-getriebene CSS-Variablen + custom_css ─────────────────────

    public function outputCustomStyles(): void
    {
        // Customizer-Werte vor der Ausgabe validieren: Farben nur als Hex/rgb(a)/hsl(a), Zahlen nur numerisch,
        // damit kein Wert den <style>-Block mit `;}` verlassen kann.
        $p = function (string $g, string $k, string $d): string {
            $raw = (string) $this->customizerGet($g, $k, $d);
            return str_starts_with($d, '#') ? ll_css_color($raw, $d) : ll_css_number($raw, $d);
        };
        $choice = function (string $g, string $k, array $allowed, string $d): string {
            $raw = (string) $this->customizerGet($g, $k, $d);
            return in_array($raw, $allowed, true) ? $raw : $d;
        };

        // Fonts
        $baseChoice    = (string) $this->customizerGet('typography', 'font_family_base',    'inter');
        $headingChoice = (string) $this->customizerGet('typography', 'font_family_heading', 'roboto-condensed');
        $dataChoice    = (string) $this->customizerGet('typography', 'font_family_data',    'jetbrains-mono');

        $baseFont    = htmlspecialchars($this->mapFontChoice($baseChoice,    'body'),    ENT_QUOTES, 'UTF-8');
        $headingFont = htmlspecialchars($this->mapFontChoice($headingChoice, 'heading'), ENT_QUOTES, 'UTF-8');
        $monoFont    = htmlspecialchars($this->mapFontChoice($dataChoice,    'mono'),    ENT_QUOTES, 'UTF-8');

        echo '<style id="ll-custom-vars"' . theme_csp_nonce_attr() . '>:root{';

        // Brand & colors
        echo '--ll-primary:'       . $p('colors', 'primary_color',   '#0e6ba8') . ';';
        echo '--ll-primary-hover:' . $p('colors', 'secondary_color', '#0a558a') . ';';
        echo '--ll-accent:'        . $p('colors', 'accent_color',    '#f59e0b') . ';';
        echo '--ll-ink:'           . $p('colors', 'dark_bg_color',   '#0c1a2e') . ';';
        echo '--ll-paper:'         . $p('colors', 'bg_color',        '#f3f5f8') . ';';
        echo '--ll-surface:'       . $p('colors', 'card_bg_color',   '#ffffff') . ';';
        echo '--ll-text:'          . $p('colors', 'text_color',      '#0f172a') . ';';
        echo '--ll-text-mute:'     . $p('colors', 'muted_color',     '#5b6b80') . ';';
        echo '--ll-rule:'          . $p('colors', 'border_color',    '#d4dbe3') . ';';

        // Status
        echo '--status-warehouse:' . $p('colors', 'status_warehouse', '#475569') . ';';
        echo '--status-picked:'    . $p('colors', 'status_picked',    '#b45309') . ';';
        echo '--status-transit:'   . $p('colors', 'status_transit',   '#1d4ed8') . ';';
        echo '--status-delivered:' . $p('colors', 'status_delivered', '#15803d') . ';';
        echo '--status-delayed:'   . $p('colors', 'status_delayed',   '#b91c1c') . ';';
        echo '--status-returned:'  . $p('colors', 'status_returned',  '#6d28d9') . ';';

        // Typography
        echo '--ll-font-body:'      . $baseFont    . ';';
        echo '--ll-font-heading:'   . $headingFont . ';';
        echo '--ll-font-mono:'      . $monoFont    . ';';
        echo '--ll-font-size-base:' . $p('typography', 'font_size_base',     '15') . 'px;';
        echo '--ll-line-height:'    . $p('typography', 'line_height_base',   '1.5') . ';';
        echo '--ll-heading-weight:' . $p('typography', 'font_weight_heading','700') . ';';

        // Layout
        echo '--ll-max-width:'   . $p('layout', 'container_width', '1280') . 'px;';
        echo '--ll-content-pad:' . $p('layout', 'content_padding', '1.5')  . 'rem;';
        echo '--ll-radius:'      . $p('layout', 'border_radius',   '6')    . 'px;';
        echo '--ll-section-pad:' . $p('layout', 'section_spacing', '3')    . 'rem;';
        echo '--ll-kpi-columns:' . $p('layout', 'kpi_columns',     '4')    . ';';

        // Header
        echo '--ll-header-bg:'      . $p('header', 'header_bg_color',   '#0c1a2e') . ';';
        echo '--ll-header-text:'    . $p('header', 'header_text_color', '#e0f2fe') . ';';
        echo '--ll-header-height:'  . $p('header', 'header_height',     '64')      . 'px;';
        echo '--ll-logo-max-height:'. $p('header', 'logo_max_height',   '38')      . 'px;';

        // Footer
        echo '--ll-footer-bg:'   . $p('footer', 'footer_bg_color',   '#0c1a2e') . ';';
        echo '--ll-footer-text:' . $p('footer', 'footer_text_color', '#94a3b8') . ';';
        echo '--ll-footer-link:' . $p('footer', 'footer_link_color', '#bae6fd') . ';';

        // Buttons
        echo '--ll-btn-radius:'    . $p('buttons', 'button_border_radius', '5')     . 'px;';
        echo '--ll-btn-padding-x:' . $p('buttons', 'button_padding_x',     '1.5')   . 'rem;';
        echo '--ll-btn-padding-y:' . $p('buttons', 'button_padding_y',     '0.625') . 'rem;';
        echo '--ll-btn-weight:'    . $p('buttons', 'button_font_weight',   '600')   . ';';
        echo '--ll-btn-transform:' . $choice('buttons', 'button_transform', ['none', 'uppercase', 'capitalize'], 'none') . ';';

        // Fokusrahmen auf hellen Flächen: Amber erreicht auf --ll-paper nur 2,0:1 (WCAG 1.4.11 verlangt 3:1).
        // Dunkle Flächen (Header, Hero, KPI, Footer) setzen --focus-ring in style.css auf --ll-accent.
        echo '--focus-ring:'   . $p('colors', 'secondary_color', '#0a558a') . ';';

        echo '}';

        // Sticky-Header opt-out – Klasse via body wirkt
        $stickyHeader = filter_var(
            $this->customizerGet('layout', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$stickyHeader) {
            echo '.ll-site-header{position:static;}.ll-site-content{padding-top:0;}';
        }

        // Custom CSS (defensiv gegen </style>-Injection)
        $custom = $this->sanitizeCustomCss((string) $this->customizerGet('advanced', 'custom_css', ''));
        if ($custom !== '') {
            echo $custom;
        }

        echo '</style>' . "\n";
    }

    // ── Menü-Positionen ──────────────────────────────────────────────────────

    public function registerMenuLocations(array $locations): array
    {
        $locations[] = ['slug' => 'primary-nav',  'label' => 'Hauptmenü'];
        $locations[] = ['slug' => 'footer-nav',   'label' => 'Fußzeilen-Navigation'];
        $locations[] = ['slug' => 'footer-legal', 'label' => 'Rechtliche Links'];
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

    // ── Internal helpers ────────────────────────────────────────────────────

    private function themeBaseUrl(): string
    {
        return rtrim(theme_v4_theme_url(__DIR__), '/');
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
     * @param string $slot   'body' | 'heading' | 'mono'
     */
    private function mapFontChoice(string $choice, string $slot): string
    {
        $sansStack = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        $monoStack = 'ui-monospace, SFMono-Regular, "JetBrains Mono", "IBM Plex Mono", Menlo, Consolas, monospace';

        return match ($choice) {
            // Body / heading
            'inter'            => 'Inter, ' . $sansStack,
            'roboto'           => 'Roboto, ' . $sansStack,
            'open-sans'        => '"Open Sans", ' . $sansStack,
            'lato'             => 'Lato, ' . $sansStack,
            'roboto-condensed' => '"Roboto Condensed", ' . $sansStack,
            'montserrat'       => 'Montserrat, ' . $sansStack,
            'barlow-condensed' => '"Barlow Condensed", ' . $sansStack,

            // Mono / data
            'jetbrains-mono'   => '"JetBrains Mono", ' . $monoStack,
            'roboto-mono'      => '"Roboto Mono", ' . $monoStack,
            'fira-code'        => '"Fira Code", ' . $monoStack,
            'monospace'        => $monoStack,

            default            => match ($slot) {
                'heading' => '"Roboto Condensed", ' . $sansStack,
                'mono'    => '"JetBrains Mono", ' . $monoStack,
                default   => 'Inter, ' . $sansStack,
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

LogiLink_Theme::instance();

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
    /**
     * Theme-lokaler Routen-Resolver. Kennt die gängigen LogiLink-Pfade,
     * fällt sonst auf SITE_URL + Pfad zurück.
     */
    function theme_route_url(string $name): string
    {
        $base = rtrim((string) (defined('SITE_URL') ? SITE_URL : ''), '/');
        return match ($name) {
            'home'     => $base . '/',
            'tracking' => $base . '/tracking',
            'login'    => $base . '/login',
            'register' => $base . '/register',
            'member'   => $base . '/member',
            'partners' => $base . '/partners',
            'routes'   => $base . '/routes',
            'search'   => $base . '/search',
            default    => $base . '/' . ltrim($name, '/'),
        };
    }
}

if (!function_exists('theme_nav_menu')) {
    /**
     * Menü einer Position als <ul>. Mit `$mainNavContext` (`desktop`/`mobile`) folgen direkt nach den
     * Menüeinträgen die optionalen Einträge der Core-Module (Hook `main_nav`: Downloads, Knowledgebase, Newsletter) –
     * auch dann, wenn das Menü selbst leer ist.
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
            // Der Core liefert die Beschriftung in `title`; `label` bleibt als Fallback älterer Menüs.
            $label  = (string) ($item['label'] ?? $item['title'] ?? '');

            $sanitizedUrl = filter_var($rawUrl, FILTER_VALIDATE_URL)
                ? $rawUrl
                : (str_starts_with($rawUrl, '/') || str_starts_with($rawUrl, '#')
                    ? $rawUrl
                    : '#');
            if (!in_array(strtolower((string) parse_url($sanitizedUrl, PHP_URL_SCHEME)), ['', 'http', 'https'], true)) {
                $sanitizedUrl = '#';
            }
            $isActive = !str_starts_with($sanitizedUrl, '#') && theme_v4_is_current_path($sanitizedUrl);

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
            $tp = \CMS\ThemeManager::instance()->getThemePath(LOGILINK_THEME_SLUG) . '/header.php';
            if (file_exists($tp)) {
                $path = $tp;
            }
        } catch (\Throwable) {
            // bleibt bei __DIR__
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
            $tp = \CMS\ThemeManager::instance()->getThemePath(LOGILINK_THEME_SLUG) . '/footer.php';
            if (file_exists($tp)) {
                $path = $tp;
            }
        } catch (\Throwable) {
            // bleibt bei __DIR__
        }
        if (file_exists($path)) {
            require $path;
        }
    }
}

if (!function_exists('ll_get_setting')) {
    /**
     * Shorthand for ThemeCustomizer::get() with safe fallback.
     */
    function ll_get_setting(string $section, string $key, mixed $default = ''): mixed
    {
        try {
            return \CMS\Services\ThemeCustomizer::instance()->get($section, $key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (!function_exists('ll_get_flash')) {
    /**
     * Return and clear a session flash message array or null.
     *
     * @return array{type:string,message:string}|null
     */
    function ll_get_flash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!empty($_SESSION['ll_flash']) && is_array($_SESSION['ll_flash'])) {
            $flash = $_SESSION['ll_flash'];
            unset($_SESSION['ll_flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('ll_set_flash')) {
    function ll_set_flash(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION['ll_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('ll_href')) {
    /**
     * Normalize a link target into an output-safe URL with site host.
     */
    function ll_href(string $target): string
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

if (!function_exists('ll_site_url')) {
    function ll_site_url(): string
    {
        return rtrim((string) (defined('SITE_URL') ? SITE_URL : ''), '/');
    }
}

if (!function_exists('ll_site_title')) {
    function ll_site_title(): string
    {
        try {
            return (string) \CMS\ThemeManager::instance()->getSiteTitle();
        } catch (\Throwable) {
            return 'LogiLink';
        }
    }
}

if (!function_exists('ll_body_class')) {
    /**
     * Server-render the <body> class string – kein JS-Mutation der Klasse.
     */
    function ll_body_class(string ...$extra): string
    {
        $classes = ['ll-body'];

        $sticky = filter_var(
            ll_get_setting('layout', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$sticky) {
            $classes[] = 'no-sticky-header';
        }

        $dashboard = filter_var(
            ll_get_setting('layout', 'enable_dashboard_mode', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if ($dashboard) {
            $classes[] = 'has-dashboard-mode';
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

if (!function_exists('ll_safe_headline')) {
    /**
     * Erlaubt ausschliesslich `<span class="hl">…</span>` als kontrolliertes
     * Inline-Markup in Überschriften. Alles andere wird HTML-escaped.
     */
    function ll_safe_headline(string $raw): string
    {
        $escaped = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
        return str_replace(
            ['&lt;span class=&quot;hl&quot;&gt;', '&lt;/span&gt;'],
            ['<span class="ll-hl">', '</span>'],
            $escaped
        );
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
