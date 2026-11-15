<?php
/**
 * CMS Newspaper Theme – Header
 *
 * Erhält die Render-Daten des Templates ($post, $page, $title, $isPreview, $contentLocale …).
 * Öffnet <main id="main-content"> (THEME_V4_HEADER_HAS_MAIN); footer.php schließt es.
 *
 * @package CmsNewspaper_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Header in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_HEADER_RENDERED')) {
    return;
}
define('CMS_THEME_HEADER_RENDERED', true);

// Vorschau-Links ($isPreview) und nicht veröffentlichte Inhalte: noindex, keine strukturierten Daten, Hinweisleiste.
$newsIsPreview = theme_v4_is_preview(get_defined_vars());
$newsLocale    = theme_v4_locale($contentLocale ?? null);

$siteTitle = news_site_title();
$siteDesc  = '';
try {
    $siteDesc = (string) \CMS\ThemeManager::instance()->getSiteDescription();
} catch (\Throwable) {
    $siteDesc = '';
}

$bodyCls = news_body_class($newsIsPreview ? 'is-preview' : '');

$showUtilityBar = filter_var(news_get_setting('header', 'show_utility_bar', true),  FILTER_VALIDATE_BOOLEAN);
$showTopicsBar  = filter_var(news_get_setting('header', 'show_topics_bar',  true),  FILTER_VALIDATE_BOOLEAN);
$showSearch     = filter_var(news_get_setting('header', 'show_search',      true),  FILTER_VALIDATE_BOOLEAN);

$utilityLabel  = (string) news_get_setting('header', 'utility_status_label', 'SERVER_NODE: DE_WEST // STATUS:');
$utilityState  = (string) news_get_setting('header', 'utility_status_state', 'OPTIMAL');
// Logo: Theme-Einstellung (nur lokale URLs), sonst Core-Option site_logo.
$logoUrl       = theme_v4_site_logo((string) news_get_setting('header', 'logo_url', ''));
$logoSize      = $logoUrl !== '' ? theme_v4_image_dimensions($logoUrl) : null;
$brandRaw      = (string) CmsNewspaper_Theme::instance()->getConfig('masthead_brand', 'PHIN<span>IT</span>.DE');
$socialLinks   = news_social_links();
$socialIcons   = [
    'linkedin' => '<path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>',
    'github'   => '<path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>',
    'x'        => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>',
];

$safe      = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$homeHref  = $safe(news_href('/'));
$searchUrl = $safe(theme_route_url('search'));
?>
<!DOCTYPE html>
<html lang="<?php echo $safe($newsLocale); ?>">
<head>
    <meta charset="UTF-8">
    <?= function_exists('cms_csp_runtime_tags') ? cms_csp_runtime_tags(true) : '' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $safe(news_document_title($title ?? null)); ?></title>
    <?php // Core-SEO (Beschreibung, Robots, Canonical, Open Graph, JSON-LD); Vorschau: noindex, ohne JSON-LD. ?>
    <?php echo theme_v4_head_tags($newsIsPreview, $siteDesc); ?>
    <?php echo theme_v4_favicon_tag(); ?>
    <?php \CMS\Hooks::doAction('head'); ?>
</head>

<body class="<?php echo $bodyCls; ?>">
<a class="news-skip-link" href="#main-content"><?php echo $safe(theme_v4_t('Zum Inhalt springen', 'Skip to content', $newsLocale)); ?></a>
<?php if ($newsIsPreview) : ?>
<p class="theme-v4-preview-banner" role="status"><?php echo $safe(theme_v4_t('Vorschau – dieser Inhalt ist nicht veröffentlicht.', 'Preview – this content is not published.', $newsLocale)); ?></p>
<?php endif; ?>

<header class="news-header" id="news-masthead">

    <?php if ($showUtilityBar) : ?>
        <div class="news-bar news-bar-utility">
            <div class="news-bar-inner">
                <span class="news-status">
                    <?php echo $safe($utilityLabel); ?>
                    <span class="news-status-state"><?php echo $safe($utilityState); ?></span>
                </span>
                <?php if ($socialLinks !== []) : ?>
                    <ul class="news-social" aria-label="<?php echo $safe(theme_v4_t('Soziale Profile', 'Social profiles', $newsLocale)); ?>">
                        <?php foreach ($socialLinks as $social) : ?>
                            <li><a href="<?php echo $safe($social['url']); ?>" aria-label="<?php echo $safe($social['label']); ?>" target="_blank" rel="noopener noreferrer me">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?php echo $socialIcons[$social['icon']] ?? ''; // statische SVG-Pfade ?></svg>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="news-bar news-bar-masthead">
        <div class="news-bar-inner">

            <a href="<?php echo $homeHref; ?>" class="news-wordmark news-focus-shadow" rel="home" aria-label="<?php echo $safe($siteTitle); ?> – <?php echo $safe(theme_v4_t('Startseite', 'Home', $newsLocale)); ?>">
                <?php if ($logoUrl !== '') : ?>
                    <img src="<?php echo $safe($logoUrl); ?>" alt="<?php echo $safe($siteTitle); ?>"<?php echo $logoSize !== null ? ' width="' . (int) $logoSize[0] . '" height="' . (int) $logoSize[1] . '"' : ''; ?>>
                <?php else : ?>
                    <?php echo news_brand_html($brandRaw); ?>
                <?php endif; ?>
            </a>

            <nav class="news-nav" id="news-primary-nav" aria-label="<?php echo $safe(theme_v4_t('Hauptnavigation', 'Main navigation', $newsLocale)); ?>">
                <?php // Desktop: Core-Hook main_nav (Downloads, Knowledgebase, Newsletter) direkt nach den Menüeinträgen. ?>
                <?php theme_nav_menu('primary-nav', 'desktop'); ?>
            </nav>

            <div class="news-header-actions">

                <?php if ($showSearch) : ?>
                    <button type="button"
                            id="newsSearchToggle"
                            class="news-icon-btn news-focus-shadow"
                            aria-label="<?php echo $safe(theme_v4_t('Suche öffnen', 'Open search', $newsLocale)); ?>"
                            aria-expanded="false"
                            aria-controls="newsSearchPanel">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                <?php endif; ?>

                <button type="button"
                        id="newsMobileToggle"
                        class="news-mobile-toggle news-focus-shadow"
                        aria-label="<?php echo $safe(theme_v4_t('Menü öffnen', 'Open menu', $newsLocale)); ?>"
                        aria-expanded="false"
                        aria-controls="newsMobileDrawer">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </button>

            </div>
        </div>
    </div>

    <?php if ($showSearch) : ?>
        <div id="newsSearchPanel" class="news-search-panel" hidden>
            <form role="search" method="get" action="<?php echo $searchUrl; ?>" class="news-search-form">
                <label for="news-search" class="news-visually-hidden"><?php echo $safe(theme_v4_t('Suche', 'Search', $newsLocale)); ?></label>
                <input id="news-search"
                       type="search"
                       name="q"
                       placeholder="<?php echo $safe(theme_v4_t('Artikel, Themen, Tags suchen …', 'Search articles, topics, tags …', $newsLocale)); ?>"
                       autocomplete="off"
                       class="news-search-input news-focus-shadow">
                <button type="submit" class="news-btn news-btn-primary"><?php echo $safe(theme_v4_t('Suchen', 'Search', $newsLocale)); ?></button>
                <button type="button" id="newsSearchClose" class="news-btn news-btn-ghost"><?php echo $safe(theme_v4_t('Suche schließen', 'Close search', $newsLocale)); ?></button>
            </form>
        </div>
    <?php endif; ?>

    <?php
    ob_start();
    $newsHasTopics = $showTopicsBar && theme_nav_menu('topics-nav');
    $newsTopicsNav = (string) ob_get_clean();
    ?>
    <?php if ($newsHasTopics) : ?>
        <div class="news-bar news-bar-topics">
            <div class="news-bar-inner">
                <nav class="news-topics-nav" aria-label="<?php echo $safe(theme_v4_t('Themen-Schnellnavigation', 'Topics', $newsLocale)); ?>">
                    <?php echo $newsTopicsNav; // theme_nav_menu(): escapte Menüliste ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>

</header>

<!-- Mobile Overlay & Drawer -->
<div class="news-mobile-overlay" id="newsMobileOverlay" aria-hidden="true"></div>
<nav id="newsMobileDrawer"
     class="news-mobile-drawer"
     aria-label="<?php echo $safe(theme_v4_t('Mobile Navigation', 'Mobile navigation', $newsLocale)); ?>"
     hidden>
    <?php // Mobil: eigene Navigation, daher Core-Hook main_nav im Kontext `mobile`. ?>
    <?php theme_nav_menu('primary-nav', 'mobile'); ?>
</nav>

<main id="main-content" class="news-content" tabindex="-1">
<?php
$flash = news_get_flash();
if ($flash !== null) :
    $type = (string) ($flash['type'] ?? 'info');
    $msg  = (string) ($flash['message'] ?? '');
    $cls  = in_array($type, ['success', 'error', 'info'], true) ? 'is-' . $type : 'is-info';
    ?>
    <div class="news-container">
        <div class="news-flash <?php echo $cls; ?>" role="status">
            <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    </div>
<?php endif; ?>
