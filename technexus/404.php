<?php
/**
 * TechNexus Theme – 404 Fehlerseite
 *
 * @package TechNexus_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$tnLocale = theme_v4_locale($contentLocale ?? null);
// /it-experts kommt aus einem Plugin – der Button erscheint nur, wenn es die Route/Seite gibt.
$tnExpertsUrl = theme_v4_link_available(theme_route_url('experts')) ? theme_route_url('experts') : '';
?>

<main id="main" class="site-main error-page tn-section">
    <div class="container tn-error-layout">
        <div class="tech-card tn-error-card">
            <p class="tn-error-code" aria-hidden="true">404</p>
            <h1><?php echo tn_html_attr(theme_v4_t('Seite nicht gefunden', 'Page not found', $tnLocale)); ?></h1>
            <p class="tech-card__meta">
                <?php echo tn_html_attr(theme_v4_t('Die gesuchte Seite existiert nicht oder wurde verschoben.', 'The page you are looking for does not exist or has been moved.', $tnLocale)); ?>
            </p>
            <div class="tn-error-actions">
                <a href="<?php echo tn_html_attr(theme_route_url('home')); ?>" class="btn btn-primary"><?php echo tn_html_attr(theme_v4_t('Zurück zur Startseite', 'Back to home', $tnLocale)); ?></a>
                <a href="<?php echo tn_html_attr(theme_route_url('search')); ?>" class="btn btn-outline"><?php echo tn_html_attr(theme_v4_t('Suche öffnen', 'Open search', $tnLocale)); ?></a>
                <?php if ($tnExpertsUrl !== '') : ?>
                <a href="<?php echo tn_html_attr($tnExpertsUrl); ?>" class="btn btn-outline">Experten suchen</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
