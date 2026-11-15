<?php
declare(strict_types=1);

/**
 * MedCare Pro Theme – functions.php
 *
 * Medizin- & Gesundheitspraxis-Theme für 365CMS 4.0.0 – 4.1.0 auf PHP 8.4.1.
 *
 * @package MedCarePro_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('MEDCAREPRO_THEME_VERSION')) {
    define('MEDCAREPRO_THEME_VERSION', '2.0.0');
}

if (!defined('MEDCAREPRO_THEME_SLUG')) {
    define('MEDCAREPRO_THEME_SLUG', 'medcarepro');
}

// 365CMS 4.x: header.php öffnet kein <main>; jedes Template setzt <main id="main"> (Skip-Link-Ziel) selbst.
if (!defined('THEME_V4_HEADER_HAS_MAIN')) {
    define('THEME_V4_HEADER_HAS_MAIN', false);
}
if (!defined('THEME_V4_MAIN_ID')) {
    define('THEME_V4_MAIN_ID', 'main');
}
require_once __DIR__ . '/includes/cms-v4-compat.php';

/**
 * Theme bootstrap (singleton).
 */
final class MedCarePro_Theme
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
        // dort weder Frontend-Stylesheet noch Customizer-Variablen oder Script, nur die Palette als --member-*.
        if (theme_v4_is_member_area()) {
            \CMS\Hooks::addAction('member_head', [$this, 'outputMemberTokens'], 20);
            return;
        }

        \CMS\Hooks::addAction('head', [$this, 'enqueueStyles'],      1);
        \CMS\Hooks::addAction('head', [$this, 'outputMetaTags'],     10);
        \CMS\Hooks::addAction('head', [$this, 'outputCustomStyles'], 15);

        \CMS\Hooks::addAction('before_footer', [$this, 'enqueueScripts'], 99);
    }

    // ── Assets ───────────────────────────────────────────────────────────────

    public function enqueueStyles(): void
    {
        $version = rawurlencode(MEDCAREPRO_THEME_VERSION);
        $href    = htmlspecialchars($this->themeBaseUrl() . '/style.css?v=' . $version, ENT_QUOTES, 'UTF-8');
        $v4Href  = htmlspecialchars($this->themeBaseUrl() . '/css/cms-v4.css?v=' . $version, ENT_QUOTES, 'UTF-8');
        echo '<link rel="stylesheet" href="' . $href . '">' . "\n";
        echo '<link rel="stylesheet" href="' . $v4Href . '">' . "\n";
        // Schriften: keine Google-Fonts-Requests (DSGVO). Lokale Schriften liefert die Core-Schriftverwaltung,
        // sonst greifen die System-Fallbacks der Font-Stacks.
    }

    /** Memberbereich (inkl. Newsletter-Widget) in der MedCare-Palette – Customizer-Werte, sanitisiert. */
    public function outputMemberTokens(): void
    {
        $c = fn(string $key, string $default): string => mc_css_color($this->customizerGet('colors', $key, $default), $default);
        echo theme_v4_member_tokens_style([
            'primary'    => $c('primary_color', '#0e5b6b'),
            'accent'     => $c('accent_color', '#047857'),
            'bg'         => $c('bg_color', '#f6f8fa'),
            'card_bg'    => $c('card_bg_color', '#ffffff'),
            'text'       => $c('text_color', '#0f1f25'),
            'text_muted' => $c('muted_color', '#5b6b73'),
            'border'     => $c('border_color', '#e1e6ec'),
            'focus'      => $c('primary_color', '#0e5b6b'),
            'radius'     => mc_css_number($this->customizerGet('layout', 'border_radius', 10), 10, 0, 24) . 'px',
            'font'       => $this->mapFontChoice((string) $this->customizerGet('typography', 'font_family_base', 'open-sans'), 'body'),
        ]);
    }

    public function enqueueScripts(): void
    {
        $src = htmlspecialchars(
            $this->themeBaseUrl() . '/js/navigation.js?v=' . rawurlencode(MEDCAREPRO_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<script src="' . $src . '" defer></script>' . "\n";
    }

    // ── Meta ────────────────────────────────────────────────────────────────

    /**
     * Nur noch theme-color. Beschreibung, Robots, Canonical und Open Graph liefert das Core-SEO in header.php
     * (theme_v4_head_tags()) – bisher gab das Theme zusätzlich eine site-weite Beschreibung, `og:url` = Startseite
     * und `robots: index,follow` aus, auch auf Vorschau-, Login- und Fehlerseiten.
     */
    public function outputMetaTags(): void
    {
        $themeColor = mc_css_color($this->customizerGet('header', 'header_bg_color', '#ffffff'), '#ffffff');
        echo '<meta name="theme-color" content="' . htmlspecialchars($themeColor, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    // ── Customizer-getriebene CSS-Variablen + custom_css ─────────────────────

    public function outputCustomStyles(): void
    {
        // Werte validiert statt nur HTML-escaped: Farben als Hex/RGB/HSL, Zahlen auf die theme.json-Grenzen geklemmt,
        // Auswahlwerte gegen ihre Liste geprüft.
        $numberLimits = [
            'typography'    => ['font_size_base' => [17, 14, 24], 'line_height_base' => [1.75, 1.2, 2.5]],
            'accessibility' => ['min_font_size_accessibility' => [16, 14, 24]],
            'layout'        => ['container_width' => [1100, 800, 1400], 'content_padding' => [2.5, 0.5, 6], 'border_radius' => [10, 0, 24], 'section_spacing' => [5, 1, 12]],
            'header'        => ['header_height' => [76, 48, 120], 'logo_max_height' => [52, 24, 100]],
            'buttons'       => ['button_border_radius' => [8, 0, 50], 'button_padding_x' => [2, 0.5, 4], 'button_padding_y' => [0.875, 0.25, 2]],
        ];
        $choices = [
            'typography' => ['font_weight_heading' => ['600', '700']],
            'buttons'    => ['button_font_weight' => ['600', '700'], 'button_transform' => ['none', 'capitalize', 'uppercase']],
        ];
        $p = function (string $g, string $k, string $d) use ($numberLimits, $choices): string {
            $raw = $this->customizerGet($g, $k, $d);
            if (isset($numberLimits[$g][$k])) {
                [$default, $min, $max] = $numberLimits[$g][$k];
                return mc_css_number($raw, (float) $default, (float) $min, (float) $max);
            }
            if (isset($choices[$g][$k])) {
                return in_array((string) $raw, $choices[$g][$k], true) ? (string) $raw : $d;
            }

            return mc_css_color($raw, $d);
        };

        // Fonts – feste Stacks aus mapFontChoice(), nicht HTML-escapen (Entities wie &quot; werden in <style>
        // nicht dekodiert; bisher war --mc-font-* dadurch ungültig und die gewählten Schriften griffen nie).
        $baseChoice    = (string) $this->customizerGet('typography', 'font_family_base',         'open-sans');
        $headingChoice = (string) $this->customizerGet('typography', 'font_family_heading',      'source-sans-pro');
        $medicalChoice = (string) $this->customizerGet('typography', 'font_family_medical_text', 'source-serif-pro');

        $baseFont    = $this->mapFontChoice($baseChoice,    'body');
        $headingFont = $this->mapFontChoice($headingChoice, 'heading');
        $serifFont   = $this->mapFontChoice($medicalChoice, 'serif');

        echo '<style id="mc-custom-vars"' . theme_csp_nonce_attr() . '>:root{';

        // Brand & Farben
        echo '--mc-primary:'       . $p('colors', 'primary_color',     '#0e5b6b') . ';';
        echo '--mc-primary-hover:' . $p('colors', 'secondary_color',   '#0c4f5d') . ';';
        echo '--mc-accent:'        . $p('colors', 'accent_color',      '#047857') . ';';
        echo '--mc-warning:'       . $p('colors', 'warning_color',     '#b45309') . ';';
        echo '--mc-urgent:'        . $p('colors', 'urgent_color',      '#b91c1c') . ';';
        echo '--mc-link:'          . $p('colors', 'link_color',        '#107585') . ';';

        // Spezialgebiete (Fachbereiche)
        echo '--mc-spec-general:'  . $p('colors', 'specialty_general', '#0e5b6b') . ';';
        echo '--mc-spec-dental:'   . $p('colors', 'specialty_dental',  '#b45309') . ';';
        echo '--mc-spec-surgery:'  . $p('colors', 'specialty_surgery', '#7c3aed') . ';';
        echo '--mc-spec-therapy:'  . $p('colors', 'specialty_therapy', '#047857') . ';';

        // Flächen / Texte
        echo '--mc-paper:'         . $p('colors', 'bg_color',          '#f6f8fa') . ';';
        echo '--mc-surface:'       . $p('colors', 'card_bg_color',     '#ffffff') . ';';
        echo '--mc-ink:'           . $p('colors', 'text_color',        '#0f1f25') . ';';
        echo '--mc-text-mute:'     . $p('colors', 'muted_color',       '#5b6b73') . ';';
        echo '--mc-rule:'          . $p('colors', 'border_color',      '#e1e6ec') . ';';

        // Typografie
        echo '--mc-font-body:'     . $baseFont    . ';';
        echo '--mc-font-heading:'  . $headingFont . ';';
        echo '--mc-font-serif:'    . $serifFont   . ';';
        echo '--mc-font-size-base:'    . $p('typography',    'font_size_base',              '17')  . 'px;';
        echo '--mc-font-size-min:'     . $p('accessibility', 'min_font_size_accessibility', '16')  . 'px;';
        echo '--mc-line-height:'       . $p('typography',    'line_height_base',            '1.7') . ';';
        echo '--mc-heading-weight:'    . $p('typography',    'font_weight_heading',         '700') . ';';

        // Layout
        echo '--mc-max-width:'    . $p('layout', 'container_width', '1100') . 'px;';
        echo '--mc-content-pad:'  . $p('layout', 'content_padding', '2.5')  . 'rem;';
        echo '--mc-radius:'       . $p('layout', 'border_radius',   '10')   . 'px;';
        echo '--mc-section-pad:'  . $p('layout', 'section_spacing', '5')    . 'rem;';

        // Header
        echo '--mc-header-bg:'      . $p('header', 'header_bg_color',     '#ffffff') . ';';
        echo '--mc-header-text:'    . $p('header', 'header_text_color',   '#0f1f25') . ';';
        echo '--mc-header-rule:'    . $p('header', 'header_border_color', '#e1e6ec') . ';';
        echo '--mc-header-height:'  . $p('header', 'header_height',       '76')      . 'px;';
        echo '--mc-logo-max:'       . $p('header', 'logo_max_height',     '52')      . 'px;';

        // Footer
        echo '--mc-footer-bg:'   . $p('footer', 'footer_bg_color',   '#082f38') . ';';
        echo '--mc-footer-text:' . $p('footer', 'footer_text_color', '#b9d3da') . ';';
        echo '--mc-footer-link:' . $p('footer', 'footer_link_color', '#e0f1f5') . ';';

        // Buttons
        echo '--mc-btn-radius:'    . $p('buttons', 'button_border_radius', '8')     . 'px;';
        echo '--mc-btn-padding-x:' . $p('buttons', 'button_padding_x',     '2')     . 'rem;';
        echo '--mc-btn-padding-y:' . $p('buttons', 'button_padding_y',     '0.875') . 'rem;';
        echo '--mc-btn-weight:'    . $p('buttons', 'button_font_weight',   '600')   . ';';
        echo '--mc-btn-transform:' . $p('buttons', 'button_transform',     'none')  . ';';

        // Focus
        echo '--focus-ring:'   . $p('colors', 'primary_color', '#0e5b6b') . ';';

        echo '}';

        // Sticky-Header opt-out – Klasse via body wirkt
        $stickyHeader = filter_var(
            $this->customizerGet('layout', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$stickyHeader) {
            echo '.mc-site-header{position:static;}body.mc-body{padding-top:0;}.mc-site-content{padding-top:0;}';
        }

        // Custom CSS (defensiv gegen </style>-Injection)
        $custom = $this->sanitizeCustomCss((string) $this->customizerGet('advanced', 'custom_css', ''));
        if ($custom !== '') {
            echo $custom;
        }

        echo '</style>' . "\n";
    }

    // ── Menü-Positionen ──────────────────────────────────────────────────────

    /**
     * @param mixed $locations
     * @return array<int, mixed>
     */
    public function registerMenuLocations(mixed $locations): array
    {
        $locations = is_array($locations) ? $locations : [];
        $existing  = array_column(array_filter($locations, 'is_array'), 'slug');
        foreach ([
            'primary-nav'  => 'Hauptmenü',
            'footer-nav'   => 'Fußzeilen-Navigation',
            'footer-legal' => 'Rechtliche Links',
        ] as $slug => $label) {
            if (!in_array($slug, $existing, true)) {
                $locations[] = ['slug' => $slug, 'label' => $label];
            }
        }
        return $locations;
    }

    // ── Internal helpers ────────────────────────────────────────────────────

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
     * @param string $slot   'body' | 'heading' | 'serif'
     */
    private function mapFontChoice(string $choice, string $slot): string
    {
        // System-Fallbacks: Webfonts kommen nur aus der Core-Schriftverwaltung (keine Google Fonts).
        $sansStack  = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        $serifStack = 'ui-serif, Georgia, Cambria, "Times New Roman", "Liberation Serif", serif';

        return match ($choice) {
            'open-sans'         => '"Open Sans", ' . $sansStack,
            'roboto'            => 'Roboto, ' . $sansStack,
            'lato'              => 'Lato, ' . $sansStack,
            'inter'             => 'Inter, ' . $sansStack,
            'source-sans-pro'   => '"Source Sans 3", ' . $sansStack,
            'libre-baskerville' => '"Libre Baskerville", ' . $serifStack,
            'source-serif-pro'  => '"Source Serif 4", ' . $serifStack,
            'georgia'           => $serifStack,
            default             => match ($slot) {
                'heading' => '"Source Sans 3", ' . $sansStack,
                'serif'   => '"Source Serif 4", ' . $serifStack,
                default   => '"Open Sans", ' . $sansStack,
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

MedCarePro_Theme::instance();

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
     * Theme-lokaler Routen-Resolver. Kennt die gängigen MedCare-Pro-Pfade,
     * fällt sonst auf SITE_URL + Pfad zurück.
     */
    function theme_route_url(string $name): string
    {
        $base = rtrim((string) (defined('SITE_URL') ? SITE_URL : ''), '/');
        return match ($name) {
            'home'      => $base . '/',
            'search'    => $base . '/search',
            'login'     => $base . '/login',
            'register'  => $base . '/register',
            'member'    => $base . '/member',
            'doctors'   => $base . '/aerzte',
            'booking'   => $base . '/termin',
            'fields'    => $base . '/fachgebiete',
            'blog'      => $base . '/blog',
            'imprint'   => $base . '/impressum',
            'privacy'   => $base . '/datenschutz',
            'terms'     => $base . '/agb',
            'cookies'   => $base . '/cookie-richtlinie',
            default     => $base . '/' . ltrim($name, '/'),
        };
    }
}

if (!function_exists('theme_nav_menu')) {
    /**
     * Render a registered navigation menu as a <ul> list.
     *
     * Mit `$mainNavContext` (`desktop`/`mobile`) folgen direkt nach den Menüeinträgen die Einträge der Core-Module
     * (Hook `main_nav`: Downloads, Knowledgebase, Newsletter) – auch wenn das Menü leer ist.
     *
     * @param string $location  Menu location slug (e.g. 'primary-nav')
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
            $sanitizedUrl = mc_safe_url((string) ($item['url'] ?? '#'));
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

if (!function_exists('mc_safe_url')) {
    /**
     * Nur sichere Linkziele: Pfad, Anker, Query, http(s), mailto:, tel: – sonst `$fallback`
     * (FILTER_VALIDATE_URL allein lässt auch Script-Schemata mit `://` durch).
     */
    function mc_safe_url(string $url, string $fallback = '#'): string
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url) === 1 || str_starts_with($url, '//')) {
            return $fallback;
        }
        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return $url;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return $url;
        }

        return in_array($scheme, ['mailto', 'tel'], true) ? $url : $fallback;
    }
}

if (!function_exists('mc_media_url')) {
    /** Bild-URL aus Inhalt/Customizer: relative Pfade („uploads/…“) werden zu „/uploads/…“, unsichere Ziele zu ''. */
    function mc_media_url(mixed $value): string
    {
        $url = trim(is_scalar($value) ? (string) $value : '');
        if ($url === '') {
            return '';
        }
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) !== 1 && !str_starts_with($url, '/')) {
            $url = '/' . $url;
        }
        $safe = mc_safe_url($url, '');

        return ($safe === '' || str_starts_with($safe, '#') || preg_match('#^(?:mailto|tel):#i', $safe) === 1) ? '' : $safe;
    }
}

if (!function_exists('mc_css_color')) {
    /** Farbwert für CSS: nur Hex/RGB(A)/HSL(A), sonst `$fallback`. */
    function mc_css_color(mixed $value, string $fallback): string
    {
        $candidate = trim(is_scalar($value) ? (string) $value : '');
        if (preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $candidate) === 1
            || preg_match('/^(?:rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/]+\)$/i', $candidate) === 1) {
            return $candidate;
        }

        return $fallback;
    }
}

if (!function_exists('mc_css_number')) {
    /** Zahl für CSS, auf [min, max] geklemmt. */
    function mc_css_number(mixed $value, float $default, float $min, float $max): string
    {
        $number = is_numeric($value) ? (float) $value : $default;
        $number = max($min, min($max, $number));

        return rtrim(rtrim(number_format($number, 3, '.', ''), '0'), '.');
    }
}

if (!function_exists('mc_format_date')) {
    /**
     * Datum für die Ausgabe (`d.m.Y` bzw. `Y-m-d` für datetime). Leer bei ungültigem Wert – bisher führte
     * date(…, strtotime(…)) unter strict_types bei ungültigen Daten zu einem TypeError.
     */
    function mc_format_date(mixed $value, string $format = 'd.m.Y'): string
    {
        $ts = is_string($value) && trim($value) !== '' ? strtotime($value) : false;

        return $ts !== false ? date($format, $ts) : '';
    }
}

if (!function_exists('mc_registration_enabled')) {
    /** true, wenn die Core-Registrierung aktiv ist (Einstellungen → Login & Registrierung). */
    function mc_registration_enabled(): bool
    {
        try {
            if (class_exists('\\CMS\\Services\\CmsAuthPageService')) {
                return \CMS\Services\CmsAuthPageService::getInstance()->isRegistrationEnabled();
            }
        } catch (\Throwable) {
        }

        return true;
    }
}

if (!function_exists('mc_get_setting')) {
    /**
     * Shorthand for ThemeCustomizer::get() with safe fallback.
     */
    function mc_get_setting(string $section, string $key, mixed $default = ''): mixed
    {
        try {
            return \CMS\Services\ThemeCustomizer::instance()->get($section, $key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (!function_exists('mc_get_flash')) {
    /**
     * Return and clear a session flash message array or null.
     *
     * @return array{type:string,message:string}|null
     */
    function mc_get_flash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!empty($_SESSION['mc_flash']) && is_array($_SESSION['mc_flash'])) {
            $flash = $_SESSION['mc_flash'];
            unset($_SESSION['mc_flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('mc_set_flash')) {
    function mc_set_flash(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION['mc_flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('mc_href')) {
    /**
     * Normalize a link target into an output-safe URL with site host.
     */
    function mc_href(string $target): string
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

if (!function_exists('mc_site_url')) {
    function mc_site_url(): string
    {
        return rtrim((string) (defined('SITE_URL') ? SITE_URL : ''), '/');
    }
}

if (!function_exists('mc_site_title')) {
    function mc_site_title(): string
    {
        try {
            return (string) \CMS\ThemeManager::instance()->getSiteTitle();
        } catch (\Throwable) {
            return 'MedCare Pro';
        }
    }
}

if (!function_exists('mc_body_class')) {
    /**
     * Server-render the <body> class string – kein JS-Mutation der Klasse vor Paint.
     */
    function mc_body_class(string ...$extra): string
    {
        $classes = ['mc-body'];

        $sticky = filter_var(
            mc_get_setting('layout', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$sticky) {
            $classes[] = 'no-sticky-header';
        }

        $showEmergency = filter_var(
            mc_get_setting('header', 'show_emergency_banner', false),
            FILTER_VALIDATE_BOOLEAN
        );
        $emergencyPhone = trim((string) mc_get_setting('header', 'emergency_phone', '112'));
        if ($showEmergency && $emergencyPhone !== '') {
            $classes[] = 'has-emergency-banner';
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

if (!function_exists('mc_safe_headline')) {
    /**
     * Erlaubt ausschließlich `<span class="hl">…</span>` als kontrolliertes
     * Inline-Markup in Überschriften. Alles andere wird HTML-escaped.
     */
    function mc_safe_headline(string $raw): string
    {
        $escaped = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
        return str_replace(
            ['&lt;span class=&quot;hl&quot;&gt;', '&lt;/span&gt;'],
            ['<span class="mc-hl">', '</span>'],
            $escaped
        );
    }
}

if (!function_exists('mc_tel_sanitize')) {
    /**
     * Reduziert eine Eingabe auf `+` und Ziffern für sichere `tel:`-URIs.
     */
    function mc_tel_sanitize(string $raw): string
    {
        $cleaned = (string) preg_replace('/[^0-9+]/', '', $raw);
        if ($cleaned === '' || $cleaned === '+') {
            return '';
        }
        return $cleaned;
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
