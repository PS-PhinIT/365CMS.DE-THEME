<?php
declare(strict_types=1);

/**
 * TechNexus Theme – Bootstrap & Customizer Integration
 *
 * IT & Tech Hub Theme for 365CMS 4.0.0 – 4.1.0 on PHP 8.4.1.
 *
 * @package TechNexus_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('TECHNEXUS_THEME_VERSION')) {
    define('TECHNEXUS_THEME_VERSION', '2.1.0');
}

if (!defined('TECHNEXUS_THEME_SLUG')) {
    define('TECHNEXUS_THEME_SLUG', 'technexus');
}

// 365CMS 4.x: header.php öffnet kein <main>; die Templates setzen <main id="main"> selbst.
if (!defined('THEME_V4_HEADER_HAS_MAIN')) {
    define('THEME_V4_HEADER_HAS_MAIN', false);
}
if (!defined('THEME_V4_MAIN_ID')) {
    define('THEME_V4_MAIN_ID', 'main');
}
require_once __DIR__ . '/includes/cms-v4-compat.php';

if (!function_exists('tn_css_color')) {
    /** Customizer-Farbe für CSS: nur #rgb/#rrggbb(aa), rgb(a)/hsl(a) – sonst Fallback. */
    function tn_css_color(string $value, string $fallback): string
    {
        $value = trim($value);

        return preg_match('/^(#[0-9a-f]{3,8}|(?:rgb|rgba|hsl|hsla)\([0-9.,%\s\/]+\))$/i', $value) === 1 ? $value : $fallback;
    }
}

if (!function_exists('tn_css_number')) {
    /** Customizer-Zahl für CSS (ohne Einheit) – sonst Fallback. */
    function tn_css_number(string $value, string $fallback): string
    {
        $value = trim($value);

        return preg_match('/^\d{1,4}(?:\.\d{1,3})?$/', $value) === 1 ? $value : $fallback;
    }
}

/**
 * TechNexus Theme Singleton
 */
final class TechNexus_Theme
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

        \CMS\Hooks::addAction('head', [$this, 'outputColorSchemeBootstrap'], 0);
        \CMS\Hooks::addAction('head', [$this, 'enqueueStyles'],      1);
        \CMS\Hooks::addAction('head', [$this, 'outputMetaTags'],     10);
        \CMS\Hooks::addAction('head', [$this, 'outputCustomStyles'], 15);

        \CMS\Hooks::addAction('before_footer', [$this, 'enqueueScripts'], 99);
    }

    /**
     * Memberdashboard (inkl. Newsletter-Widget) in der TechNexus-Palette; Dark-Palette bei
     * prefers-color-scheme: dark (der Member-Shell fehlt der Theme-Umschalter).
     */
    public function outputMemberTokens(): void
    {
        $color = fn(string $group, string $key, string $default): string =>
            tn_css_color((string) $this->customizerGet($group, $key, $default), $default);
        $radius = tn_css_number((string) $this->customizerGet('layout', 'border_radius', '8'), '8') . 'px';
        $font   = 'Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';

        echo theme_v4_member_tokens_style([
            'primary'    => $color('colors', 'primary_color', '#0e7490'),
            'accent'     => $color('colors', 'accent_color', '#22d3ee'),
            'bg'         => $color('colors', 'bg_color', '#f8fafc'),
            'card_bg'    => $color('colors', 'card_bg_color', '#ffffff'),
            'text'       => $color('colors', 'text_color', '#0f172a'),
            'text_muted' => $color('colors', 'muted_color', '#5c6b7a'),
            'border'     => $color('colors', 'border_color', '#e2e8f0'),
            'focus'      => $color('colors', 'primary_color', '#0e7490'),
            'radius'     => $radius,
            'font'       => $font,
        ], [
            'primary'    => $color('colors', 'primary_color', '#0e7490'),
            'accent'     => $color('colors', 'accent_color', '#22d3ee'),
            'bg'         => $color('colors', 'dark_bg_color', '#0f172a'),
            'card_bg'    => $color('colors', 'dark_surface_color', '#1e293b'),
            'text'       => '#e8edf2',
            'text_muted' => '#94a3b8',
            'border'     => '#2a3548',
            'focus'      => $color('colors', 'accent_color', '#22d3ee'),
            'radius'     => $radius,
            'font'       => $font,
        ]);
    }

    /**
     * Farbschema vor dem ersten Rendern (Nonce-Inline-Script im <head>): gespeicherte Wahl (`tn-color-scheme`),
     * sonst – wenn „Standardmäßig dunkel“ aus ist – die Systemeinstellung. Bisher setzte erst navigation.js im
     * Footer die Wahl (Aufblitzen), und `prefers-color-scheme` griff nie, weil header.php immer data-theme setzt.
     */
    public function outputColorSchemeBootstrap(): void
    {
        $toggle = filter_var(tn_get_setting('advanced', 'enable_dark_mode_toggle', true), FILTER_VALIDATE_BOOLEAN);
        $js = '(function(){var d=document.documentElement,s=null;'
            . ($toggle ? 'try{s=localStorage.getItem("tn-color-scheme");}catch(e){}' : '')
            . 'if(s!=="dark"&&s!=="light"&&d.getAttribute("data-theme-default")==="system"&&window.matchMedia'
            . '&&window.matchMedia("(prefers-color-scheme: dark)").matches){s="dark";}'
            . 'if(s==="dark"||s==="light"){d.setAttribute("data-theme",s);}})();';
        echo '<script' . theme_csp_nonce_attr() . '>' . $js . '</script>' . "\n";
    }

    public function enqueueStyles(): void
    {
        $href = htmlspecialchars(
            $this->themeBaseUrl() . '/style.css?v=' . rawurlencode(TECHNEXUS_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        $v4Href = htmlspecialchars(
            $this->themeBaseUrl() . '/css/cms-v4.css?v=' . rawurlencode(TECHNEXUS_THEME_VERSION),
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
            $this->themeBaseUrl() . '/js/navigation.js?v=' . rawurlencode(TECHNEXUS_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<script src="' . $src . '" defer></script>' . "\n";
    }

    /**
     * Nur noch `theme-color`. Beschreibung, Robots, Canonical und Open Graph kommen aus der Core-SEO
     * (theme_v4_head_tags() in header.php); die bisherigen festen Angaben (`index,follow`, `og:url` = Startseite)
     * hätten Vorschau-Seiten indexierbar gemacht und jede Unterseite als Startseite ausgewiesen.
     */
    public function outputMetaTags(): void
    {
        $themeColor = tn_css_color((string) $this->customizerGet('header', 'header_bg_color', '#0f172a'), '#0f172a');
        echo '<meta name="theme-color" content="' . htmlspecialchars($themeColor, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    public function outputCustomStyles(): void
    {
        // Customizer-Werte vor der Ausgabe validieren: Farben nur als Hex/rgb(a)/hsl(a), Zahlen nur numerisch,
        // damit kein Wert den <style>-Block mit `;}` verlassen kann.
        $p = function (string $g, string $k, string $d): string {
            $raw = (string) $this->customizerGet($g, $k, $d);
            return str_starts_with($d, '#') ? tn_css_color($raw, $d) : tn_css_number($raw, $d);
        };
        $choice = function (string $g, string $k, array $allowed, string $d): string {
            $raw = (string) $this->customizerGet($g, $k, $d);
            return in_array($raw, $allowed, true) ? $raw : $d;
        };

        $baseChoice    = (string) $this->customizerGet('typography', 'font_family_base',    'inter');
        $headingChoice = (string) $this->customizerGet('typography', 'font_family_heading', 'plus-jakarta-sans');
        $codeChoice    = (string) $this->customizerGet('typography', 'font_family_code',    'jetbrains-mono');

        $baseFont    = htmlspecialchars($this->mapFontChoice($baseChoice,    'body'),    ENT_QUOTES, 'UTF-8');
        $headingFont = htmlspecialchars($this->mapFontChoice($headingChoice, 'heading'), ENT_QUOTES, 'UTF-8');
        $codeFont    = htmlspecialchars($this->mapFontChoice($codeChoice,    'mono'),    ENT_QUOTES, 'UTF-8');

        echo '<style id="tn-custom-vars"' . theme_csp_nonce_attr() . '>:root{';

        // Colors
        echo '--tn-primary:'       . $p('colors', 'primary_color',   '#0e7490') . ';';
        echo '--tn-primary-hover:' . $p('colors', 'secondary_color', '#155e75') . ';';
        echo '--tn-accent:'        . $p('colors', 'accent_color',    '#22d3ee') . ';';
        echo '--tn-code:'          . $p('colors', 'code_color',      '#047857') . ';';
        echo '--tn-text:'          . $p('colors', 'text_color',      '#0f172a') . ';';
        echo '--tn-bg:'            . $p('colors', 'bg_color',        '#f8fafc') . ';';
        echo '--tn-card-bg:'       . $p('colors', 'card_bg_color',   '#ffffff') . ';';
        echo '--tn-dark-bg:'       . $p('colors', 'dark_bg_color',   '#0f172a') . ';';
        echo '--tn-dark-surface:'  . $p('colors', 'dark_surface_color', '#1e293b') . ';';
        echo '--tn-link:'          . $p('colors', 'link_color',      '#1d4ed8') . ';';
        echo '--tn-muted:'         . $p('colors', 'muted_color',     '#5c6b7a') . ';';
        echo '--tn-border:'        . $p('colors', 'border_color',    '#e2e8f0') . ';';
        echo '--tn-success:'       . $p('colors', 'success_color',   '#22c55e') . ';';
        echo '--tn-error:'         . $p('colors', 'error_color',     '#ef4444') . ';';
        echo '--tn-warning:'       . $p('colors', 'warning_color',   '#f59e0b') . ';';
        // Fokusrahmen auf hellen Flächen: Cyan #22d3ee erreicht dort nur 1,7:1 (WCAG 1.4.11 verlangt 3:1).
        // Header, Hero, CTA, Footer und Dark Mode setzen --focus-ring in style.css auf --accent-color.
        echo '--focus-ring:'       . $p('colors', 'primary_color',   '#0e7490') . ';';

        // Legacy aliases (style.css)
        echo '--primary-color:'     . $p('colors', 'primary_color',   '#0e7490') . ';';
        echo '--primary-hover:'     . $p('colors', 'secondary_color', '#155e75') . ';';
        echo '--accent-color:'      . $p('colors', 'accent_color',    '#22d3ee') . ';';
        echo '--code-color:'        . $p('colors', 'code_color',      '#047857') . ';';
        echo '--text-color:'        . $p('colors', 'text_color',      '#0f172a') . ';';
        echo '--text-primary:'      . $p('colors', 'text_color',      '#0f172a') . ';';
        echo '--heading-color:'     . $p('colors', 'text_color',      '#0f172a') . ';';
        echo '--background-color:'  . $p('colors', 'bg_color',        '#f8fafc') . ';';
        echo '--card-bg:'           . $p('colors', 'card_bg_color',   '#ffffff') . ';';
        echo '--muted-color:'       . $p('colors', 'muted_color',     '#5c6b7a') . ';';
        echo '--border-color:'      . $p('colors', 'border_color',    '#e2e8f0') . ';';
        echo '--tn-link:'           . $p('colors', 'link_color',      '#1d4ed8') . ';';
        echo '--link-color:'        . $p('colors', 'link_color',      '#1d4ed8') . ';';
        echo '--success-color:'     . $p('colors', 'success_color',   '#22c55e') . ';';
        echo '--error-color:'       . $p('colors', 'error_color',     '#ef4444') . ';';
        echo '--warning-color:'     . $p('colors', 'warning_color',   '#f59e0b') . ';';
        echo '--status-online:'     . $p('colors', 'success_color',   '#22c55e') . ';';
        echo '--status-busy:'       . $p('colors', 'warning_color',   '#f59e0b') . ';';

        // Typography
        echo '--tn-font-body:'      . $baseFont    . ';';
        echo '--tn-font-heading:'   . $headingFont . ';';
        echo '--tn-font-code:'      . $codeFont    . ';';
        echo '--font-body:'         . $baseFont    . ';';
        echo '--font-heading:'      . $headingFont . ';';
        echo '--font-code:'         . $codeFont    . ';';
        echo '--tn-font-size-base:' . $p('typography', 'font_size_base',     '16') . 'px;';
        echo '--tn-line-height:'    . $p('typography', 'line_height_base',   '1.6') . ';';
        echo '--tn-heading-weight:' . $p('typography', 'font_weight_heading','700') . ';';

        // Layout
        echo '--tn-max-width:'      . $p('layout', 'container_width',  '1280') . 'px;';
        echo '--container-max-width:' . $p('layout', 'container_width', '1280') . 'px;';
        echo '--tn-content-pad:'    . $p('layout', 'content_padding',  '2')   . 'rem;';
        echo '--container-padding:' . $p('layout', 'content_padding',  '2')   . 'rem;';
        echo '--tn-radius:'         . $p('layout', 'border_radius',    '8')   . 'px;';
        echo '--border-radius:'     . $p('layout', 'border_radius',    '8')   . 'px;';
        echo '--tn-section-pad:'    . $p('layout', 'section_spacing',  '5')   . 'rem;';
        echo '--tn-card-columns:'   . $choice('layout', 'card_columns', ['2', '3', '4'], '3') . ';';

        // Header
        echo '--tn-header-bg:'      . $p('header', 'header_bg_color',   '#0f172a') . ';';
        echo '--header-bg:'         . $p('header', 'header_bg_color',   '#0f172a') . ';';
        echo '--tn-header-text:'    . $p('header', 'header_text_color', '#f1f5f9') . ';';
        echo '--header-text:'       . $p('header', 'header_text_color', '#f1f5f9') . ';';
        echo '--tn-header-height:'  . $p('header', 'header_height',     '72')      . 'px;';
        echo '--tn-logo-max-height:' . $p('header', 'logo_max_height',   '40')      . 'px;';
        echo '--logo-max-height:'    . $p('header', 'logo_max_height',   '40')      . 'px;';

        // Footer
        echo '--tn-footer-bg:'     . $p('footer', 'footer_bg_color',   '#0f172a') . ';';
        echo '--footer-bg:'         . $p('footer', 'footer_bg_color',   '#0f172a') . ';';
        echo '--tn-footer-text:'   . $p('footer', 'footer_text_color', '#94a3b8') . ';';
        echo '--footer-text:'      . $p('footer', 'footer_text_color', '#94a3b8') . ';';
        echo '--tn-footer-link:'   . $p('footer', 'footer_link_color', '#e2e8f0') . ';';

        // Buttons
        echo '--tn-btn-radius:'     . $p('buttons', 'button_border_radius', '6')     . 'px;';
        echo '--tn-btn-padding-x:'  . $p('buttons', 'button_padding_x',     '1.5')   . 'rem;';
        echo '--tn-btn-padding-y:'  . $p('buttons', 'button_padding_y',     '0.625') . 'rem;';
        echo '--tn-btn-weight:'     . $p('buttons', 'button_font_weight',   '600')   . ';';
        echo '--tn-btn-transform:'  . $choice('buttons', 'button_transform', ['none', 'uppercase', 'capitalize'], 'none') . ';';

        // Hero
        echo '--hero-grad-start:'   . $p('tech_hero', 'hero_gradient_start', '#0f172a') . ';';
        echo '--hero-grad-end:'     . $p('tech_hero', 'hero_gradient_end',   '#1e3a5f') . ';';

        echo '}';

        $headerHeight = (int) $this->customizerGet('header', 'header_height', 72);
        echo '.site-header{height:' . max(48, min(120, $headerHeight)) . 'px;}';

        $stickyHeader = filter_var(
            $this->customizerGet('layout', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$stickyHeader) {
            echo '.site-header{position:static;}';
        }

        $showShadow = filter_var(
            $this->customizerGet('header', 'show_header_shadow', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$showShadow) {
            echo '.site-header.scrolled{box-shadow:none;}';
        }

        $blurHeader = filter_var(
            $this->customizerGet('header', 'enable_blur_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$blurHeader) {
            echo '.site-header.blur-enabled.scrolled{backdrop-filter:none;}';
        }

        // Spaltenzahl nur noch als --tn-card-columns (oben); die feste Regel `.tech-grid{…}` an dieser Stelle
        // überschrieb die Responsive-Regeln aus style.css (auf dem Smartphone blieben drei Spalten).

        $custom = $this->sanitizeCustomCss((string) $this->customizerGet('advanced', 'custom_css', ''));
        if ($custom !== '') {
            echo $custom;
        }

        echo '</style>' . "\n";
    }

    public function registerMenuLocations(array $locations): array
    {
        $locations[] = ['slug' => 'primary',      'label' => 'Hauptnavigation'];
        $locations[] = ['slug' => 'footer-nav',   'label' => 'Footer-Navigation'];
        $locations[] = ['slug' => 'footer-legal', 'label' => 'Footer Rechtliche Links'];
        $locations[] = ['slug' => 'tech-cats',    'label' => 'Tech-Kategorien'];
        return $locations;
    }

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

    private function mapFontChoice(string $choice, string $slot): string
    {
        $sansStack = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        $monoStack = 'ui-monospace, SFMono-Regular, "JetBrains Mono", Menlo, Consolas, monospace';

        return match ($choice) {
            'inter'              => 'Inter, ' . $sansStack,
            'roboto'             => 'Roboto, ' . $sansStack,
            'open-sans'          => '"Open Sans", ' . $sansStack,
            'poppins'            => 'Poppins, ' . $sansStack,
            'plus-jakarta-sans'  => '"Plus Jakarta Sans", ' . $sansStack,
            'montserrat'         => 'Montserrat, ' . $sansStack,
            'raleway'            => 'Raleway, ' . $sansStack,
            'system'             => $sansStack,
            'jetbrains-mono'     => '"JetBrains Mono", ' . $monoStack,
            'fira-code'          => '"Fira Code", ' . $monoStack,
            'source-code-pro'    => '"Source Code Pro", ' . $monoStack,
            'monospace'          => $monoStack,
            default              => match ($slot) {
                'heading' => '"Plus Jakarta Sans", ' . $sansStack,
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

TechNexus_Theme::instance();

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
            'home'      => $base . '/',
            'search'    => $base . '/search',
            'login'     => $base . '/login',
            'register'  => $base . '/register',
            'member'    => $base . '/member',
            'experts'   => $base . '/it-experts',
            'companies' => $base . '/companies',
            'events'    => $base . '/events',
            'jobs'      => $base . '/jobs',
            default     => $base . '/' . ltrim($name, '/'),
        };
    }
}

if (!function_exists('tn_menu_url')) {
    /** Menü-URL: http(s), lokaler Pfad (kein `//`/`\`), Anker, mailto:/tel: – sonst `#`. */
    function tn_menu_url(string $url): string
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url) === 1 || str_contains($url, '\\') || str_starts_with($url, '//')) {
            return '#';
        }
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }
        if (preg_match('/^(?:mailto:[^\s@<>"]+@[^\s@<>"]+|tel:\+?[0-9][0-9\s().\/-]{2,31})$/i', $url) === 1) {
            return $url;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false ? $url : '#';
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

            // `//host` und `/\host` galten als lokale Pfade (fremde Adresse), mailto:/tel: wurden zu `#`.
            $sanitizedUrl = tn_menu_url($rawUrl);
            $isActive = !str_starts_with($sanitizedUrl, '#') && preg_match('/^(?:mailto|tel):/i', $sanitizedUrl) !== 1
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
            $tp = \CMS\ThemeManager::instance()->getThemePath(TECHNEXUS_THEME_SLUG) . '/header.php';
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
            $tp = \CMS\ThemeManager::instance()->getThemePath(TECHNEXUS_THEME_SLUG) . '/footer.php';
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

if (!function_exists('tn_get_setting')) {
    function tn_get_setting(string $section, string $key, mixed $default = ''): mixed
    {
        try {
            return \CMS\Services\ThemeCustomizer::instance()->get($section, $key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (!function_exists('technexus_get_setting')) {
    function technexus_get_setting(string $section, string $key, mixed $default = ''): mixed
    {
        return tn_get_setting($section, $key, $default);
    }
}

if (!function_exists('tn_get_flash')) {
    /** @return array{type:string,message:string}|null */
    function tn_get_flash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!empty($_SESSION['tn_flash']) && is_array($_SESSION['tn_flash'])) {
            $flash = $_SESSION['tn_flash'];
            unset($_SESSION['tn_flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('tn_set_flash')) {
    function tn_set_flash(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION['tn_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('tn_href')) {
    function tn_href(string $target): string
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

if (!function_exists('tn_site_title')) {
    function tn_site_title(): string
    {
        try {
            return (string) \CMS\ThemeManager::instance()->getSiteTitle();
        } catch (\Throwable) {
            return 'TechNexus';
        }
    }
}

if (!function_exists('tn_body_class')) {
    function tn_body_class(string ...$extra): string
    {
        $classes = ['tn-body'];

        $sticky = filter_var(
            tn_get_setting('layout', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if ($sticky) {
            $classes[] = 'has-sticky-header';
        } else {
            $classes[] = 'no-sticky-header';
        }

        if (filter_var(tn_get_setting('layout', 'enable_grid_background', true), FILTER_VALIDATE_BOOLEAN)) {
            $classes[] = 'tn-grid-bg';
        }

        $hover = (string) tn_get_setting('tech_expert_cards', 'card_hover_effect', 'border');
        if (in_array($hover, ['lift', 'glow', 'border', 'none'], true)) {
            $classes[] = 'tn-card-hover-' . $hover;
        }

        if (filter_var(tn_get_setting('advanced', 'prefers_dark_mode', false), FILTER_VALIDATE_BOOLEAN)) {
            $classes[] = 'tn-prefers-dark';
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

if (!function_exists('tn_safe_headline')) {
    function tn_safe_headline(string $raw): string
    {
        $escaped = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
        return str_replace(
            ['&lt;span class=&quot;tn-hl&quot;&gt;', '&lt;/span&gt;'],
            ['<span class="tn-hl">', '</span>'],
            $escaped
        );
    }
}

if (!function_exists('tn_html_attr')) {
    function tn_html_attr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

if (!function_exists('theme_logout_url')) {
    /**
     * Logout-URL mit CSRF-Token der Aktion „logout“ (PublicRouter::handleLogout verlangt ihn).
     *
     * @deprecated 2.0.0 Das Theme meldet per POST-Formular ab (theme_v4_logout_form()); ein Token in der
     *             URL landet in Verlauf, Logs und Referer. Nur noch für Child-Themes vorhanden.
     */
    function theme_logout_url(): string
    {
        static $url = null;
        if ($url !== null) {
            return $url;
        }
        // Veraltet (seit 2.0.0 ungenutzt): Logout nur noch als POST über theme_v4_logout_form(). Bis 2.1.0 hängte
        // die Funktion das CSRF-Token an die URL (Leck über Logs, Verlauf und Referer); jetzt reine Adresse.
        $url = rtrim((string) SITE_URL, '/') . '/logout';

        return $url;
    }
}
