<?php
/**
 * LogiLink Theme – 404 Template
 *
 * @package LogiLink_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$llLocale  = theme_v4_locale($contentLocale ?? null);
$homeUrl   = htmlspecialchars(theme_route_url('home'),     ENT_QUOTES, 'UTF-8');
$searchUrl = htmlspecialchars(theme_route_url('search'),   ENT_QUOTES, 'UTF-8');
// /tracking ist keine Core-Route – der Button erscheint nur, wenn es die Seite gibt.
$trackUrl  = theme_v4_link_available(theme_route_url('tracking'))
    ? htmlspecialchars(theme_route_url('tracking'), ENT_QUOTES, 'UTF-8')
    : '';
?>
<main id="main" class="ll-main ll-error-wrap">
    <div class="ll-container">
        <div class="ll-card ll-error-card">
            <p class="ll-error-code" aria-hidden="true">404</p>
            <h1><?php echo theme_v4_e(theme_v4_t('Seite nicht gefunden', 'Page not found', $llLocale)); ?></h1>
            <p><?php echo theme_v4_e(theme_v4_t('Die gesuchte Seite existiert nicht oder wurde verschoben.', 'The page you are looking for does not exist or has been moved.', $llLocale)); ?></p>
            <div class="ll-error-actions">
                <a href="<?php echo $homeUrl; ?>"  class="ll-btn ll-btn-primary"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $llLocale)); ?></a>
                <a href="<?php echo $searchUrl; ?>" class="ll-btn ll-btn-ghost"><?php echo theme_v4_e(theme_v4_t('Suche öffnen', 'Open search', $llLocale)); ?></a>
                <?php if ($trackUrl !== '') : ?>
                <a href="<?php echo $trackUrl; ?>" class="ll-btn ll-btn-accent">Sendung verfolgen</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
