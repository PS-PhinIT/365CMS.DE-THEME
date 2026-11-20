<?php
declare(strict_types=1);

/**
 * Business Theme – Header
 *
 * @package IT_Business_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Header in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_HEADER_RENDERED')) {
    return;
}
define('CMS_THEME_HEADER_RENDERED', true);

$bizSiteTitle = biz_site_title(); // bereits escaped; $title bleibt der Seitentitel aus Modulen/Router
$siteUrl      = biz_site_url();
$bizLocale    = theme_v4_locale($contentLocale ?? null);
$bizIsPreview = theme_v4_is_preview(get_defined_vars());
$bodyCls      = biz_body_class($bizIsPreview ? 'is-preview' : '');
$bizContact   = biz_href('/contact'); // Core-Route (365CMS 4.0.0) mit Kontaktformular

try {
    $_logoUrl = (string) \CMS\Services\ThemeCustomizer::instance()->get('header', 'logo_url', '');
} catch (\Throwable) {
    $_logoUrl = '';
}
$logoUrl  = theme_v4_site_logo(biz_safe_url($_logoUrl));
$logoDims = $logoUrl !== '' ? theme_v4_image_dimensions($logoUrl) : null;
?>
<!DOCTYPE html>
<html lang="<?php echo theme_v4_e($bizLocale); ?>">
<head>
    <meta charset="UTF-8">
    <?= function_exists('cms_csp_runtime_tags') ? cms_csp_runtime_tags(true) : '' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo theme_v4_e(theme_v4_document_title($title ?? null)); ?></title>
    <?php echo theme_v4_head_tags($bizIsPreview, \CMS\ThemeManager::instance()->getSiteDescription()); ?>
    <?php echo theme_v4_favicon_tag(); ?>
    <?php \CMS\Hooks::doAction('head'); ?>
</head>

<body class="<?php echo $bodyCls; ?>">
<a class="skip-link" href="#main-content"><?php echo theme_v4_e(theme_v4_t('Zum Inhalt springen', 'Skip to content', $bizLocale)); ?></a>
<?php if ($bizIsPreview) : ?>
<p class="theme-v4-preview-banner" role="status"><?php echo theme_v4_e(theme_v4_t('Vorschau – dieser Inhalt ist nicht veröffentlicht.', 'Preview – this content is not published.', $bizLocale)); ?></p>
<?php endif; ?>

<div class="biz-site">

    <header class="biz-header" id="biz-masthead">
        <div class="biz-container">
            <div class="biz-header-inner">

                <!-- Logo -->
                <a href="<?php echo htmlspecialchars(biz_href('/'), ENT_QUOTES, 'UTF-8'); ?>" class="biz-logo biz-focus-shadow" rel="home">
                    <?php if ($logoUrl !== '') : ?>
                        <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo $bizSiteTitle; ?>" class="biz-logo-img" decoding="async"<?php echo $logoDims !== null ? ' width="' . $logoDims[0] . '" height="' . $logoDims[1] . '"' : ''; ?>>
                    <?php else : ?>
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"
                             aria-hidden="true" focusable="false">
                            <rect width="32" height="32" rx="6" fill="#c08a2e"/>
                            <path d="M8 10h4v12H8V10zm6 0h4v12h-4V10zm6 4h4v8h-4v-8z" fill="#0c1320"/>
                        </svg>
                        <span class="biz-logo-text"><?php echo $bizSiteTitle; ?></span>
                    <?php endif; ?>
                </a>

                <!-- Desktop Navigation (main_nav-Kontext desktop; der Mobil-Drawer unten nutzt mobile) -->
                <nav class="biz-nav" id="biz-main-nav" aria-label="<?php echo theme_v4_e(theme_v4_t('Hauptnavigation', 'Main navigation', $bizLocale)); ?>">
                    <?php biz_nav_menu('primary', 'desktop'); ?>
                </nav>

                <!-- Header CTA -->
                <div class="biz-header-cta">
                    <a href="<?php echo htmlspecialchars($bizContact, ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn-biz btn-biz-primary">
                        <?php echo theme_v4_e(theme_v4_t('Kontakt', 'Contact', $bizLocale)); ?>
                    </a>
                </div>

                <!-- Mobile Toggle -->
                <button class="biz-mobile-toggle" id="bizMobileToggle"
                        aria-label="<?php echo theme_v4_e(theme_v4_t('Menü öffnen', 'Open menu', $bizLocale)); ?>" data-label-open="<?php echo theme_v4_e(theme_v4_t('Menü öffnen', 'Open menu', $bizLocale)); ?>" data-label-close="<?php echo theme_v4_e(theme_v4_t('Menü schließen', 'Close menu', $bizLocale)); ?>" aria-expanded="false" aria-controls="bizMobileDrawer"
                        type="button">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </button>

            </div>
        </div>
    </header>

    <!-- Mobile Overlay & Drawer (geschlossen: inert, damit die Links nicht per Tab erreichbar sind) -->
    <div class="biz-mobile-overlay" id="bizMobileOverlay" aria-hidden="true"></div>
    <nav id="bizMobileDrawer" class="biz-mobile-drawer" aria-label="<?php echo theme_v4_e(theme_v4_t('Mobile Navigation', 'Mobile navigation', $bizLocale)); ?>" inert>
        <?php biz_nav_menu('primary', 'mobile'); ?>
        <div class="biz-drawer-cta">
            <a href="<?php echo htmlspecialchars($bizContact, ENT_QUOTES, 'UTF-8'); ?>"
               class="btn-biz btn-biz-primary biz-drawer-cta-link">
                <?php echo theme_v4_e(theme_v4_t('Kontakt aufnehmen', 'Get in touch', $bizLocale)); ?>
            </a>
        </div>
    </nav>

    <main id="main-content" class="biz-content" tabindex="-1">
