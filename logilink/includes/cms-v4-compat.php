<?php
/**
 * 365CMS 4.0.0 / 4.1.0 / 4.2.0 – gemeinsame Kompatibilitätsschicht der öffentlichen Themes.
 *
 * Die Datei ist in allen Public Themes identisch (Stand: THEME_V4_COMPAT_VERSION). Alle Funktionen sind
 * über function_exists() geschützt und greifen ausschließlich auf öffentliche Core-APIs zu:
 *
 *  - Fehlerseiten:   theme_v4_error_context(), theme_v4_error_texts(), theme_v4_standalone_open()/_close()
 *  - Navigation:     theme_v4_main_nav() löst den Core-Hook `main_nav` aus (Downloads, Knowledgebase, Newsletter)
 *  - Standardlinks:  theme_v4_link_available(), theme_v4_filter_links()
 *  - Optionen:       theme_v4_option(), theme_v4_options() über OptionStore (ein Request-Cache statt Einzelabfragen)
 *  - SEO/Vorschau:   theme_v4_head_tags(), theme_v4_document_title(), theme_v4_is_preview()
 *  - Konto:          theme_v4_logout_form() (CSRF-geschützter POST auf /logout)
 *  - Module:         theme_v4_render_module_view() für Overrides unter knowledgebase/ und newsletter/
 *  - Medien:         theme_v4_focal_position(), theme_v4_focal_style_attr() (Fokus-Punkt, 4.1.0)
 *  - Memberbereich:  theme_v4_is_member_area(), theme_v4_member_tokens_style()
 *  - 4.2.0:          theme_v4_module_enabled(), theme_v4_community_available()/_url()/_link() (Modul `community`,
 *                    fail-closed), theme_v4_user_can() (Capabilities, Rolle `moderator`), theme_v4_slug()
 *                    (CMS\Slug::fromText() mit Rückfall); `community` in theme_v4_render_module_view()
 *
 * Kein Datenbankzugriff in den Fehlerseiten-Helfern: eigenständige Fehlerseiten (Wartungsmodus,
 * Fatal-Handler) laufen ohne Datenbank.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('THEME_V4_COMPAT_VERSION')) {
    define('THEME_V4_COMPAT_VERSION', '1.1.0');
}

// ── Basis ────────────────────────────────────────────────────────────────────

if (!function_exists('theme_v4_e')) {
    /** HTML-Escaping für Text und Attribute. */
    function theme_v4_e(mixed $value): string
    {
        return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('theme_v4_site_url')) {
    /** SITE_URL ohne abschließenden Schrägstrich. */
    function theme_v4_site_url(string $path = ''): string
    {
        $base = rtrim(defined('SITE_URL') ? (string) SITE_URL : '', '/');
        if ($path === '') {
            return $base;
        }

        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('theme_v4_request_path')) {
    /** Pfad der aktuellen Anfrage ohne Unterverzeichnis von SITE_URL, ohne Query, ohne abschließenden Schrägstrich. */
    function theme_v4_request_path(): string
    {
        $path = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?? '/');
        $sitePath = rtrim((string) (parse_url(theme_v4_site_url(), PHP_URL_PATH) ?? ''), '/');
        if ($sitePath !== '' && ($path === $sitePath || str_starts_with($path, $sitePath . '/'))) {
            $path = substr($path, strlen($sitePath));
        }
        $path = '/' . trim($path, '/');

        return $path;
    }
}

if (!function_exists('theme_v4_locale')) {
    /** Inhaltssprache der Anfrage: `en` unter /en/…, sonst `de`. */
    function theme_v4_locale(mixed $contentLocale = null): string
    {
        if (is_string($contentLocale) && in_array($contentLocale, ['de', 'en'], true)) {
            return $contentLocale;
        }
        $path = theme_v4_request_path();

        return ($path === '/en' || str_starts_with($path, '/en/')) ? 'en' : 'de';
    }
}

if (!function_exists('theme_v4_t')) {
    /** Kleiner zweisprachiger Textwähler für Theme-Texte ohne eigenes Sprachpaket. */
    function theme_v4_t(string $de, string $en, mixed $contentLocale = null): string
    {
        return theme_v4_locale($contentLocale) === 'en' ? $en : $de;
    }
}

// ── Optionen (OptionStore statt Einzelabfragen) ──────────────────────────────

if (!function_exists('theme_v4_options')) {
    /**
     * Mehrere Einstellungen aus `cms_settings` in einem Zugriff (OptionStore puffert je Request).
     *
     * @param list<string> $names
     * @return array<string, string>
     */
    function theme_v4_options(array $names, string $default = ''): array
    {
        $result = array_fill_keys($names, $default);
        try {
            if (class_exists('\\CMS\\Services\\OptionStore')) {
                foreach (\CMS\Services\OptionStore::getInstance()->getMany($names, $default) as $name => $value) {
                    $result[(string) $name] = (string) ($value ?? $default);
                }
            }
        } catch (\Throwable) {
        }

        return $result;
    }
}

if (!function_exists('theme_v4_option')) {
    /** Einzelne Einstellung über den OptionStore. */
    function theme_v4_option(string $name, string $default = ''): string
    {
        try {
            if (class_exists('\\CMS\\Services\\OptionStore')) {
                return (string) (\CMS\Services\OptionStore::getInstance()->get($name, $default) ?? $default);
            }
        } catch (\Throwable) {
        }

        return $default;
    }
}

if (!function_exists('theme_v4_use_local_fonts')) {
    /** true, wenn „Schriften lokal einbinden“ (privacy_use_local_fonts) aktiv ist. */
    function theme_v4_use_local_fonts(): bool
    {
        return theme_v4_option('privacy_use_local_fonts', '0') === '1';
    }
}

if (!function_exists('theme_v4_local_asset_url')) {
    /** Nur lokale URLs (Pfad oder eigener Origin) – sonst leer. Für Logo/Favicon-Fallbacks. */
    function theme_v4_local_asset_url(string $url): string
    {
        $url = trim($url);
        // Backslashes werten Browser wie „/“: `/\evil.example` wäre eine protokollrelative, fremde Adresse.
        if ($url === '' || str_starts_with($url, '//') || str_contains($url, '\\') || preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return '';
        }
        if (str_starts_with($url, '/')) {
            return $url;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $siteHost = strtolower((string) parse_url(theme_v4_site_url(), PHP_URL_HOST));
        if (in_array($scheme, ['http', 'https'], true) && $host !== '' && $host === $siteHost) {
            return $url;
        }

        return '';
    }
}

if (!function_exists('theme_v4_site_logo')) {
    /** Logo: Theme-Einstellung, sonst Core-Option `site_logo`. */
    function theme_v4_site_logo(string $themeLogo = ''): string
    {
        $logo = theme_v4_local_asset_url($themeLogo);

        return $logo !== '' ? $logo : theme_v4_local_asset_url(theme_v4_option('site_logo'));
    }
}

if (!function_exists('theme_v4_favicon_tag')) {
    /** `<link rel="icon">` aus der Core-Option `site_favicon` (leer, wenn nicht gesetzt). */
    function theme_v4_favicon_tag(string $themeFavicon = ''): string
    {
        $icon = theme_v4_local_asset_url($themeFavicon);
        if ($icon === '') {
            $icon = theme_v4_local_asset_url(theme_v4_option('site_favicon'));
        }

        return $icon !== '' ? '<link rel="icon" href="' . theme_v4_e($icon) . '">' . "\n" : '';
    }
}

// ── Navigation: Core-Hook main_nav ───────────────────────────────────────────

if (!function_exists('theme_v4_capture_action')) {
    /** Gibt die Ausgabe eines Hooks als String zurück. */
    function theme_v4_capture_action(string $hook, mixed ...$args): string
    {
        if (!class_exists('\\CMS\\Hooks')) {
            return '';
        }
        $level = ob_get_level();
        ob_start();
        try {
            \CMS\Hooks::doAction($hook, ...$args);
        } catch (\Throwable) {
        }
        $html = '';
        while (ob_get_level() > $level) {
            $html = (string) ob_get_clean() . $html;
        }

        return $html;
    }
}

if (!function_exists('theme_v4_main_nav')) {
    /**
     * Löst den Core-Hook `main_nav` aus (Kontext `desktop` oder `mobile`) – direkt nach den regulären Menüeinträgen.
     *
     * Der Core liefert reine `<a>`-Elemente (aktiv mit `aria-current="page"`). Für Listen-Navigationen
     * werden sie in `<li>` gesetzt und erhalten die Klassen des Themes.
     *
     * @param array{wrap?:string,item_class?:string,active_item_class?:string,link_class?:string,active_link_class?:string} $options
     */
    function theme_v4_main_nav(string $context = 'desktop', array $options = []): void
    {
        $context = $context === 'mobile' ? 'mobile' : 'desktop';
        $html = theme_v4_capture_action('main_nav', $context);
        if (trim($html) === '') {
            return;
        }

        $wrapLi = ($options['wrap'] ?? '') === 'li';
        $itemClass = trim((string) ($options['item_class'] ?? ''));
        $activeItemClass = trim((string) ($options['active_item_class'] ?? ''));
        $linkClass = trim((string) ($options['link_class'] ?? ''));
        $activeLinkClass = trim((string) ($options['active_link_class'] ?? ''));

        $pattern = '#<a\b[^>]*>.*?</a>#is';
        $onlyLinks = preg_match_all($pattern, $html, $matches) > 0
            && trim((string) preg_replace($pattern, '', $html)) === '';

        if (!$onlyLinks) {
            echo $wrapLi ? '<li class="' . theme_v4_e(trim($itemClass . ' theme-v4-nav-item')) . '">' . $html . '</li>' : $html;
            return;
        }

        foreach ($matches[0] as $link) {
            $active = str_contains($link, 'aria-current="page"');
            $classes = trim($linkClass . ($active && $activeLinkClass !== '' ? ' ' . $activeLinkClass : ''));
            if ($classes !== '') {
                $link = preg_match('/^<a\b[^>]*\bclass="/i', $link) === 1
                    ? (string) preg_replace('/^(<a\b[^>]*\bclass=")/i', '$1' . theme_v4_e($classes) . ' ', $link, 1)
                    : (string) preg_replace('/^<a\b/i', '<a class="' . theme_v4_e($classes) . '"', $link, 1);
            }
            if ($wrapLi) {
                $liClasses = trim($itemClass . ' theme-v4-nav-item' . ($active && $activeItemClass !== '' ? ' ' . $activeItemClass : ''));
                echo '<li class="' . theme_v4_e($liClasses) . '">' . $link . '</li>';
            } else {
                echo $link;
            }
        }
    }
}

if (!function_exists('theme_v4_is_current_path')) {
    /** Aktiver Menüpunkt: exakter Pfad oder Unterpfad (Startseite nur exakt). */
    function theme_v4_is_current_path(string $url): bool
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');
        $host = (string) (parse_url($url, PHP_URL_HOST) ?? '');
        if ($host !== '' && strcasecmp($host, (string) parse_url(theme_v4_site_url(), PHP_URL_HOST)) !== 0) {
            return false;
        }
        $sitePath = rtrim((string) (parse_url(theme_v4_site_url(), PHP_URL_PATH) ?? ''), '/');
        if ($sitePath !== '' && ($path === $sitePath || str_starts_with($path, $sitePath . '/'))) {
            $path = substr($path, strlen($sitePath));
        }
        $path = '/' . trim($path, '/');
        $current = theme_v4_request_path();
        if ($path === '/') {
            return $current === '/';
        }

        return $current === $path || str_starts_with($current, $path . '/');
    }
}

// ── Standardlinks nur zu existierenden Seiten/Routen ─────────────────────────

if (!function_exists('theme_v4_link_available')) {
    /**
     * true, wenn ein interner Standardlink auf eine vorhandene Route oder veröffentlichte Seite zeigt.
     * Externe URLs, Anker und Mail-/Telefonlinks gelten als verfügbar.
     */
    function theme_v4_link_available(string $url): bool
    {
        static $cache = [];

        $url = trim($url);
        if ($url === '' || $url === '#') {
            return false;
        }
        if (preg_match('#^(?:mailto:|tel:|\#)#i', $url) === 1) {
            return true;
        }
        $host = (string) (parse_url($url, PHP_URL_HOST) ?? '');
        if ($host !== '' && strcasecmp($host, (string) parse_url(theme_v4_site_url(), PHP_URL_HOST)) !== 0) {
            return true;
        }

        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '/');
        $sitePath = rtrim((string) (parse_url(theme_v4_site_url(), PHP_URL_PATH) ?? ''), '/');
        if ($sitePath !== '' && ($path === $sitePath || str_starts_with($path, $sitePath . '/'))) {
            $path = substr($path, strlen($sitePath));
        }
        $locale = 'de';
        if (preg_match('#^/en(/.*)?$#', $path, $m) === 1) {
            $locale = 'en';
            $path = $m[1] ?? '/';
        }
        $slug = trim($path, '/');
        $key = $locale . ':' . $slug;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        // Feste Routen des Themes/Cores (4.0.0: Downloads, Knowledgebase, Newsletter, Kontakt sind Core-Module).
        $coreRoutes = [
            '' => true, 'blog' => true, 'search' => true, 'suche' => true, 'login' => true, 'register' => true,
            'forgot-password' => true, 'cms-login' => true, 'cms-register' => true, 'member' => true,
            'sitemap' => true, 'autoren' => true, 'kategorie' => true, 'tag' => true, 'feed' => true,
            'contact' => true, 'kontakt' => true,
            'newsletter' => class_exists('\\CMS\\Services\\Newsletter\\NewsletterFrontendController'),
            'downloads' => class_exists('\\CMS\\Services\\Downloads\\DownloadFrontendController'),
            'kb' => class_exists('\\CMS\\Services\\Knowledgebase\\Http\\PublicController'),
            'glossar' => class_exists('\\CMS\\Services\\Knowledgebase\\Http\\PublicController'),
        ];
        $first = explode('/', $slug)[0];

        // 4.2.0: Community-Modul (Basispfad einstellbar, Standard `forum`), wenn Modul und Vorlagen da sind.
        // Sonst weiter wie bisher (z. B. eine Seite oder ein Plugin-Forum unter demselben Pfad).
        if ($first === theme_v4_community_base_path() && theme_v4_community_available()) {
            return $cache[$key] = true;
        }

        if (array_key_exists($slug, $coreRoutes)) {
            return $cache[$key] = (bool) $coreRoutes[$slug];
        }
        if (in_array($first, ['blog', 'kategorie', 'tag', 'member', 'downloads', 'kb', 'glossar', 'newsletter'], true)) {
            return $cache[$key] = (bool) ($coreRoutes[$first] ?? true);
        }

        // Feed-Aggregator: Archiv-Slug nur bei eingeschaltetem Aggregator.
        try {
            if (class_exists('\\CMS\\Services\\Feed\\AggregatorSettings')) {
                $feedSlug = (string) \CMS\Services\Feed\AggregatorSettings::archiveSlug();
                if ($feedSlug !== '' && ($slug === $feedSlug || str_starts_with($slug, $feedSlug . '/'))) {
                    return $cache[$key] = \CMS\Services\Feed\AggregatorSettings::isEnabled();
                }
            }
        } catch (\Throwable) {
        }

        // Sonst: veröffentlichte Seite mit diesem Slug.
        try {
            $page = \CMS\PageManager::instance()->getPageBySlug($slug, $locale);
            $cache[$key] = is_array($page) && (string) ($page['status'] ?? '') === 'published';
        } catch (\Throwable) {
            $cache[$key] = false;
        }

        return $cache[$key];
    }
}

if (!function_exists('theme_v4_filter_links')) {
    /**
     * Entfernt Standardlinks, deren Ziel nicht existiert.
     *
     * @param list<array{label:string,url:string}> $links
     * @return list<array{label:string,url:string}>
     */
    function theme_v4_filter_links(array $links): array
    {
        return array_values(array_filter($links, static fn (array $link): bool => theme_v4_link_available((string) ($link['url'] ?? ''))));
    }
}

// ── Fehlerseiten (Router-Vertrag 4.0.0) ──────────────────────────────────────

if (!function_exists('theme_v4_error_context')) {
    /**
     * Normalisiert die Variablen des Routers für error.php/<status>.php.
     *
     * Der Router übergibt `error_code`, `error_title`, `error_message`, bei eigenständigen Seiten zusätzlich
     * `error_standalone` und bei 503 `error_retry_after`; ältere Aufrufer nutzen camelCase (`errorCode` …).
     * Setzt den HTTP-Status (403/429/503 bleiben erhalten) und bei 429/503 `Retry-After`.
     *
     * @param array<string, mixed> $vars get_defined_vars() des Templates
     * @return array{code:int,title:string,message:string,standalone:bool,retry_after:int}
     */
    function theme_v4_error_context(array $vars, int $defaultCode = 500): array
    {
        $code = (int) ($vars['error_code'] ?? $vars['errorCode'] ?? $vars['statusCode'] ?? $defaultCode);
        if ($code < 400 || $code > 599) {
            $code = $defaultCode;
        }
        $title = trim((string) ($vars['error_title'] ?? $vars['errorTitle'] ?? ''));
        $message = trim((string) ($vars['error_message'] ?? $vars['errorMessage'] ?? ''));
        $retryAfter = max(0, (int) ($vars['error_retry_after'] ?? $vars['errorRetryAfter'] ?? 0));
        $standalone = !empty($vars['error_standalone']) || !empty($vars['errorStandalone']);

        if (!headers_sent()) {
            http_response_code($code);
            header('Cache-Control: no-store', true);
            if ($retryAfter > 0 && in_array($code, [429, 503], true)) {
                header('Retry-After: ' . $retryAfter, true);
            }
        }

        return [
            'code' => $code,
            'title' => $title,
            'message' => $message,
            'standalone' => $standalone,
            'retry_after' => $retryAfter,
        ];
    }
}

if (!function_exists('theme_v4_error_texts')) {
    /**
     * Standardtexte je Status (ohne Datenbank, ohne Sprachpaket).
     *
     * @return array{title:string,message:string}
     */
    function theme_v4_error_texts(int $code, string $locale = 'de'): array
    {
        $en = $locale === 'en';
        $texts = [
            400 => ['Ungültige Anfrage', 'Die Anfrage konnte nicht verarbeitet werden.', 'Bad request', 'The request could not be processed.'],
            401 => ['Anmeldung erforderlich', 'Bitte melde dich an, um diese Seite zu sehen.', 'Sign-in required', 'Please sign in to view this page.'],
            403 => ['Zugriff verweigert', 'Für diese Seite fehlt die Berechtigung, oder das Formular ist abgelaufen. Bitte lade die Seite neu.', 'Access denied', 'You are not allowed to view this page, or the form has expired. Please reload the page.'],
            404 => ['Seite nicht gefunden', 'Die angeforderte Seite existiert nicht oder wurde verschoben.', 'Page not found', 'The requested page does not exist or has been moved.'],
            405 => ['Methode nicht erlaubt', 'Diese Aktion ist für die Adresse nicht vorgesehen.', 'Method not allowed', 'This action is not available for this address.'],
            410 => ['Inhalt entfernt', 'Dieser Inhalt ist nicht mehr verfügbar.', 'Gone', 'This content is no longer available.'],
            429 => ['Zu viele Anfragen', 'Bitte warte einen Moment und versuche es dann erneut.', 'Too many requests', 'Please wait a moment and try again.'],
            500 => ['Serverfehler', 'Ein unerwarteter Fehler ist aufgetreten. Bitte versuche es später erneut.', 'Server error', 'An unexpected error occurred. Please try again later.'],
            502 => ['Fehlerhaftes Gateway', 'Ein vorgelagerter Dienst hat nicht korrekt geantwortet.', 'Bad gateway', 'An upstream service did not respond correctly.'],
            503 => ['Wartungsarbeiten', 'Die Website wird gerade gewartet und ist in Kürze wieder erreichbar.', 'Maintenance', 'The website is undergoing maintenance and will be back shortly.'],
        ];
        $row = $texts[$code] ?? ($code >= 500 ? $texts[500] : $texts[400]);

        return $en ? ['title' => $row[2], 'message' => $row[3]] : ['title' => $row[0], 'message' => $row[1]];
    }
}

if (!function_exists('theme_v4_theme_url')) {
    /** URL des Theme-Verzeichnisses (ThemeManager, ohne Datenbank-Fallback über SITE_URL). */
    function theme_v4_theme_url(string $themeDir): string
    {
        try {
            if (class_exists('\\CMS\\ThemeManager')) {
                $url = rtrim(\CMS\ThemeManager::instance()->getThemeUrl(), '/');
                if ($url !== '' && str_ends_with($url, '/' . basename($themeDir))) {
                    return $url;
                }
            }
        } catch (\Throwable) {
        }

        return theme_v4_site_url('themes/' . rawurlencode(basename($themeDir)));
    }
}

if (!function_exists('theme_v4_standalone_open')) {
    /**
     * Beginn eines vollständigen Dokuments für eigenständige Fehler-/Wartungsseiten (ohne Header/Footer,
     * ohne Hooks, ohne Datenbank).
     *
     * @param list<string> $stylesheets absolute URLs
     */
    function theme_v4_standalone_open(string $title, array $stylesheets, string $locale = 'de', string $bodyClass = ''): void
    {
        echo '<!DOCTYPE html>' . "\n" . '<html lang="' . theme_v4_e($locale === 'en' ? 'en' : 'de') . '">' . "\n<head>\n";
        echo '<meta charset="UTF-8">' . "\n";
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
        echo '<meta name="robots" content="noindex, nofollow">' . "\n";
        echo '<title>' . theme_v4_e($title) . '</title>' . "\n";
        foreach ($stylesheets as $href) {
            echo '<link rel="stylesheet" href="' . theme_v4_e($href) . '">' . "\n";
        }
        echo "</head>\n" . '<body class="' . theme_v4_e(trim('error-standalone ' . $bodyClass)) . '">' . "\n";
    }
}

if (!function_exists('theme_v4_standalone_close')) {
    function theme_v4_standalone_close(): void
    {
        echo "\n</body>\n</html>\n";
    }
}

if (!function_exists('theme_v4_safe_back_url')) {
    /** Referer nur, wenn er von dieser Website stammt (http/https). */
    function theme_v4_safe_back_url(): string
    {
        $back = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $siteHost = (string) parse_url(theme_v4_site_url(), PHP_URL_HOST);
        if ($back === '' || $siteHost === '' || str_contains($back, '\\') || preg_match('/[\x00-\x1F\x7F]/', $back) === 1
            || strcasecmp((string) parse_url($back, PHP_URL_HOST), $siteHost) !== 0
            || !in_array(strtolower((string) parse_url($back, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            return '';
        }

        return $back;
    }
}

// ── SEO, Titel, Vorschau ─────────────────────────────────────────────────────

if (!function_exists('theme_v4_is_preview')) {
    /**
     * Vorschau-/Entwurfskontext: Vorschau-Link (`$isPreview`), angemeldete Entwurfsansicht oder nicht
     * veröffentlichter Inhalt. Dann noindex und keine strukturierten Daten.
     *
     * @param array<string, mixed> $vars Variablen des Templates (header.php erhält die Render-Daten)
     */
    function theme_v4_is_preview(array $vars = []): bool
    {
        if (!empty($vars['isPreview']) || !empty($vars['is_preview'])) {
            return true;
        }
        foreach (['post', 'page'] as $key) {
            $item = $vars[$key] ?? ($GLOBALS[$key] ?? null);
            $status = is_array($item) ? ($item['status'] ?? null) : (is_object($item) ? ($item->status ?? null) : null);
            if (is_string($status) && $status !== '' && $status !== 'published') {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('theme_v4_head_tags')) {
    /**
     * SEO-Ausgabe des Cores (Beschreibung, Robots, Canonical, hreflang, Open Graph, Twitter, JSON-LD).
     * Im Vorschaukontext ohne Robots-/JSON-LD-Ausgabe des Cores, stattdessen `noindex, nofollow`.
     */
    function theme_v4_head_tags(bool $preview = false, string $fallbackDescription = ''): string
    {
        $html = '';
        try {
            if (class_exists('\\CMS\\Services\\SEOService')) {
                $seo = \CMS\Services\SEOService::getInstance();
                $html = method_exists($seo, 'renderCurrentHeadTags') ? (string) $seo->renderCurrentHeadTags() : '';
            }
        } catch (\Throwable) {
            $html = '';
        }
        if ($html === '' && trim($fallbackDescription) !== '') {
            $html = '<meta name="description" content="' . theme_v4_e(trim($fallbackDescription)) . '">' . "\n";
        }
        if ($preview) {
            $html = (string) preg_replace('#<meta\s+name=["\']robots["\'][^>]*>\s*#i', '', $html);
            $html = (string) preg_replace('#<script\b[^>]*application/ld\+json[^>]*>.*?</script>\s*#is', '', $html);
            $html = '<meta name="robots" content="noindex, nofollow">' . "\n" . $html;
        }

        return $html;
    }
}

if (!function_exists('theme_v4_document_title')) {
    /**
     * Dokumenttitel: übergebener Seitentitel (Module, Fehlerseiten) › Meta-Titel des Cores › Site-Titel.
     */
    function theme_v4_document_title(mixed $pageTitle = null): string
    {
        $siteTitle = '';
        try {
            $siteTitle = (string) \CMS\ThemeManager::instance()->getSiteTitle();
        } catch (\Throwable) {
        }
        if (is_string($pageTitle) && trim($pageTitle) !== '') {
            $title = trim($pageTitle);
            return $siteTitle !== '' && !str_contains($title, $siteTitle) ? $title . ' – ' . $siteTitle : $title;
        }
        try {
            if (class_exists('\\CMS\\Services\\SEOService')) {
                $payload = \CMS\Services\SEOService::getInstance()->getCurrentSeoPayload();
                $title = trim((string) ($payload['title'] ?? ''));
                if ($title !== '') {
                    return $title;
                }
            }
        } catch (\Throwable) {
        }
        try {
            if (class_exists('\\CMS\\Hooks')) {
                $filtered = trim((string) \CMS\Hooks::applyFilters('page_title', $siteTitle));
                if ($filtered !== '') {
                    return $filtered;
                }
            }
        } catch (\Throwable) {
        }

        return $siteTitle;
    }
}

// ── Konto: CSRF-geschützter Logout ───────────────────────────────────────────

if (!function_exists('theme_v4_logout_form')) {
    /** Logout als POST-Formular mit CSRF-Token der Aktion `logout` (kein GET-Link). */
    function theme_v4_logout_form(string $label, string $buttonClass = '', string $formClass = ''): string
    {
        try {
            $token = (string) \CMS\Security::instance()->generateToken('logout');
        } catch (\Throwable) {
            return '';
        }

        return '<form method="post" action="' . theme_v4_e(theme_v4_site_url('logout')) . '" class="' . theme_v4_e(trim('theme-v4-logout ' . $formClass)) . '">'
            . '<input type="hidden" name="csrf_token" value="' . theme_v4_e($token) . '">'
            . '<button type="submit" class="' . theme_v4_e($buttonClass) . '">' . theme_v4_e($label) . '</button>'
            . '</form>';
    }
}

// ── Core-Module (Knowledgebase, Newsletter): Overrides ───────────────────────

if (!function_exists('theme_v4_render_module_view')) {
    /**
     * Rendert eine Core-View und passt den `<main>`-Landmark an das Theme an.
     *
     * Die Views `knowledgebase/*` und `newsletter/*` (ab 4.2.0 auch `community/*`) bringen ein eigenes `<main>` mit. Öffnet das Theme
     * `<main>` bereits in header.php (THEME_V4_HEADER_HAS_MAIN), wird es zu `<div>`, sonst erhält es die ID
     * des Skip-Links (THEME_V4_MAIN_ID).
     */
    function theme_v4_render_module_view(string $html, string $module): string
    {
        $headerHasMain = defined('THEME_V4_HEADER_HAS_MAIN') && THEME_V4_HEADER_HAS_MAIN;
        $mainId = defined('THEME_V4_MAIN_ID') ? (string) THEME_V4_MAIN_ID : 'main';
        $module = preg_replace('/[^a-z0-9-]/', '', strtolower($module)) ?? 'module';

        if ($headerHasMain) {
            $html = (string) preg_replace('/<main\b/i', '<div', $html);
            $html = (string) preg_replace('#</main>#i', '</div>', $html);
        } elseif (stripos($html, '<main') !== false) {
            if (preg_match('/<main\b[^>]*\bid=/i', $html) !== 1) {
                $html = (string) preg_replace('/<main\b/i', '<main id="' . theme_v4_e($mainId) . '"', $html, 1);
            }
        } else {
            $html = '<main id="' . theme_v4_e($mainId) . '">' . $html . '</main>';
        }

        return '<div class="theme-v4-module theme-v4-module--' . $module . '">' . $html . '</div>';
    }
}

// ── Medien: Fokus-Punkt (4.1.0) ──────────────────────────────────────────────

if (!function_exists('theme_v4_focal_position')) {
    /**
     * `object-position` aus dem Fokus-Punkt eines Bildes (`focal_x`/`focal_y`, 0–1 oder 0–100).
     * Quelle: Felder am Inhalt (`featured_image_focal_x`, `focal_x`, `image_focal_x`) oder der Filter
     * `cms_media_focal_point` (Bild-URL → ['x' => …, 'y' => …]). Leer = zentriert (Standard).
     */
    function theme_v4_focal_position(mixed $item = null, string $imageUrl = ''): string
    {
        $read = static function (mixed $source, string $key): mixed {
            if (is_array($source)) {
                return $source[$key] ?? null;
            }

            return is_object($source) ? ($source->{$key} ?? null) : null;
        };
        $x = null;
        $y = null;
        foreach (['featured_image_focal_', 'image_focal_', 'focal_'] as $prefix) {
            $x = $read($item, $prefix . 'x');
            $y = $read($item, $prefix . 'y');
            if ($x !== null && $y !== null) {
                break;
            }
        }
        if (($x === null || $y === null) && $imageUrl !== '' && class_exists('\\CMS\\Hooks')) {
            try {
                $point = \CMS\Hooks::applyFilters('cms_media_focal_point', null, $imageUrl);
                if (is_array($point)) {
                    $x = $point['x'] ?? $point['focal_x'] ?? null;
                    $y = $point['y'] ?? $point['focal_y'] ?? null;
                }
            } catch (\Throwable) {
            }
        }
        if (!is_numeric($x) || !is_numeric($y)) {
            return '';
        }
        $toPercent = static function (float $value): float {
            $value = $value <= 1.0 ? $value * 100 : $value;

            return max(0.0, min(100.0, round($value, 2)));
        };

        return $toPercent((float) $x) . '% ' . $toPercent((float) $y) . '%';
    }
}

if (!function_exists('theme_v4_focal_style_attr')) {
    /** ` style="object-position:…"` oder leer. Der Core überführt style-Attribute in Nonce-Klassen (CSP). */
    function theme_v4_focal_style_attr(mixed $item = null, string $imageUrl = ''): string
    {
        $position = theme_v4_focal_position($item, $imageUrl);

        return $position !== '' ? ' style="object-position:' . theme_v4_e($position) . '"' : '';
    }
}

if (!function_exists('theme_v4_image_dimensions')) {
    /**
     * Breite/Höhe eines lokalen Bildes unter /uploads (gegen CLS). Ergebnis je Request gepuffert.
     *
     * @return array{0:int,1:int}|null
     */
    function theme_v4_image_dimensions(string $url): ?array
    {
        static $cache = [];
        if ($url === '' || array_key_exists($url, $cache)) {
            return $cache[$url] ?? null;
        }
        $cache[$url] = null;
        $local = theme_v4_local_asset_url($url);
        $path = (string) (parse_url($local, PHP_URL_PATH) ?? '');
        $sitePath = rtrim((string) (parse_url(theme_v4_site_url(), PHP_URL_PATH) ?? ''), '/');
        if ($sitePath !== '' && str_starts_with($path, $sitePath . '/')) {
            $path = substr($path, strlen($sitePath));
        }
        $path = rawurldecode($path);
        if (!str_starts_with($path, '/uploads/') || str_contains($path, '..')) {
            return null;
        }
        $base = defined('UPLOAD_PATH') ? rtrim((string) UPLOAD_PATH, '/\\') : rtrim((string) ABSPATH, '/\\') . '/uploads';
        $file = $base . substr($path, strlen('/uploads'));
        try {
            if (class_exists('\\CMS\\Services\\ImageService') && method_exists('\\CMS\\Services\\ImageService', 'getInstance')) {
                $info = \CMS\Services\ImageService::getInstance()->getDimensions($file);
                if (is_array($info) && (int) ($info['width'] ?? 0) > 0 && (int) ($info['height'] ?? 0) > 0) {
                    return $cache[$url] = [(int) $info['width'], (int) $info['height']];
                }
            }
        } catch (\Throwable) {
        }

        return $cache[$url];
    }
}

if (!function_exists('theme_v4_img')) {
    /**
     * `<img>` mit width/height (CLS), `loading="lazy"` bzw. für das LCP-Bild `fetchpriority="high"`
     * und Fokus-Punkt (`object-position`).
     *
     * @param array{class?:string,lcp?:bool,item?:mixed,width?:int,height?:int,sizes?:string} $options
     */
    function theme_v4_img(string $src, string $alt, array $options = []): string
    {
        if ($src === '') {
            return '';
        }
        $lcp = !empty($options['lcp']);
        $dims = (isset($options['width'], $options['height']) && (int) $options['width'] > 0 && (int) $options['height'] > 0)
            ? [(int) $options['width'], (int) $options['height']]
            : theme_v4_image_dimensions($src);
        $class = trim((string) ($options['class'] ?? ''));
        $focal = theme_v4_focal_position($options['item'] ?? null, $src);
        if ($focal !== '') {
            $class = trim($class . ' theme-v4-focal');
        }

        return '<img src="' . theme_v4_e($src) . '" alt="' . theme_v4_e($alt) . '"'
            . ($class !== '' ? ' class="' . theme_v4_e($class) . '"' : '')
            . ($dims !== null ? ' width="' . $dims[0] . '" height="' . $dims[1] . '"' : '')
            . (!empty($options['sizes']) ? ' sizes="' . theme_v4_e((string) $options['sizes']) . '"' : '')
            . ($lcp ? ' fetchpriority="high" decoding="async"' : ' loading="lazy" decoding="async"')
            . ($focal !== '' ? ' style="object-position:' . theme_v4_e($focal) . '"' : '')
            . '>';
    }
}

// ── 365CMS 4.2.0: Module, Community, Rechte, Slugs ───────────────────────────

if (!function_exists('theme_v4_module_enabled')) {
    /**
     * Zustand eines Core-Moduls (Admin → Module) mit Request-Cache.
     *
     * Achtung: `CoreModuleService::isModuleEnabled()` meldet unbekannte Module als aktiv. Kennt der Core das Modul
     * nicht (z. B. `community` vor 4.2.0) oder fehlt der Dienst, gilt `$default`.
     */
    function theme_v4_module_enabled(string $slug, bool $default = false): bool
    {
        static $cache = [];
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return $default;
        }
        if (array_key_exists($slug, $cache)) {
            return $cache[$slug];
        }
        try {
            if (class_exists('\\CMS\\Services\\CoreModuleService')) {
                $service = \CMS\Services\CoreModuleService::getInstance();
                if (method_exists($service, 'getModuleDefinition') && $service->getModuleDefinition($slug) === null) {
                    return $cache[$slug] = $default;
                }

                return $cache[$slug] = (bool) $service->isModuleEnabled($slug);
            }
        } catch (\Throwable) {
        }

        return $cache[$slug] = $default;
    }
}

if (!function_exists('theme_v4_community_base_path')) {
    /** Basispfad der Community ohne Schrägstriche (Einstellung `base_path`, Standard `forum`). */
    function theme_v4_community_base_path(): string
    {
        static $base = null;
        if ($base !== null) {
            return $base;
        }
        $base = 'forum';
        try {
            if (class_exists('\\CMS\\Services\\Community\\Support\\Settings')) {
                $value = trim((string) \CMS\Services\Community\Support\Settings::getInstance()->string('base_path'), '/');
                if (preg_match('#^[a-z0-9][a-z0-9-]{0,63}$#', $value) === 1) {
                    $base = $value;
                }
            }
        } catch (\Throwable) {
        }

        return $base;
    }
}

if (!function_exists('theme_v4_community_available')) {
    /**
     * true, wenn die Community öffentlich erreichbar ist: Modul `community` aktiv (fail-closed wie der Core)
     * und die Core-Vorlagen vorhanden (CMS/views/community/index.php). Vor 4.2.0 und während der Entwicklung
     * ohne Oberfläche immer false – Themes verlinken dann kein Forum.
     */
    function theme_v4_community_available(): bool
    {
        static $available = null;
        if ($available !== null) {
            return $available;
        }
        $available = false;
        try {
            $enabled = class_exists('\\CMS\\Services\\Community\\Support\\CommunityModule')
                ? \CMS\Services\Community\Support\CommunityModule::isEnabled()
                : theme_v4_module_enabled('community');
            $available = $enabled && is_file(rtrim((string) ABSPATH, '/\\') . '/views/community/index.php');
            if (class_exists('\\CMS\\Hooks')) {
                // Nur verschärfen (wie cms_community_can): ein Filter kann den Link ausblenden, nicht erzwingen.
                $available = $available && (bool) \CMS\Hooks::applyFilters('theme_v4_community_available', true);
            }
        } catch (\Throwable) {
            $available = false;
        }

        return $available;
    }
}

if (!function_exists('theme_v4_community_url')) {
    /** URL der Community-Startseite (mit Sprachpräfix) oder leer, wenn die Community nicht erreichbar ist. */
    function theme_v4_community_url(mixed $contentLocale = null): string
    {
        if (!theme_v4_community_available()) {
            return '';
        }
        $locale = theme_v4_locale($contentLocale);
        try {
            if (class_exists('\\CMS\\Services\\Community\\Support\\Urls')) {
                $path = (string) \CMS\Services\Community\Support\Urls::index($locale);
                if (str_starts_with($path, '/') && !str_starts_with($path, '//')) {
                    return theme_v4_site_url($path);
                }
            }
        } catch (\Throwable) {
        }

        return theme_v4_site_url(($locale === 'en' ? 'en/' : '') . theme_v4_community_base_path());
    }
}

if (!function_exists('theme_v4_community_link')) {
    /**
     * Navigationseintrag „Community“ für Footer/Fallback-Menüs oder null.
     *
     * @return array{label:string,url:string}|null
     */
    function theme_v4_community_link(mixed $contentLocale = null, string $labelDe = 'Community', string $labelEn = 'Community'): ?array
    {
        $url = theme_v4_community_url($contentLocale);

        return $url !== '' ? ['label' => theme_v4_t($labelDe, $labelEn, $contentLocale), 'url' => $url] : null;
    }
}

if (!function_exists('theme_v4_user_can')) {
    /**
     * Capability des angemeldeten Kontos (z. B. `community.moderate`). Admins dürfen alles; ohne Capability-API
     * (vor 4.0) nur Admins. Für Admin-Links stattdessen `Auth::isAdmin()`: Die Rolle `moderator` (4.2.0) hat
     * Community-Rechte, aber keinen Zugang zu /admin.
     */
    function theme_v4_user_can(string $capability): bool
    {
        $capability = strtolower(trim($capability));
        if ($capability === '') {
            return false;
        }
        try {
            if (!class_exists('\\CMS\\Auth') || !\CMS\Auth::isLoggedIn()) {
                return false;
            }
            $auth = \CMS\Auth::instance();
            if (method_exists($auth, 'hasCapability')) {
                return (bool) $auth->hasCapability($capability);
            }

            return \CMS\Auth::isAdmin();
        } catch (\Throwable) {
            return false;
        }
    }
}

if (!function_exists('theme_v4_slug')) {
    /**
     * Slug wie der Core 4.2.0 (`CMS\Slug::fromText()`: ä → ae, ö → oe, ü → ue, ß → ss, Akzente entfernt),
     * mit gleichwertigem Rückfall für 4.0/4.1. Für Anker-IDs, CSS-Klassen aus Namen und Filter-Parameter.
     */
    function theme_v4_slug(string $text, int $maxLength = 100): string
    {
        try {
            if (class_exists('\\CMS\\Slug') && method_exists('\\CMS\\Slug', 'fromText')) {
                return (string) \CMS\Slug::fromText($text, max(0, $maxLength));
            }
        } catch (\Throwable) {
        }
        $text = trim($text);
        $text = strtr($text, ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', 'Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue', 'ẞ' => 'ss']);
        $text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
        if (class_exists('\\Normalizer')) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_KD);
            if (is_string($normalized)) {
                $text = (string) (preg_replace('/\p{Mn}+/u', '', $normalized) ?? $normalized);
            }
        }
        $slug = (string) (preg_replace('/[^a-z0-9\-]+/', '-', $text) ?? '');
        $slug = trim((string) (preg_replace('/-+/', '-', $slug) ?? ''), '-');
        if ($maxLength > 0 && strlen($slug) > $maxLength) {
            $slug = trim(substr($slug, 0, $maxLength), '-');
        }

        return $slug;
    }
}

// ── Memberbereich (/member) ──────────────────────────────────────────────────

if (!function_exists('theme_v4_is_member_area')) {
    /** true im Core-Mitgliederbereich (eigene Tabler-Shell; dort kein Frontend-Stylesheet laden). */
    function theme_v4_is_member_area(): bool
    {
        $path = theme_v4_request_path();
        if (str_starts_with($path, '/en/')) {
            $path = substr($path, 3);
        }

        return $path === '/member' || str_starts_with($path, '/member/');
    }
}

if (!function_exists('theme_v4_member_tokens_style')) {
    /**
     * Nonce-`<style>` für den Hook `member_head`: setzt die `--member-*`-Variablen des Core-Mitgliederbereichs
     * (inkl. Newsletter-Widget) auf die Palette des Themes.
     *
     * `focus` ist eine Farbe; daraus entsteht der box-shadow-Wert von `--member-focus-ring`.
     * `$dark` wird bewusst nicht angewendet: Die Tabler-Shell des Mitgliederbereichs hat keinen Dunkelmodus
     * (ihre light-dark()-Farben bleiben ohne color-scheme: dark hell, z. B. .text-secondary ≈ 3,6:1 auf
     * dunklen Karten). Der Parameter bleibt für spätere Core-Versionen mit Dunkelmodus erhalten.
     *
     * @param array<string, string> $light Schlüssel: primary, accent, bg, card_bg, text, text_muted, border, radius, font
     * @param array<string, string> $dark
     */
    function theme_v4_member_tokens_style(array $light, array $dark = []): string
    {
        $map = [
            'primary' => '--member-primary', 'accent' => '--member-accent', 'bg' => '--member-bg',
            'card_bg' => '--member-card-bg', 'text' => '--member-text', 'text_muted' => '--member-text-muted',
            'border' => '--member-border', 'radius' => '--member-radius',
        ];
        $clean = static function (string $value): string {
            $value = trim($value);

            return preg_match('/^(#[0-9a-f]{3,8}|(?:rgb|rgba|hsl|hsla)\([0-9.,%\s\/]+\)|[0-9.]+(?:px|rem|em|%)|0)$/i', $value) === 1 ? $value : '';
        };
        $render = static function (array $tokens) use ($map, $clean): string {
            $css = '';
            foreach ($map as $key => $property) {
                $value = $clean((string) ($tokens[$key] ?? ''));
                if ($value !== '') {
                    $css .= $property . ':' . $value . ';';
                }
            }
            if (isset($tokens['primary']) && $clean((string) $tokens['primary']) !== '') {
                $css .= '--tblr-primary:' . $clean((string) $tokens['primary']) . ';';
                $css .= '--member-primary-soft:color-mix(in srgb,' . $clean((string) $tokens['primary']) . ' 12%,transparent);';
            }
            $focus = $clean((string) ($tokens['focus'] ?? ''));
            if ($focus !== '' && preg_match('/^(#|rgb|hsl)/i', $focus) === 1) {
                $css .= '--member-focus-ring:0 0 0 3px color-mix(in srgb,' . $focus . ' 45%,transparent);';
            }
            if (isset($tokens['font']) && preg_match('/^[a-z0-9 ,"\'-]+$/i', (string) $tokens['font']) === 1) {
                $css .= '--tblr-font-sans-serif:' . str_replace(['<', '>'], '', (string) $tokens['font']) . ';';
            }

            return $css;
        };

        // Im Admin bewusst gesetzte Member-Farben (Mitgliederbereich → Design) haben Vorrang:
        // Das Theme ersetzt nur Werte, die noch auf dem Core-Standard stehen.
        try {
            if (class_exists('\\CMS\\MemberArea\\MemberController')) {
                $design = \CMS\MemberArea\MemberController::instance()->getSettings()['design'] ?? [];
                $coreDefaults = ['primary' => '#6366f1', 'accent' => '#8b5cf6', 'bg' => '#f1f5f9', 'card_bg' => '#ffffff', 'text' => '#1e293b', 'border' => '#e2e8f0'];
                foreach ($coreDefaults as $key => $coreDefault) {
                    $stored = strtolower(trim((string) (is_array($design) ? ($design[$key] ?? '') : '')));
                    if ($stored !== '' && $stored !== $coreDefault) {
                        unset($light[$key], $dark[$key]);
                    }
                }
            }
        } catch (\Throwable) {
        }

        $nonce = '';
        try {
            $nonce = (string) \CMS\Security::instance()->nonceAttr();
        } catch (\Throwable) {
        }
        unset($dark); // siehe Docblock: kein Dunkelmodus in der Member-Shell
        $css = 'body.member-shell{' . $render($light) . '}';

        return '<style id="theme-v4-member-tokens"' . ($nonce !== '' ? ' ' . $nonce : '') . '>' . $css . '</style>' . "\n";
    }
}
