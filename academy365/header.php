<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Header in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_HEADER_RENDERED')) {
    return;
}
define('CMS_THEME_HEADER_RENDERED', true);
$acLocale    = theme_v4_locale($contentLocale ?? null);
$acIsPreview = theme_v4_is_preview(get_defined_vars());
?><!DOCTYPE html>
<html lang="<?php echo theme_v4_e($acLocale); ?>">
<head>
    <meta charset="UTF-8">
    <?= function_exists('cms_csp_runtime_tags') ? cms_csp_runtime_tags(true) : '' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo theme_v4_e(theme_v4_document_title($title ?? null)); ?></title>
    <?php echo theme_v4_head_tags($acIsPreview, \CMS\ThemeManager::instance()->getSiteDescription()); ?>
    <?php echo theme_v4_favicon_tag(); ?>
    <?php \CMS\Hooks::doAction('head'); ?>
</head>
<body class="ac-body <?php echo theme_is_logged_in() ? 'is-logged-in' : ''; ?><?php echo $acIsPreview ? ' is-preview' : ''; ?>">
<div id="page" class="ac-page-wrapper">
<a class="ac-skip-link" href="#main"><?php echo theme_v4_e(theme_v4_t('Zum Inhalt springen', 'Skip to content', $acLocale)); ?></a>
<?php if ($acIsPreview) : ?>
<p class="theme-v4-preview-banner" role="status"><?php echo theme_v4_e(theme_v4_t('Vorschau – dieser Inhalt ist nicht veröffentlicht.', 'Preview – this content is not published.', $acLocale)); ?></p>
<?php endif; ?>
<?php
try {
    $c = \CMS\Services\ThemeCustomizer::instance();
    $logoUrl    = $c->get('header', 'logo_url', '');
    $showSearch = filter_var($c->get('header', 'show_search_btn', true), FILTER_VALIDATE_BOOLEAN);
} catch (\Throwable $e) { $logoUrl = ''; $showSearch = true; }
$themeManager = \CMS\ThemeManager::instance();
$siteTitle    = $themeManager->getSiteTitle();
$isLoggedIn   = theme_is_logged_in();
$siteUrl      = SITE_URL;
$safe         = fn(string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$homeUrl      = academy365_safe_url(function_exists('theme_route_url') ? theme_route_url('home') : rtrim($siteUrl, '/') . '/', rtrim($siteUrl, '/') . '/');
$searchUrl    = academy365_safe_url(function_exists('theme_route_url') ? theme_route_url('search') : rtrim($siteUrl, '/') . '/search', rtrim($siteUrl, '/') . '/search');
$loginUrl     = academy365_safe_url(function_exists('theme_route_url') ? theme_route_url('login') : rtrim($siteUrl, '/') . '/login', rtrim($siteUrl, '/') . '/login');
$registerUrl  = academy365_safe_url(function_exists('theme_route_url') ? theme_route_url('register') : rtrim($siteUrl, '/') . '/register', rtrim($siteUrl, '/') . '/register');
$memberUrl    = academy365_safe_url(function_exists('theme_route_url') ? theme_route_url('member-dashboard', ['area' => 'member']) : rtrim($siteUrl, '/') . '/member', rtrim($siteUrl, '/') . '/member');
$logoSafeUrl  = theme_v4_site_logo(academy365_safe_url((string) $logoUrl));
?>
<header id="masthead" class="ac-site-header">
    <div class="ac-header-inner">
        <div class="ac-branding">
            <a href="<?php echo $safe($homeUrl); ?>" rel="home">
                <?php if ($logoSafeUrl !== '') : ?>
                    <img src="<?php echo $safe($logoSafeUrl); ?>" alt="<?php echo $safe($siteTitle); ?>" width="150" height="44">
                <?php else : ?>
                    <span class="ac-logo-text"><span aria-hidden="true">🎓</span><?php echo $safe($siteTitle); ?></span>
                <?php endif; ?>
            </a>
        </div>
        <nav id="site-navigation" class="main-navigation" aria-label="<?php echo theme_v4_e(theme_v4_t('Hauptnavigation', 'Main navigation', $acLocale)); ?>">
            <?php // Eine responsive Navigation für Desktop und Mobil: main_nav einmal (Kontext desktop), sonst doppelte Einträge. ?>
            <?php theme_nav_menu('primary-nav', 'desktop'); ?>
        </nav>
        <div class="ac-header-actions">
            <?php if ($showSearch) : ?>
                <button id="searchToggle" type="button" class="ac-btn ac-btn-ghost ac-search-toggle-btn" aria-label="Suche öffnen" aria-expanded="false" aria-controls="searchPanel">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </button>
            <?php endif; ?>
            <?php if ($isLoggedIn) : ?>
                <a href="<?php echo $safe($memberUrl); ?>" class="ac-btn ac-btn-primary">Meine Kurse</a>
            <?php else : ?>
                <a href="<?php echo $safe($loginUrl); ?>"    class="ac-btn ac-btn-ghost">Anmelden</a>
                <a href="<?php echo $safe($registerUrl); ?>" class="ac-btn ac-btn-primary">Kostenlos lernen</a>
            <?php endif; ?>
            <button id="mobileMenuToggle" class="ac-mobile-toggle" type="button" aria-label="Menü öffnen" aria-expanded="false" aria-controls="site-navigation">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <?php if ($showSearch) : ?>
    <div id="searchPanel" class="ac-search-panel" hidden>
        <div class="ac-header-inner">
            <form role="search" method="get" action="<?php echo $safe($searchUrl); ?>" class="ac-search-form">
                <label for="ac-search" class="ac-visually-hidden">Kurse suchen</label>
                <input id="ac-search" type="search" name="q" placeholder="Kurs, Thema oder Lehrer suchen …" autocomplete="off" class="ac-search-input">
                <button type="submit" class="ac-btn ac-btn-primary" aria-label="Suchen">Suchen</button>
            </form>
            <button id="searchClose" type="button" class="ac-btn ac-btn-ghost" aria-label="Suche schließen"><span aria-hidden="true">✕</span></button>
        </div>
    </div>
    <?php endif; ?>
</header><!-- #masthead -->
<div id="content" class="ac-site-content">
