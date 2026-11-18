<?php
/**
 * LogiLink Theme – Header Template
 *
 * @package LogiLink_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Header in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_HEADER_RENDERED')) {
    return;
}
define('CMS_THEME_HEADER_RENDERED', true);

// header.php erhält die Render-Daten des Routers ($title, $page, $post, $isPreview, $contentLocale …).
$llLocale    = theme_v4_locale($contentLocale ?? null);
$llIsPreview = theme_v4_is_preview(get_defined_vars());
$llTitle      = ll_site_title();
$llTitleEsc   = htmlspecialchars($llTitle, ENT_QUOTES, 'UTF-8');
$llBodyClass  = ll_body_class();
$llHomeUrl    = htmlspecialchars(theme_route_url('home'),     ENT_QUOTES, 'UTF-8');
$llLoginUrl   = htmlspecialchars(theme_route_url('login'),    ENT_QUOTES, 'UTF-8');
$llRegisterUrl= htmlspecialchars(theme_route_url('register'), ENT_QUOTES, 'UTF-8');
$llMemberUrl  = htmlspecialchars(theme_route_url('member'),   ENT_QUOTES, 'UTF-8');
$llTrackUrl   = htmlspecialchars(theme_route_url('tracking'), ENT_QUOTES, 'UTF-8');
$llSearchUrl  = htmlspecialchars(theme_route_url('search'),   ENT_QUOTES, 'UTF-8');

$llLogoUrl     = theme_v4_site_logo((string) ll_get_setting('header', 'logo_url', ''));
// /tracking ist keine Core-Route: Schnellsuche und Drawer-Link nur, wenn eine Seite/Route dafür existiert.
$llHasTracking = theme_v4_link_available(theme_route_url('tracking'));
$llShowTrack   = $llHasTracking && filter_var(
    ll_get_setting('header', 'show_tracking_quick_search', true),
    FILTER_VALIDATE_BOOLEAN
);
$llTrackPh     = (string) ll_get_setting('header', 'tracking_placeholder', 'Sendungsnummer eingeben …');
$llNoResultMsg = (string) ll_get_setting('logistics_tracking', 'tracking_no_result_text', 'Keine Sendung zu dieser Nummer gefunden.');
$llIsLoggedIn  = theme_is_logged_in();
?><!DOCTYPE html>
<html lang="<?php echo theme_v4_e($llLocale); ?>">
<head>
    <meta charset="UTF-8">
    <?= function_exists('cms_csp_runtime_tags') ? cms_csp_runtime_tags(true) : '' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo theme_v4_e(theme_v4_document_title($title ?? null)); ?></title>
    <?php echo theme_v4_head_tags($llIsPreview, \CMS\ThemeManager::instance()->getSiteDescription()); ?>
    <?php echo theme_v4_favicon_tag(); ?>
    <?php \CMS\Hooks::doAction('head'); ?>
</head>
<body class="<?php echo $llBodyClass; ?><?php echo $llIsPreview ? ' is-preview' : ''; ?>">
<a class="ll-skip-link" href="#main"><?php echo theme_v4_e(theme_v4_t('Zum Inhalt springen', 'Skip to content', $llLocale)); ?></a>
<?php if ($llIsPreview) : ?>
<p class="theme-v4-preview-banner" role="status"><?php echo theme_v4_e(theme_v4_t('Vorschau – dieser Inhalt ist nicht veröffentlicht.', 'Preview – this content is not published.', $llLocale)); ?></p>
<?php endif; ?>

<div id="page" class="ll-page-wrapper">

    <header id="masthead" class="ll-site-header">
        <div class="ll-header-inner">

            <div class="ll-branding">
                <a href="<?php echo $llHomeUrl; ?>" class="ll-focus-shadow" rel="home" aria-label="<?php echo $llTitleEsc; ?>">
                    <?php if ($llLogoUrl !== '') : ?>
                        <?php $llLogoDims = theme_v4_image_dimensions($llLogoUrl); ?>
                        <img src="<?php echo htmlspecialchars($llLogoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo $llTitleEsc; ?>"<?php echo $llLogoDims !== null ? ' width="' . (int) $llLogoDims[0] . '" height="' . (int) $llLogoDims[1] . '"' : ''; ?>>
                    <?php else : ?>
                        <span class="ll-logo-text">
                            <span class="ll-logo-mark" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg"
                                     focusable="false" aria-hidden="true">
                                    <path d="M3 7h11v9H3z" fill="currentColor" opacity="0.85"/>
                                    <path d="M14 10h4l3 3v3h-7z" fill="currentColor"/>
                                    <circle cx="7"  cy="17" r="2" fill="#0c1a2e"/>
                                    <circle cx="17" cy="17" r="2" fill="#0c1a2e"/>
                                </svg>
                            </span>
                            <?php echo $llTitleEsc; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <nav id="site-navigation" class="main-navigation" aria-label="Hauptnavigation">
                <?php theme_nav_menu('primary-nav', 'desktop'); ?>
            </nav>

            <?php if ($llShowTrack) : ?>
                <form class="ll-tracking-quick" role="search" method="get" action="<?php echo $llTrackUrl; ?>"
                      data-no-result="<?php echo htmlspecialchars($llNoResultMsg, ENT_QUOTES, 'UTF-8'); ?>">
                    <label for="ll-track-input" class="ll-visually-hidden">Sendung verfolgen</label>
                    <input id="ll-track-input"
                           type="search"
                           name="id"
                           placeholder="<?php echo htmlspecialchars($llTrackPh, ENT_QUOTES, 'UTF-8'); ?>"
                           autocomplete="off"
                           aria-label="Sendungsnummer">
                    <button type="submit">Verfolgen</button>
                </form>
            <?php endif; ?>

            <div class="ll-header-actions">
                <?php if ($llIsLoggedIn) : ?>
                    <a href="<?php echo $llMemberUrl; ?>" class="ll-btn ll-btn-accent">Dashboard</a>
                <?php else : ?>
                    <a href="<?php echo $llLoginUrl; ?>"    class="ll-btn ll-btn-outline">Anmelden</a>
                    <a href="<?php echo $llRegisterUrl; ?>" class="ll-btn ll-btn-accent">Kostenlos starten</a>
                <?php endif; ?>

                <button id="llSearchToggle"
                        type="button"
                        class="ll-search-toggle"
                        aria-label="Suche öffnen"
                        aria-expanded="false"
                        aria-controls="llSearchPanel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         focusable="false" aria-hidden="true">
                        <circle cx="10.5" cy="10.5" r="6.5"></circle>
                        <line x1="20" y1="20" x2="15.5" y2="15.5"></line>
                    </svg>
                </button>

                <button id="llMobileToggle"
                        type="button"
                        class="ll-mobile-toggle"
                        aria-label="Menü öffnen"
                        aria-expanded="false"
                        aria-controls="llMobileDrawer">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </button>
            </div>
        </div>
    </header><!-- #masthead -->

    <!-- Mobile-Overlay + Drawer (geschlossen per CSS visibility:hidden, damit die Links nicht fokussierbar sind) -->
    <div class="ll-mobile-overlay" id="llMobileOverlay" aria-hidden="true"></div>
    <nav id="llMobileDrawer"
         class="ll-mobile-drawer"
         aria-label="<?php echo theme_v4_e(theme_v4_t('Mobile Navigation', 'Mobile navigation', $llLocale)); ?>">
        <?php theme_nav_menu('primary-nav', 'mobile'); ?>
        <div class="ll-drawer-cta">
            <?php if ($llIsLoggedIn) : ?>
                <a href="<?php echo $llMemberUrl; ?>" class="ll-btn ll-btn-accent">Dashboard</a>
            <?php else : ?>
                <a href="<?php echo $llLoginUrl; ?>"    class="ll-btn ll-btn-outline">Anmelden</a>
                <a href="<?php echo $llRegisterUrl; ?>" class="ll-btn ll-btn-accent">Kostenlos starten</a>
            <?php endif; ?>
            <?php if ($llHasTracking) : ?>
            <a href="<?php echo $llTrackUrl; ?>" class="ll-btn ll-btn-outline">Sendung verfolgen</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Mobile/Universal Search Panel -->
    <div id="llSearchPanel" class="ll-search-panel" hidden>
        <form class="ll-search-panel-inner" role="search" method="get" action="<?php echo $llSearchUrl; ?>">
            <label for="ll-search-input" class="ll-visually-hidden">Suche</label>
            <input id="ll-search-input"
                   type="search"
                   name="q"
                   placeholder="Sendungsnummer oder Suchbegriff …"
                   autocomplete="off">
            <button type="submit">Suchen</button>
            <button type="button" class="ll-search-close" id="llSearchClose" aria-label="Suche schließen">Schließen</button>
        </form>
    </div>

    <div id="content" class="ll-site-content">
