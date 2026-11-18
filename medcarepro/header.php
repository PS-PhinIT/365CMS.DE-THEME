<?php
/**
 * MedCare Pro Theme – Header Template
 *
 * Erhält die Render-Daten des Templates ($post, $page, $title, $isPreview, $contentLocale …).
 * Öffnet kein <main>: Jedes Template setzt <main id="main"> (Skip-Link-Ziel) selbst.
 *
 * @package MedCarePro_Theme
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

// Vorschau-Links ($isPreview) und nicht veröffentlichte Inhalte: noindex, keine strukturierten Daten, Hinweisleiste.
$mcIsPreview = theme_v4_is_preview(get_defined_vars());
$mcLocale    = theme_v4_locale($contentLocale ?? null);

$mcTitle    = mc_site_title();
$mcTitleEsc = htmlspecialchars($mcTitle, ENT_QUOTES, 'UTF-8');
$mcBodyCls  = mc_body_class($mcIsPreview ? 'is-preview' : '');
$mcSiteDesc = '';
try {
    $mcSiteDesc = (string) \CMS\ThemeManager::instance()->getSiteDescription();
} catch (\Throwable) {
    $mcSiteDesc = '';
}

$mcHomeUrl     = htmlspecialchars(theme_route_url('home'),     ENT_QUOTES, 'UTF-8');
$mcSearchUrl   = htmlspecialchars(theme_route_url('search'),   ENT_QUOTES, 'UTF-8');
$mcLoginUrl    = htmlspecialchars(theme_route_url('login'),    ENT_QUOTES, 'UTF-8');
$mcRegisterUrl = htmlspecialchars(theme_route_url('register'), ENT_QUOTES, 'UTF-8');
$mcMemberUrl   = htmlspecialchars(theme_route_url('member'),   ENT_QUOTES, 'UTF-8');

// Logo: Theme-Einstellung (nur lokale URLs), sonst Core-Option site_logo.
$mcLogoUrl       = theme_v4_site_logo((string) mc_get_setting('header', 'logo_url', ''));
$mcLogoSize      = $mcLogoUrl !== '' ? theme_v4_image_dimensions($mcLogoUrl) : null;
$mcShowSearch    = true;
$mcShowFontSize  = filter_var(mc_get_setting('accessibility', 'enable_font_size_toggle',    true),  FILTER_VALIDATE_BOOLEAN);
$mcShowContrast  = filter_var(mc_get_setting('accessibility', 'enable_high_contrast_toggle', true), FILTER_VALIDATE_BOOLEAN);
$mcShowEmergency = filter_var(mc_get_setting('header',        'show_emergency_banner',      false), FILTER_VALIDATE_BOOLEAN);

$mcEmergencyPhone  = (string) mc_get_setting('header', 'emergency_phone',       '112');
$mcEmergencyBanner = (string) mc_get_setting('header', 'emergency_banner_text', 'Notfall? Bitte rufen Sie sofort an:');
$mcTelHref         = mc_tel_sanitize($mcEmergencyPhone);
$mcIsLoggedIn      = theme_is_logged_in();
$mcCanRegister     = !$mcIsLoggedIn && mc_registration_enabled();
$mcLogoutForm      = $mcIsLoggedIn ? theme_v4_logout_form(theme_v4_t('Abmelden', 'Sign out', $mcLocale), 'mc-btn mc-btn-ghost') : '';

$mcEmergencyActive = $mcShowEmergency && $mcTelHref !== '' && $mcEmergencyPhone !== '';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($mcLocale, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <?= function_exists('cms_csp_runtime_tags') ? cms_csp_runtime_tags(true) : '' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(theme_v4_document_title($title ?? null), ENT_QUOTES, 'UTF-8'); ?></title>
    <?php // Core-SEO (Beschreibung, Robots, Canonical, Open Graph, JSON-LD); Vorschau: noindex, ohne JSON-LD. ?>
    <?php echo theme_v4_head_tags($mcIsPreview, $mcSiteDesc); ?>
    <?php echo theme_v4_favicon_tag(); ?>
    <?php \CMS\Hooks::doAction('head'); ?>
</head>
<body class="<?php echo $mcBodyCls; ?>">
<a class="mc-skip-link" href="#main"><?php echo htmlspecialchars(theme_v4_t('Zum Inhalt springen', 'Skip to content', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></a>
<div id="page" class="mc-page-wrapper">
<?php if ($mcIsPreview) : ?>
<p class="theme-v4-preview-banner" role="status"><?php echo htmlspecialchars(theme_v4_t('Vorschau – dieser Inhalt ist nicht veröffentlicht.', 'Preview – this content is not published.', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<?php if ($mcEmergencyActive) : ?>
<div class="mc-emergency-banner" role="note" aria-label="<?php echo htmlspecialchars(theme_v4_t('Notfall', 'Emergency', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="mc-container mc-emergency-banner-row">
        <span class="mc-emergency-banner-label">
            <span class="mc-emergency-banner-icon" aria-hidden="true">⚠</span>
            <?php echo htmlspecialchars($mcEmergencyBanner, ENT_QUOTES, 'UTF-8'); ?>
        </span>
        <a href="tel:<?php echo htmlspecialchars($mcTelHref, ENT_QUOTES, 'UTF-8'); ?>" class="mc-emergency-phone">
            <?php echo htmlspecialchars($mcEmergencyPhone, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    </div>
</div>
<?php endif; ?>

<header id="masthead" class="mc-site-header">
    <div class="mc-header-inner">

        <div class="mc-branding">
            <a href="<?php echo $mcHomeUrl; ?>" rel="home" class="mc-branding-link" aria-label="<?php echo $mcTitleEsc; ?>">
                <?php if ($mcLogoUrl !== '') : ?>
                    <img src="<?php echo htmlspecialchars($mcLogoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo $mcTitleEsc; ?>" class="mc-logo-img"<?php echo $mcLogoSize !== null ? ' width="' . (int) $mcLogoSize[0] . '" height="' . (int) $mcLogoSize[1] . '"' : ''; ?>>
                <?php else : ?>
                    <span class="mc-logo-text">
                        <span class="mc-logo-mark" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg"
                                 focusable="false" aria-hidden="true">
                                <rect x="2" y="2" width="20" height="20" rx="4" fill="currentColor" opacity=".12"/>
                                <path d="M10 5h4v5h5v4h-5v5h-4v-5H5v-4h5z" fill="currentColor"/>
                            </svg>
                        </span>
                        <?php echo $mcTitleEsc; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <nav id="site-navigation" class="main-navigation" aria-label="<?php echo htmlspecialchars(theme_v4_t('Hauptnavigation', 'Main navigation', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>">
            <?php
            // Eine responsive Navigation für Desktop und Mobil (≤ 768 px als Aufklappmenü): Core-Hook main_nav
            // deshalb einmal im Kontext `desktop` – zwei Aufrufe ergäben doppelte Einträge.
            theme_nav_menu('primary-nav', 'desktop');
            ?>
            <ul class="mc-nav-account">
                <?php // Kontolinks im Aufklappmenü: Die Header-Buttons sind ≤ 768 px ausgeblendet. ?>
                <?php if ($mcIsLoggedIn) : ?>
                    <li><a href="<?php echo $mcMemberUrl; ?>"><?php echo htmlspecialchars(theme_v4_t('Mein Bereich', 'My account', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <?php if ($mcLogoutForm !== '') : ?><li><?php echo $mcLogoutForm; // CSRF-geschütztes POST-Formular ?></li><?php endif; ?>
                <?php else : ?>
                    <li><a href="<?php echo $mcLoginUrl; ?>"><?php echo htmlspecialchars(theme_v4_t('Anmelden', 'Sign in', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <?php if ($mcCanRegister) : ?>
                        <li><a href="<?php echo $mcRegisterUrl; ?>"><?php echo htmlspecialchars(theme_v4_t('Arzt registrieren', 'Register as a doctor', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="mc-header-actions">
            <?php if ($mcShowFontSize) : ?>
                <button id="fontSizeToggle"
                        type="button"
                        class="mc-icon-btn mc-font-size-toggle"
                        aria-label="<?php echo htmlspecialchars(theme_v4_t('Größere Schrift', 'Larger text', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>"
                        aria-pressed="false"
                        title="<?php echo htmlspecialchars(theme_v4_t('Schriftgröße', 'Text size', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true">A±</span></button>
            <?php endif; ?>
            <?php if ($mcShowContrast) : ?>
                <button id="contrastToggle"
                        type="button"
                        class="mc-icon-btn mc-contrast-toggle"
                        aria-label="<?php echo htmlspecialchars(theme_v4_t('Hoher Kontrast', 'High contrast', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>"
                        aria-pressed="false"
                        title="<?php echo htmlspecialchars(theme_v4_t('Hoher Kontrast', 'High contrast', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true">◐</span></button>
            <?php endif; ?>
            <?php if ($mcShowSearch) : ?>
                <button id="searchToggle"
                        type="button"
                        class="mc-icon-btn mc-search-toggle-btn"
                        aria-label="<?php echo htmlspecialchars(theme_v4_t('Suche öffnen', 'Open search', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>"
                        aria-expanded="false"
                        aria-controls="searchPanel">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         focusable="false" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </button>
            <?php endif; ?>
            <?php if ($mcIsLoggedIn) : ?>
                <a href="<?php echo $mcMemberUrl; ?>" class="mc-btn mc-btn-primary"><?php echo htmlspecialchars(theme_v4_t('Mein Bereich', 'My account', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></a>
                <?php echo $mcLogoutForm; // Logout als CSRF-geschütztes POST-Formular (kein GET-Link) ?>
            <?php else : ?>
                <a href="<?php echo $mcLoginUrl; ?>"    class="mc-btn mc-btn-ghost"><?php echo htmlspecialchars(theme_v4_t('Anmelden', 'Sign in', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></a>
                <?php if ($mcCanRegister) : ?>
                    <a href="<?php echo $mcRegisterUrl; ?>" class="mc-btn mc-btn-primary"><?php echo htmlspecialchars(theme_v4_t('Arzt registrieren', 'Register as a doctor', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
            <?php endif; ?>
            <button id="mobileMenuToggle"
                    type="button"
                    class="mc-mobile-toggle"
                    aria-label="<?php echo htmlspecialchars(theme_v4_t('Menü öffnen', 'Open menu', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>"
                    aria-expanded="false"
                    aria-controls="site-navigation">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <?php if ($mcShowSearch) : ?>
    <div id="searchPanel" class="mc-search-panel" hidden>
        <div class="mc-header-inner mc-search-panel-inner">
            <form role="search" method="get" action="<?php echo $mcSearchUrl; ?>" class="mc-header-search-form">
                <label for="mc-search" class="mc-visually-hidden"><?php echo htmlspecialchars(theme_v4_t('Arzt, Fachgebiet oder PLZ suchen', 'Search for a doctor, specialty or postcode', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></label>
                <input id="mc-search"
                       type="search"
                       name="q"
                       placeholder="<?php echo htmlspecialchars(theme_v4_t('Arzt, Fachgebiet, PLZ suchen …', 'Doctor, specialty, postcode …', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>"
                       autocomplete="off"
                       class="mc-header-search-input">
                <button type="submit" class="mc-btn mc-btn-primary"><?php echo htmlspecialchars(theme_v4_t('Suchen', 'Search', $mcLocale), ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
            <button id="searchClose"
                    type="button"
                    class="mc-icon-btn mc-search-close"
                    aria-label="<?php echo htmlspecialchars(theme_v4_t('Suche schließen', 'Close search', $mcLocale), ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true">✕</span></button>
        </div>
    </div>
    <?php endif; ?>
</header><!-- #masthead -->
<div id="content" class="mc-site-content">
