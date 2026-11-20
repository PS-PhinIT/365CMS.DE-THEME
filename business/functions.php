<?php
declare(strict_types=1);

/**
 * Business Presentation Theme – functions.php
 *
 * Editorial B2B / Corporate Landing Theme for 365CMS 4.0.0 / 4.1.0 on PHP 8.4.1.
 *
 * @package IT_Business_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('BUSINESS_THEME_VERSION')) {
    define('BUSINESS_THEME_VERSION', '4.1.1');
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
 * Business Theme Singleton
 */
final class IT_Business_Theme
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
        // Menü-Positionen nur über den Filter (ThemeManager::registerMenuLocation() gibt es in 4.0.0 nicht mehr).
        \CMS\Hooks::addFilter('register_menu_locations', [$this, 'registerMenuLocations']);

        // Der Core-Mitgliederbereich (/member) ist eine eigene Tabler-Shell und löst ebenfalls `head` aus:
        // dort kein Frontend-Stylesheet und keine Theme-Scripts, nur die Theme-Palette als --member-*.
        if (theme_v4_is_member_area()) {
            \CMS\Hooks::addAction('member_head', [$this, 'outputMemberTokens'], 20);
            return;
        }

        // Assets in <head>
        \CMS\Hooks::addAction('head',          [$this, 'enqueueStyles'],      1);
        \CMS\Hooks::addAction('head',          [$this, 'outputMetaTags'],     10);
        \CMS\Hooks::addAction('head',          [$this, 'outputCustomStyles'], 15);

        // Deferred script before </body>
        \CMS\Hooks::addAction('before_footer', [$this, 'enqueueScripts'],     99);
    }

    /**
     * Memberbereich (inkl. Newsletter-Widget) in der Business-Palette; das Theme hat keinen Dark Mode.
     * Primärfarbe ist das dunkle Kupfer (weiße Schrift 5,6:1); das helle Kupfer erreicht nur 3,0:1.
     */
    public function outputMemberTokens(): void
    {
        $color = fn(string $key, string $default): string =>
            biz_css_color((string) $this->customizerGet('colors', $key, $default), $default);

        echo theme_v4_member_tokens_style([
            'primary'    => $color('primary_hover', '#8a5f17'),
            'accent'     => $color('primary_color', '#c08a2e'),
            'bg'         => $color('paper_color', '#f7f5f0'),
            'card_bg'    => '#ffffff',
            'text'       => $color('text_color', '#1a2030'),
            'text_muted' => $color('muted_color', '#5a6479'),
            'border'     => $color('border_color', '#dcd9d1'),
            'focus'      => $color('ink_color', '#0c1320'),
            'radius'     => biz_css_number($this->customizerGet('layout', 'border_radius', '6'), 6, 0, 16) . 'px',
            'font'       => $this->mapFontChoice((string) $this->customizerGet('typography', 'font_family_base', 'system'), 'system'),
        ]);
    }

    // ── Assets ───────────────────────────────────────────────────────────────

    public function enqueueStyles(): void
    {
        $themeUrl = $this->themeBaseUrl();
        $href     = htmlspecialchars(
            $themeUrl . '/style.css?v=' . rawurlencode(BUSINESS_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<link rel="stylesheet" href="' . $href . '">' . "\n";
        // Gemeinsame 4.x-Integrationsstile (Downloads, Feeds, Formulare, Knowledgebase, Newsletter …) nach style.css.
        echo '<link rel="stylesheet" href="' . htmlspecialchars($themeUrl . '/css/cms-v4.css?v=' . rawurlencode(BUSINESS_THEME_VERSION), ENT_QUOTES, 'UTF-8') . '">' . "\n";
        // Schriften: keine Google-Fonts-Requests (DSGVO). Lokale Schriften liefert die Core-Schriftverwaltung,
        // sonst greift der System-Font-Stack.
    }

    public function enqueueScripts(): void
    {
        $themeUrl = $this->themeBaseUrl();
        $src      = htmlspecialchars(
            $themeUrl . '/js/navigation.js?v=' . rawurlencode(BUSINESS_THEME_VERSION),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '<script src="' . $src . '" defer></script>' . "\n";
    }

    // ── Meta ─────────────────────────────────────────────────────────────────

    /**
     * Nur noch `theme-color`: Beschreibung, Canonical, Robots und Open Graph kommen aus dem Core-SEO
     * (theme_v4_head_tags() in header.php); eigene Ausgaben wären doppelt und im Vorschaufall falsch.
     */
    public function outputMetaTags(): void
    {
        $themeColor = biz_css_color((string) $this->customizerGet('colors', 'ink_color', '#0c1320'), '#0c1320');

        echo '<meta name="theme-color" content="' . htmlspecialchars($themeColor, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    // ── Customizer-getriebene CSS-Variablen + custom_css ─────────────────────

    public function outputCustomStyles(): void
    {
        $color = fn(string $g, string $k, string $d): string =>
            biz_css_color((string) $this->customizerGet($g, $k, $d), $d);
        $num = fn(string $g, string $k, string $d, float $min, float $max): string =>
            biz_css_number($this->customizerGet($g, $k, $d), (float) $d, $min, $max);

        $baseFontChoice    = (string) $this->customizerGet('typography', 'font_family_base',    'system');
        $headingFontChoice = (string) $this->customizerGet('typography', 'font_family_heading', 'system');

        // Feste Stacks aus mapFontChoice() – nicht HTML-escapen: &quot; im <style> machte die Deklaration ungültig.
        $baseFont    = $this->mapFontChoice($baseFontChoice, 'system');
        $headingFont = $this->mapFontChoice($headingFontChoice, 'system');

        echo '<style id="biz-custom-vars"' . theme_csp_nonce_attr() . '>:root{';

        // Colors
        echo '--biz-accent:'         . $color('colors', 'primary_color', '#c08a2e') . ';';
        echo '--biz-accent-hover:'   . $color('colors', 'primary_hover', '#8a5f17') . ';';
        echo '--biz-ink:'            . $color('colors', 'ink_color',     '#0c1320') . ';';
        echo '--biz-ink-soft:'       . $color('colors', 'ink_soft',      '#18243a') . ';';
        echo '--biz-paper:'          . $color('colors', 'paper_color',   '#f7f5f0') . ';';
        echo '--biz-text:'           . $color('colors', 'text_color',    '#1a2030') . ';';
        echo '--biz-text-mute:'      . $color('colors', 'muted_color',   '#5a6479') . ';';
        echo '--biz-rule:'           . $color('colors', 'border_color',  '#dcd9d1') . ';';
        // Fokus: auf hellen Flächen dunkles Kupfer (5,2:1 auf Papier; helles Kupfer nur 2,8:1),
        // auf dunklen Flächen helles Kupfer (6,1:1 auf Ink) – siehe style.css.
        echo '--focus-ring:'         . $color('colors', 'primary_hover', '#8a5f17') . ';';
        echo '--focus-ring-on-dark:' . $color('colors', 'primary_color', '#c08a2e') . ';';

        // Typography
        echo '--biz-font-body:'      . $baseFont    . ';';
        echo '--biz-font-heading:'   . $headingFont . ';';
        echo '--biz-font-size-base:' . $num('typography', 'font_size_base',   '16', 14, 20)   . 'px;';
        echo '--biz-line-height:'    . $num('typography', 'line_height_base', '1.65', 1.3, 2.2) . ';';

        // Layout
        echo '--biz-max-width:'      . $num('layout', 'container_width', '1200', 960, 1440) . 'px;';
        echo '--biz-section-pad:'    . $num('layout', 'section_spacing', '5', 2, 9)    . 'rem;';
        echo '--biz-radius:'         . $num('layout', 'border_radius',   '6', 0, 16)    . 'px;';

        echo '}';

        // Sticky header opt-out
        $stickyHeader = filter_var(
            $this->customizerGet('header', 'enable_sticky_header', true),
            FILTER_VALIDATE_BOOLEAN
        );
        if (!$stickyHeader) {
            echo '.biz-header{position:static;}';
        }

        // Custom CSS (defensiv gegen </style>-Injection)
        $custom = $this->sanitizeCustomCss((string) $this->customizerGet('advanced', 'custom_css', ''));
        if ($custom !== '') {
            echo $custom;
        }

        echo '</style>' . "\n";
    }

    // ── Menü-Positionen ───────────────────────────────────────────────────────

    public function registerMenuLocations(array $locations): array
    {
        $locations[] = ['slug' => 'primary',      'label' => 'Hauptnavigation (Header)'];
        $locations[] = ['slug' => 'footer-nav',   'label' => 'Footer-Navigation'];
        $locations[] = ['slug' => 'footer-legal', 'label' => 'Footer Rechtliche Links'];
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
        $t = static fn(string $de, string $en): string => theme_v4_t($de, $en);
        $defaults = [
            'primary' => [
                ['label' => $t('Start', 'Home'),            'url' => '/'],
                ['label' => $t('Über uns', 'About us'),     'url' => '/#ueber-uns'],
                ['label' => $t('Leistungen', 'Services'),   'url' => '/#leistungen'],
                ['label' => $t('Kontakt', 'Contact'),       'url' => '/contact'],
            ],
            'footer-nav' => [
                ['label' => $t('Startseite', 'Home'),       'url' => '/'],
                ['label' => $t('Über uns', 'About us'),     'url' => '/#ueber-uns'],
                ['label' => $t('Leistungen', 'Services'),   'url' => '/#leistungen'],
                ['label' => 'Newsletter',                   'url' => '/newsletter'],
                // 365CMS 4.2.0: Basispfad der Community (nur bei aktivem Modul, sonst herausgefiltert).
                ['label' => 'Community',                    'url' => '/' . theme_v4_community_base_path()],
                ['label' => $t('Kontakt', 'Contact'),       'url' => '/contact'],
            ],
            'footer-legal' => [
                ['label' => $t('Impressum', 'Legal notice'),   'url' => '/impressum'],
                ['label' => $t('Datenschutz', 'Privacy policy'), 'url' => '/datenschutz'],
                ['label' => $t('AGB', 'Terms'),             'url' => '/agb'],
            ],
        ];

        $links = array_map(
            static fn(array $link): array => ['label' => $link['label'], 'url' => theme_v4_site_url($link['url'])],
            $defaults[$location] ?? []
        );

        // Der Abschnitt `#leistungen` der Startseite existiert nur mit gepflegten Leistungen.
        if (!biz_has_services()) {
            $links = array_values(array_filter(
                $links,
                static fn(array $link): bool => !str_contains((string) $link['url'], '#leistungen')
            ));
        }

        return theme_v4_filter_links($links);
    }

    /**
     * Theme-Konfiguration aus theme.json (settings-Block) lesen
     */
    public function getConfig(string $key, mixed $default = ''): mixed
    {
        static $config = null;

        if ($config === null) {
            $jsonFile = __DIR__ . '/theme.json'; // unabhängig vom Ordnernamen und von THEME_PATH
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

    private function mapFontChoice(string $choice, string $fallback): string
    {
        $systemStack = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        return match ($choice) {
            'system'       => $systemStack,
            'inter'        => 'Inter, ' . $systemStack,
            'source-sans'  => '"Source Sans 3", ' . $systemStack,
            'source-serif' => '"Source Serif 4", Georgia, ui-serif, "Times New Roman", serif',
            default        => $fallback === 'system' ? $systemStack : $fallback,
        };
    }

    private function sanitizeCustomCss(string $raw): string
    {
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
}

IT_Business_Theme::instance();

// ── Global Helper ──────────────────────────────────────────────────────────────

if (!function_exists('biz_css_color')) {
    function biz_css_color(string $value, string $fallback): string
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

if (!function_exists('biz_css_number')) {
    function biz_css_number(mixed $value, float $default, float $min, float $max): string
    {
        $number = is_numeric($value) ? (float) $value : $default;
        $number = max($min, min($max, $number));

        return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
    }
}

if (!function_exists('biz_safe_url')) {
    function biz_safe_url(string $url, string $fallback = ''): string
    {
        if (function_exists('theme_safe_url')) {
            return theme_safe_url($url, $fallback);
        }

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

if (!function_exists('biz_nav_menu')) {
    /**
     * Menü einer Position als Liste. Ist im Admin nichts gepflegt, greifen die Standardeinträge des Themes
     * (IT_Business_Theme::defaultMenu(), nur vorhandene Ziele). Mit `$mainNavContext` (`desktop`/`mobile`)
     * folgen direkt nach den Menüeinträgen die Einträge der Core-Module (Hook `main_nav`) – auch bei leerem Menü.
     */
    function biz_nav_menu(string $location = 'primary', string $mainNavContext = ''): void
    {
        try {
            $items = \CMS\ThemeManager::instance()->getMenu($location);
        } catch (\Throwable) {
            $items = [];
        }
        if (!is_array($items) || $items === []) {
            $items = IT_Business_Theme::defaultMenu($location);
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
            $sanitizedUrl = biz_safe_url((string) ($item['url'] ?? '#'), '#');
            // Aktiv: gleicher Pfad (Startseite nur exakt); Anker, mailto: und tel: nie.
            $isActive = !str_contains($sanitizedUrl, '#')
                && preg_match('/^(?:mailto|tel):/i', $sanitizedUrl) !== 1
                && theme_v4_is_current_path($sanitizedUrl);
            $class   = $isActive ? ' class="active"' : '';
            $target  = !empty($item['target']) && $item['target'] === '_blank'
                ? ' target="_blank" rel="noopener noreferrer"'
                : '';
            $url   = htmlspecialchars($sanitizedUrl, ENT_QUOTES, 'UTF-8');
            // Der Core liefert die Beschriftung im Feld `title`, ältere Menüs/Standardeinträge in `label`.
            $label = htmlspecialchars((string) ($item['label'] ?? $item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
            echo '<li' . $class . '><a href="' . $url . '"' . $target . ($isActive ? ' aria-current="page"' : '') . '>' . $label . '</a></li>' . "\n";
        }
        echo $coreItems;
        echo '</ul>' . "\n";
    }
}

if (!function_exists('biz_format_date')) {
    /** Datum je Seitensprache: deutsch `02.11.2026`, englisch `Nov 2, 2026`; ungültige Werte ergeben einen leeren String. */
    function biz_format_date(mixed $value, string $locale = 'de'): string
    {
        $ts = is_string($value) && trim($value) !== '' ? strtotime($value) : false;
        if ($ts === false) {
            return '';
        }

        return $locale === 'en' ? date('M j, Y', $ts) : date('d.m.Y', $ts);
    }
}

if (!function_exists('biz_has_services')) {
    /** true, wenn im Customizer Leistungen gepflegt sind (Abschnitt `#leistungen` existiert nur dann). */
    function biz_has_services(): bool
    {
        static $has = null;

        return $has ??= biz_parse_lines((string) biz_config('services_list', ''), 3, 12) !== [];
    }
}

if (!function_exists('biz_parse_lines')) {
    /**
     * Zerlegt ein Customizer-Textfeld in Zeilen mit `$parts` durch `|` getrennten Spalten (leere Zeilen und
     * Zeilen ohne Pflichtspalten entfallen, höchstens `$max` Zeilen, Spalten auf 400 Zeichen begrenzt).
     *
     * @return list<list<string>>
     */
    function biz_parse_lines(string $text, int $parts = 1, int $max = 12): array
    {
        $rows = [];
        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $cells = array_map('trim', explode('|', $line, $parts));
            if (count($cells) < $parts || in_array('', array_slice($cells, 0, $parts === 3 ? 2 : $parts), true)) {
                continue;
            }
            $rows[] = array_map(static fn(string $c): string => function_exists('mb_substr') ? mb_substr($c, 0, 400) : substr($c, 0, 400), $cells);
            if (count($rows) >= $max) {
                break;
            }
        }

        return $rows;
    }
}

if (!function_exists('biz_href')) {
    function biz_href(string $target): string
    {
        $trimmed = trim($target);
        if ($trimmed === '') {
            return biz_site_url() . '/';
        }
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return biz_safe_url($trimmed, biz_site_url() . '/');
        }
        if (str_starts_with($trimmed, 'mailto:') || str_starts_with($trimmed, 'tel:')) {
            return biz_safe_url($trimmed, biz_site_url() . '/');
        }
        if (str_starts_with($trimmed, '#')) {
            $anchor = biz_safe_url($trimmed, '');
            return $anchor !== '' ? biz_site_url() . '/' . $anchor : biz_site_url() . '/';
        }
        return biz_safe_url(biz_site_url() . '/' . ltrim($trimmed, '/'), biz_site_url() . '/');
    }
}

if (!function_exists('biz_safe_headline')) {
    function biz_safe_headline(string $headline): string
    {
        $escaped = htmlspecialchars($headline, ENT_QUOTES, 'UTF-8');
        $escaped = str_replace(
            ['&lt;span class=&quot;highlight&quot;&gt;', '&lt;/span&gt;'],
            ['<span class="highlight">', '</span>'],
            $escaped
        );
        return $escaped;
    }
}

if (!function_exists('biz_config')) {
    /**
     * Liest Theme-Inhalte – zuerst aus dem Customizer (Gruppe `biz_content`
     * bzw. `biz_hero`), fällt sonst auf theme.json `settings` zurück.
     */
    function biz_config(string $key, mixed $default = ''): mixed
    {
        static $heroKeys = [
            'hero_badge'                 => true,
            'hero_headline'              => true,
            'hero_text'                  => true,
            'hero_cta_primary_label'     => true,
            'hero_cta_primary_url'       => true,
            'hero_cta_secondary_label'   => true,
            'hero_cta_secondary_url'     => true,
        ];
        $group = isset($heroKeys[$key]) ? 'biz_hero' : 'biz_content';

        try {
            $val = \CMS\Services\ThemeCustomizer::instance()->get($group, $key, null);
            if (is_string($val)) {
                if ($val !== '') {
                    return $val;
                }
            } elseif ($val !== null) {
                return $val;
            }
        } catch (\Throwable) {
            // Customizer nicht verfügbar – fällt unten auf theme.json zurück.
        }

        return IT_Business_Theme::instance()->getConfig($key, $default);
    }
}

if (!function_exists('biz_site_url')) {
    function biz_site_url(): string
    {
        $siteUrl = rtrim(biz_safe_url((string) SITE_URL, '/'), '/');

        return $siteUrl !== '' ? $siteUrl : '/';
    }
}

if (!function_exists('biz_site_title')) {
    function biz_site_title(): string
    {
        return htmlspecialchars(\CMS\ThemeManager::instance()->getSiteTitle(), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('biz_body_class')) {
    /**
     * Gibt den serverseitig komponierten <body>-Class-String zurück
     * (statt inline JS, das später die Klasse setzt).
     */
    function biz_body_class(string ...$extra): string
    {
        $classes = ['biz-body'];

        try {
            $sticky = filter_var(
                \CMS\Services\ThemeCustomizer::instance()->get('header', 'enable_sticky_header', true),
                FILTER_VALIDATE_BOOLEAN
            );
        } catch (\Throwable) {
            $sticky = true;
        }
        if ($sticky) {
            $classes[] = 'has-sticky-header';
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
