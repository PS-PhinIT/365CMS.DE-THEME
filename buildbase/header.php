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

try {
    $c = \CMS\Services\ThemeCustomizer::instance();
    $showEmBanner   = filter_var($c->get('header', 'show_emergency_banner', false), FILTER_VALIDATE_BOOLEAN);
    $emergencyPhone = (string) $c->get('header', 'emergency_phone', '');
    $emergencyLabel = (string) $c->get('header', 'emergency_label', '24h Notfall-Service');
    $logoUrl        = (string) $c->get('header', 'logo_url', '');
    $showSearch     = filter_var($c->get('header', 'show_search_btn', true), FILTER_VALIDATE_BOOLEAN);
} catch (\Throwable $e) {
    $showEmBanner   = false;
    $emergencyPhone = '';
    $emergencyLabel = '';
    $logoUrl        = '';
    $showSearch     = true;
}

$themeManager = \CMS\ThemeManager::instance();
$siteTitle    = (string) $themeManager->getSiteTitle();
$siteDesc     = (string) $themeManager->getSiteDescription();
$isLoggedIn   = theme_is_logged_in();
$siteUrl      = (string) SITE_URL;
$bbLocale     = theme_v4_locale($contentLocale ?? null);
$bbIsPreview  = theme_v4_is_preview(get_defined_vars());

$safe        = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$siteBase    = rtrim(buildbase_safe_url($siteUrl, '/'), '/');
$siteBase    = $siteBase !== '' ? $siteBase : '/';
$homeUrl     = buildbase_safe_url(function_exists('theme_route_url') ? theme_route_url('home') : $siteBase . '/', $siteBase . '/');
$searchUrl   = buildbase_safe_url(function_exists('theme_route_url') ? theme_route_url('search') : $siteBase . '/search', $siteBase . '/search');
$loginUrl    = buildbase_safe_url(function_exists('theme_route_url') ? theme_route_url('login') : $siteBase . '/login', $siteBase . '/login');
$registerUrl = buildbase_safe_url(function_exists('theme_route_url') ? theme_route_url('register') : $siteBase . '/register', $siteBase . '/register');
$memberUrl   = buildbase_safe_url($siteBase . '/member', $siteBase . '/');
$logoSafeUrl = theme_v4_site_logo(buildbase_safe_url($logoUrl));
$logoDims    = $logoSafeUrl !== '' ? theme_v4_image_dimensions($logoSafeUrl) : null;

$telDigits = preg_replace('/[^0-9+]/', '', $emergencyPhone) ?? '';
$hasEmBanner = $showEmBanner && $emergencyPhone !== '';

$bodyClasses = ['bb-body'];
if ($isLoggedIn)   { $bodyClasses[] = 'is-logged-in'; }
if ($hasEmBanner)  { $bodyClasses[] = 'has-emergency-banner'; }
if ($bbIsPreview)  { $bodyClasses[] = 'is-preview'; }
?>
<!DOCTYPE html>
<html lang="<?php echo $safe($bbLocale); ?>">
<head>
    <meta charset="UTF-8">
    <?= function_exists('cms_csp_runtime_tags') ? cms_csp_runtime_tags(true) : '' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $safe(theme_v4_document_title($title ?? null)); ?></title>
    <?php echo theme_v4_head_tags($bbIsPreview, $siteDesc); ?>
    <?php echo theme_v4_favicon_tag(); ?>
    <?php \CMS\Hooks::doAction('head'); ?>
</head>
<body class="<?php echo $safe(implode(' ', $bodyClasses)); ?>">
<div id="page" class="bb-page-wrapper">

    <a class="bb-skip-link" href="#main"><?php echo $safe(theme_v4_t('Zum Inhalt springen', 'Skip to content', $bbLocale)); ?></a>

    <?php if ($hasEmBanner) : ?>
        <?php // Kein role="alert": der Hinweis ist statisch und soll nicht bei jedem Seitenaufruf vorgelesen werden. ?>
        <div class="bb-emergency-banner">
            <?php echo $safe($emergencyLabel); ?>
            <a class="bb-emergency-link" href="tel:<?php echo $safe($telDigits); ?>"><?php echo $safe($emergencyPhone); ?></a>
        </div>
    <?php endif; ?>

    <header id="masthead" class="bb-site-header">
        <div class="bb-header-inner">

            <div class="bb-branding">
                <a href="<?php echo $safe($homeUrl); ?>" class="bb-site-logo" rel="home">
                    <?php if ($logoSafeUrl !== '') : ?>
                        <img src="<?php echo $safe($logoSafeUrl); ?>" alt="<?php echo $safe($siteTitle); ?>" width="<?php echo $logoDims !== null ? $logoDims[0] : 160; ?>" height="<?php echo $logoDims !== null ? $logoDims[1] : 44; ?>" decoding="async">
                    <?php else : ?>
                        <span class="bb-logo-text">
                            <span class="bb-logo-icon" aria-hidden="true">&#9874;</span>
                            <?php echo $safe($siteTitle); ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <nav id="site-navigation" class="main-navigation" aria-label="<?php echo $safe(theme_v4_t('Hauptnavigation', 'Main navigation', $bbLocale)); ?>">
                <?php // Eine responsive Navigation für Desktop und Mobil: main_nav einmal (Kontext desktop), sonst doppelte Einträge. ?>
                <?php theme_nav_menu('primary-nav', 'desktop'); ?>
            </nav>

            <div class="bb-header-actions">
                <?php if ($showSearch) : ?>
                    <button id="searchToggle" type="button" class="bb-icon-btn" aria-label="Suche öffnen" aria-expanded="false" aria-controls="searchPanel">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                <?php endif; ?>

                <?php if ($isLoggedIn) : ?>
                    <a href="<?php echo $safe($memberUrl); ?>" class="bb-btn bb-btn-primary">Dashboard</a>
                <?php else : ?>
                    <a href="<?php echo $safe($loginUrl); ?>"    class="bb-btn bb-btn-ghost">Anmelden</a>
                    <a href="<?php echo $safe($registerUrl); ?>" class="bb-btn bb-btn-primary">Profil anlegen</a>
                <?php endif; ?>

                <button id="mobileMenuToggle" type="button" class="bb-mobile-toggle" aria-label="Menü öffnen" aria-expanded="false" aria-controls="site-navigation">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <?php if ($showSearch) : ?>
            <div id="searchPanel" class="bb-search-panel" hidden>
                <div class="bb-header-inner">
                    <form role="search" method="get" action="<?php echo $safe($searchUrl); ?>" class="bb-search-form">
                        <label for="bb-search" class="bb-visually-hidden">Suche</label>
                        <input id="bb-search" type="search" name="q" placeholder="Handwerker, Baufirmen, Projekte suchen …" autocomplete="off" class="bb-search-input bb-focus-shadow">
                        <button type="submit" class="bb-btn bb-btn-primary" aria-label="Suchen">Suchen</button>
                    </form>
                    <button id="searchClose" type="button" class="bb-btn bb-btn-ghost" aria-label="Suche schließen"><span aria-hidden="true">&times;</span></button>
                </div>
            </div>
        <?php endif; ?>
    </header>

<div id="content" class="bb-site-content">
<?php if ($bbIsPreview) : ?>
<p class="theme-v4-preview-banner" role="status"><?php echo $safe(theme_v4_t('Vorschau – dieser Inhalt ist nicht veröffentlicht.', 'Preview – this content is not published.', $bbLocale)); ?></p>
<?php endif; ?>
