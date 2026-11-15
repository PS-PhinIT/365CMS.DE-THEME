<?php
/**
 * PersonalFlow Theme – 404 Template
 *
 * @package PersonalFlow_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$safe     = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$pfLocale = theme_v4_locale($contentLocale ?? null);
// /jobs ist keine Core-Route – „Offene Stellen“ erscheint nur, wenn es die Seite gibt.
$pfJobsUrl = theme_v4_link_available(theme_route_url('jobs')) ? theme_route_url('jobs') : '';
?>
<main id="main" class="pf-main pf-error-shell">
    <div class="pf-container">
        <div class="pf-error-card">
            <p class="pf-error-code" aria-hidden="true">404</p>
            <h1><?php echo $safe(theme_v4_t('Seite nicht gefunden', 'Page not found', $pfLocale)); ?></h1>
            <p><?php echo $safe(theme_v4_t('Diese Seite oder dieses Profil existiert nicht (mehr) oder wurde an einen anderen Pfad verschoben.', 'This page or profile no longer exists or has been moved.', $pfLocale)); ?></p>
            <div class="pf-error-actions">
                <a href="<?php echo $safe(theme_route_url('home')); ?>" class="pf-btn pf-btn-primary"><?php echo $safe(theme_v4_t('Zur Startseite', 'Back to home', $pfLocale)); ?></a>
                <a href="<?php echo $safe(theme_route_url('search')); ?>" class="pf-btn pf-btn-ghost"><?php echo $safe(theme_v4_t('Suche öffnen', 'Open search', $pfLocale)); ?></a>
                <?php if ($pfJobsUrl !== '') : ?>
                <a href="<?php echo $safe($pfJobsUrl); ?>" class="pf-btn pf-btn-ghost">Offene Stellen</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
