<?php
/**
 * PTC Theme – Header
 *
 * @package PTC_Theme
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

$ptcSiteTitle = ptc_site_title(); // bereits escaped; $title bleibt der Seitentitel aus Modulen/Router
$siteUrl      = ptc_site_url();
$ptcLocale    = theme_v4_locale($contentLocale ?? null);
$ptcIsPreview = theme_v4_is_preview(get_defined_vars());

// Auth-Status
$isLoggedIn  = false;
$isAdmin     = false;
$currentUser = null;
try {
    $auth        = \CMS\Auth::instance();
    $isLoggedIn  = $auth->isLoggedIn();
    $isAdmin     = $auth->isAdmin();
    if ($isLoggedIn) {
        $currentUser = $auth->getCurrentUser();
    }
} catch (\Throwable $e) {
    // Auth nicht verfügbar
}

// User-Initialen
$userInitials = 'U';
$displayName  = 'Benutzer';
if ($currentUser) {
    $userName     = (string) (is_array($currentUser) ? ($currentUser['username'] ?? '') : ($currentUser->username ?? ''));
    $userInitials = $userName !== '' ? mb_strtoupper(mb_substr($userName, 0, 2)) : 'U';
    $displayName  = (string) (is_array($currentUser)
        ? ($currentUser['display_name'] ?? $currentUser['username'] ?? 'Benutzer')
        : ($currentUser->display_name ?? $currentUser->username ?? 'Benutzer'));
}

// Customizer-Einstellungen
$_logoUrl       = theme_v4_site_logo(ptc_safe_url((string) ptc_customizer_get('header', 'logo_url', '')));
$_showLoginBtn  = filter_var(ptc_customizer_get('header', 'show_login_btn', false), FILTER_VALIDATE_BOOLEAN);
$_loginIconOnly = filter_var(ptc_customizer_get('header', 'login_btn_icon_only', false), FILTER_VALIDATE_BOOLEAN);
$_showRegBtn    = filter_var(ptc_customizer_get('header', 'show_register_btn', false), FILTER_VALIDATE_BOOLEAN);
$_regIconOnly   = filter_var(ptc_customizer_get('header', 'register_btn_icon_only', false), FILTER_VALIDATE_BOOLEAN);
$_headerCtaText = trim((string) ptc_customizer_get('header', 'header_cta_text', 'Kontakt'));
$_headerCtaUrl  = ptc_href((string) ptc_customizer_get('header', 'header_cta_url', '/#kontakt'));
$_headerAnim    = (string) ptc_customizer_get('header', 'header_animation', 'none');
$_headerAnim    = in_array($_headerAnim, ['none', 'gradient', 'particles', 'pulse', 'wave'], true) ? $_headerAnim : 'none';
// Header-CTA nur zeigen, wenn das Ziel existiert (Anker auf der Startseite gelten als vorhanden).
if ($_headerCtaText !== '' && !theme_v4_link_available($_headerCtaUrl)) {
    $_headerCtaText = '';
}
?>
<!DOCTYPE html>
<html lang="<?php echo theme_v4_e($ptcLocale); ?>">
<head>
    <meta charset="UTF-8">
    <?= function_exists('cms_csp_runtime_tags') ? cms_csp_runtime_tags(true) : '' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo theme_v4_e(theme_v4_document_title($title ?? null)); ?></title>
    <?php echo theme_v4_head_tags($ptcIsPreview, \CMS\ThemeManager::instance()->getSiteDescription()); ?>
    <?php echo theme_v4_favicon_tag(); ?>
    <?php \CMS\Hooks::doAction('head'); ?>
</head>

<body class="<?php echo ptc_body_class($ptcIsPreview ? 'is-preview' : ''); ?>">
<?php \CMS\Hooks::doAction('body_start'); ?>
<a class="skip-link" href="#main-content"><?php echo theme_v4_e(theme_v4_t('Zum Inhalt springen', 'Skip to content', $ptcLocale)); ?></a>
<?php if ($ptcIsPreview) : ?>
<p class="theme-v4-preview-banner" role="status"><?php echo theme_v4_e(theme_v4_t('Vorschau – dieser Inhalt ist nicht veröffentlicht.', 'Preview – this content is not published.', $ptcLocale)); ?></p>
<?php endif; ?>

<div class="ptc-site">

    <header class="ptc-header" id="ptc-masthead"<?php echo $_headerAnim !== 'none' ? ' data-header-anim="' . htmlspecialchars($_headerAnim, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
        <?php if ($_headerAnim !== 'none'): ?>
            <div class="ptc-header-anim" aria-hidden="true"></div>
        <?php endif; ?>
        <div class="ptc-container">
            <div class="ptc-header-inner">

                <!-- Logo -->
                <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/" class="ptc-logo" rel="home">
                    <?php if ($_logoUrl !== '') : ?>
                        <?php $ptcLogoDims = theme_v4_image_dimensions($_logoUrl); ?>
                        <img src="<?php echo htmlspecialchars($_logoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo $ptcSiteTitle; ?>" class="ptc-logo-img" decoding="async"<?php echo $ptcLogoDims !== null ? ' width="' . $ptcLogoDims[0] . '" height="' . $ptcLogoDims[1] . '"' : ''; ?>>
                    <?php else : ?>
                        <svg class="ptc-logo-mark" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <rect width="36" height="36" rx="8" fill="var(--color-primary)"/>
                            <path d="M9 11h6v2H9zm0 5h6v2H9zm0 5h4v2H9zm10-10h8v2h-8zm0 5h6v2h-6zm0 5h5v2h-5z" fill="var(--color-accent)"/>
                        </svg>
                        <span class="ptc-logo-text"><?php echo $ptcSiteTitle; ?></span>
                    <?php endif; ?>
                </a>

                <!-- Desktop Navigation (main_nav-Kontext desktop; der Mobil-Drawer unten nutzt mobile) -->
                <nav class="ptc-nav" id="ptc-main-nav" aria-label="<?php echo theme_v4_e(theme_v4_t('Hauptnavigation', 'Main navigation', $ptcLocale)); ?>">
                    <?php ptc_nav_menu('primary', 'desktop'); ?>
                </nav>

                <!-- Header Actions -->
                <div class="ptc-header-actions">
                    <?php if ($isLoggedIn): ?>
                        <!-- Profil-Dropdown (Disclosure: Schalter + Linkliste, Escape schließt) -->
                        <div class="ptc-profile-dropdown" id="ptcProfileDropdown">
                            <button class="ptc-profile-toggle" type="button"
                                    aria-expanded="false" aria-controls="ptcProfileMenu"
                                    aria-label="<?php echo theme_v4_e('Benutzerkonto: ' . $displayName); ?>">
                                <span class="ptc-avatar" aria-hidden="true"><?php echo htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="ptc-profile-name" aria-hidden="true"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true" focusable="false"><path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </button>
                            <div class="ptc-profile-menu" id="ptcProfileMenu">
                                <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/member"><span aria-hidden="true">📊</span> Dashboard</a>
                                <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/member/profile"><span aria-hidden="true">👤</span> Mein Profil</a>
                                <?php if ($isAdmin): ?>
                                    <div class="ptc-profile-divider" aria-hidden="true"></div>
                                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/admin/"><span aria-hidden="true">⚙️</span> Administration</a>
                                <?php endif; ?>
                                <div class="ptc-profile-divider" aria-hidden="true"></div>
                                <?php // Abmelden als POST mit CSRF-Token (PublicRouter::handleLogout), kein GET-Link. ?>
                                <?php echo theme_v4_logout_form('Abmelden', 'ptc-profile-logout', 'ptc-profile-logout-form'); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($_showLoginBtn): ?>
                            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/login"
                               class="btn-ptc btn-ptc-ghost btn-ptc-sm<?php echo $_loginIconOnly ? ' btn-ptc-icon-only' : ''; ?>"
                               <?php echo $_loginIconOnly ? 'aria-label="Anmelden" title="Anmelden"' : ''; ?>>
                                <span aria-hidden="true">🔑</span><?php if (!$_loginIconOnly): ?> Anmelden<?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($_showRegBtn): ?>
                            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/register"
                               class="btn-ptc btn-ptc-accent btn-ptc-sm<?php echo $_regIconOnly ? ' btn-ptc-icon-only' : ''; ?>"
                               <?php echo $_regIconOnly ? 'aria-label="Registrieren" title="Registrieren"' : ''; ?>>
                                <span aria-hidden="true">✏️</span><?php if (!$_regIconOnly): ?> Registrieren<?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($_headerCtaText !== ''): ?>
                            <a href="<?php echo htmlspecialchars($_headerCtaUrl, ENT_QUOTES, 'UTF-8'); ?>"
                               class="btn-ptc btn-ptc-accent btn-ptc-sm">
                                <?php echo htmlspecialchars($_headerCtaText, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Mobile Toggle -->
                <button class="ptc-mobile-toggle" id="ptcMobileToggle"
                        aria-label="Menü öffnen" aria-expanded="false" aria-controls="ptcMobileDrawer"
                        type="button">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </button>

            </div>
        </div>
    </header>

    <?php // after_header löst der Core nach header.php selbst aus (ThemeManager::getHeader()) – hier nicht doppelt. ?>

    <!-- Mobile Overlay & Drawer (geschlossen: inert, damit die Links nicht per Tab erreichbar sind) -->
    <div class="ptc-mobile-overlay" id="ptcMobileOverlay" aria-hidden="true"></div>
    <nav id="ptcMobileDrawer" class="ptc-mobile-drawer" aria-label="<?php echo theme_v4_e(theme_v4_t('Mobile Navigation', 'Mobile navigation', $ptcLocale)); ?>" inert>
        <?php ptc_nav_menu('primary', 'mobile'); ?>

        <div class="ptc-drawer-actions">
            <?php if ($isLoggedIn): ?>
                <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/member" class="btn-ptc btn-ptc-accent ptc-drawer-btn">
                    <span aria-hidden="true">📊</span> Dashboard
                </a>
                <?php echo theme_v4_logout_form('Abmelden', 'btn-ptc btn-ptc-ghost ptc-drawer-btn', 'ptc-drawer-logout'); ?>
            <?php else: ?>
                <?php if ($_showLoginBtn): ?>
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/login" class="btn-ptc btn-ptc-ghost ptc-drawer-btn">
                        <span aria-hidden="true">🔑</span> Anmelden
                    </a>
                <?php endif; ?>
                <?php if ($_showRegBtn): ?>
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/register" class="btn-ptc btn-ptc-accent ptc-drawer-btn">
                        <span aria-hidden="true">✏️</span> Registrieren
                    </a>
                <?php endif; ?>
                <?php if ($_headerCtaText !== ''): ?>
                    <a href="<?php echo htmlspecialchars($_headerCtaUrl, ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn-ptc btn-ptc-accent ptc-drawer-btn">
                        <?php echo htmlspecialchars($_headerCtaText, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </nav>

    <main id="main-content" class="ptc-content" tabindex="-1">
