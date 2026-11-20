<?php
declare(strict_types=1);

/**
 * Feder – Header
 *
 * Der Core rendert header.php über ThemeManager::render() und übergibt die
 * Template-Daten ($post, $page, $posts, $category, $tag, $author, $results …).
 *
 * @package Feder_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Header in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_HEADER_RENDERED')) {
    return;
}
define('CMS_THEME_HEADER_RENDERED', true);

// Inhalte für das Core-SEO-Modul bereitstellen (Description, Canonical, Open Graph).
if (isset($post) && (is_object($post) || is_array($post))) {
    $GLOBALS['post'] = $post;
} elseif (isset($page) && is_array($page)) {
    $GLOBALS['page'] = $page;
}

$context = get_defined_vars();
$siteTitle = feder_site_title();
$tagline = feder_site_description();
$documentTitle = feder_page_title($context);
$locale = theme_v4_locale($contentLocale ?? null);
$scheme = feder_color_scheme();
// Vorschau-Link oder nicht veröffentlichter Inhalt: noindex, keine strukturierten Daten, Hinweisleiste.
$isPreview = theme_v4_is_preview($context);

// Theme-Logo, sonst Core-Option site_logo (nur lokale URLs).
$logoUrl = theme_v4_site_logo(feder_media_url(feder_text('header', 'logo_url')));
$showTagline = feder_flag('header', 'show_tagline', true) && $tagline !== '';
$showSearch = feder_flag('header', 'show_search', true);
$showToggle = feder_flag('header', 'show_darkmode_toggle', true);
$isArticle = isset($post) && is_object($post);
$showProgress = $isArticle && feder_flag('feder_article', 'show_progress_bar', true);

$bodyClasses = feder_body_class(
    $isArticle ? 'is-article' : '',
    isset($page) && is_array($page) ? 'is-page' : '',
    feder_flag('header', 'enable_sticky_header', false) ? 'has-sticky-header' : '',
    $isPreview ? 'is-preview' : ''
);
?>
<!DOCTYPE html>
<html lang="<?php echo feder_e($locale); ?>"<?php echo $scheme !== 'auto' ? ' data-theme="' . feder_e($scheme) . '"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <?= function_exists('cms_csp_runtime_tags') ? cms_csp_runtime_tags(true) : '' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo feder_e($documentTitle); ?></title>
    <?php // Core-SEO (Beschreibung, Robots, Canonical, hreflang, Open Graph, JSON-LD); Vorschau: noindex, ohne JSON-LD. ?>
    <?php echo theme_v4_head_tags($isPreview, $tagline); ?>
    <?php echo theme_v4_favicon_tag(); ?>
    <link rel="alternate" type="application/rss+xml" title="<?php echo feder_e($siteTitle); ?> – RSS" href="<?php echo feder_e(feder_feed_url()); ?>">
    <?php \CMS\Hooks::doAction('head'); ?>
</head>
<body class="<?php echo feder_e($bodyClasses); ?>">
<?php \CMS\Hooks::doAction('body_start'); ?>
<a class="fdr-skip-link" href="#main-content"><?php echo feder_e(theme_v4_t('Zum Inhalt springen', 'Skip to content', $locale)); ?></a>
<?php if ($isPreview) : ?>
<p class="theme-v4-preview-banner" role="status"><?php echo feder_e(theme_v4_t('Vorschau – dieser Inhalt ist nicht veröffentlicht.', 'Preview – this content is not published.', $locale)); ?></p>
<?php endif; ?>

<header class="fdr-header" id="fdr-header">
    <div class="fdr-header__inner">
        <a class="fdr-brand" href="<?php echo feder_e(feder_url('/')); ?>" rel="home">
            <?php if ($logoUrl !== '') : ?>
                <img class="fdr-brand__logo" src="<?php echo feder_e($logoUrl); ?>" alt="<?php echo feder_e($siteTitle); ?>" height="44">
            <?php else : ?>
                <span class="fdr-brand__mark" aria-hidden="true"><?php echo feder_e(feder_initials($siteTitle)); ?></span>
                <span class="fdr-brand__text">
                    <span class="fdr-brand__name"><?php echo feder_e($siteTitle); ?></span>
                    <?php if ($showTagline) : ?>
                        <span class="fdr-brand__tagline"><?php echo feder_e($tagline); ?></span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </a>

        <nav class="fdr-nav" aria-label="<?php echo feder_e(feder_t('Hauptnavigation', 'Main navigation')); ?>">
            <?php feder_nav_menu('primary', 'fdr-nav__list', feder_default_menu('primary'), 'desktop'); ?>
        </nav>

        <div class="fdr-header__actions">
            <?php if ($showSearch) : ?>
                <button type="button" class="fdr-icon-button" data-fdr-toggle="search" aria-controls="fdr-search-panel" aria-expanded="false">
                    <?php echo feder_icon('search'); ?><span class="fdr-visually-hidden"><?php echo feder_e(feder_t('Suche öffnen', 'Open search')); ?></span>
                </button>
            <?php endif; ?>
            <?php if ($showToggle) : ?>
                <button type="button" class="fdr-icon-button fdr-scheme-toggle" data-fdr-scheme-toggle aria-pressed="false">
                    <span class="fdr-scheme-toggle__sun"><?php echo feder_icon('sun'); ?></span>
                    <span class="fdr-scheme-toggle__moon"><?php echo feder_icon('moon'); ?></span>
                    <span class="fdr-visually-hidden"><?php echo feder_e(feder_t('Dunkles Farbschema', 'Dark color scheme')); ?></span>
                </button>
            <?php endif; ?>
            <button type="button" class="fdr-icon-button fdr-menu-toggle" data-fdr-toggle="menu" aria-controls="fdr-menu-panel" aria-expanded="false">
                <span class="fdr-menu-toggle__open"><?php echo feder_icon('menu'); ?></span>
                <span class="fdr-menu-toggle__close"><?php echo feder_icon('close'); ?></span>
                <span class="fdr-visually-hidden"><?php echo feder_e(feder_t('Menü', 'Menu')); ?></span>
            </button>
        </div>
    </div>

    <?php if ($showSearch) : ?>
        <div class="fdr-panel fdr-panel--search" id="fdr-search-panel" hidden>
            <div class="fdr-panel__inner">
                <?php feder_search_form('fdr-header-search'); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="fdr-panel fdr-panel--menu" id="fdr-menu-panel" hidden>
        <nav class="fdr-panel__inner" aria-label="<?php echo feder_e(feder_t('Mobile Navigation', 'Mobile navigation')); ?>">
            <?php feder_nav_menu('primary', 'fdr-mobile-nav', feder_default_menu('primary'), 'mobile'); ?>
        </nav>
    </div>

    <?php if ($showProgress) : ?>
        <div class="fdr-progress" aria-hidden="true"><span class="fdr-progress__bar" data-fdr-progress></span></div>
    <?php endif; ?>
</header>

<main id="main-content" class="fdr-main" tabindex="-1">
