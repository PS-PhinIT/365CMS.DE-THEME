<?php
/**
 * PTC Theme – 404 Fehlerseite
 *
 * @package PTC_Theme
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$ptcLocale = theme_v4_locale($contentLocale ?? null);
// Standard-CTAs nur zu vorhandenen Zielen (Kontakt ist ab 365CMS 4.0.0 eine Core-Route).
$ptcNotFoundLinks = theme_v4_filter_links([
    ['label' => theme_v4_t('Suche öffnen', 'Open search', $ptcLocale), 'url' => ptc_href('/search')],
    ['label' => theme_v4_t('Kontakt', 'Contact', $ptcLocale), 'url' => ptc_href('/contact')],
]);
?>

<section class="ptc-page-hero">
    <div class="ptc-container">
        <p class="ptc-hero-overline"><?php echo theme_v4_e(theme_v4_t('Fehler 404', 'Error 404', $ptcLocale)); ?></p>
        <h1><?php echo theme_v4_e(theme_v4_t('Seite nicht gefunden', 'Page not found', $ptcLocale)); ?></h1>
    </div>
</section>

<div class="ptc-page-content">
    <div class="ptc-container ptc-center-block">
        <p class="ptc-error-code" aria-hidden="true">404</p>
        <h2><?php echo theme_v4_e(theme_v4_t('Die angeforderte Seite existiert nicht.', 'The requested page does not exist.', $ptcLocale)); ?></h2>
        <p class="ptc-text-muted"><?php echo theme_v4_e(theme_v4_t('Der Link ist veraltet oder die Adresse wurde falsch eingegeben.', 'The link is outdated or the address was mistyped.', $ptcLocale)); ?></p>
        <div class="ptc-error-actions">
            <a href="<?php echo theme_v4_e(ptc_href('/')); ?>" class="btn-ptc btn-ptc-primary btn-ptc-lg"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $ptcLocale)); ?></a>
            <?php foreach ($ptcNotFoundLinks as $ptcLink) : ?>
            <a href="<?php echo theme_v4_e($ptcLink['url']); ?>" class="btn-ptc btn-ptc-ghost btn-ptc-lg"><?php echo theme_v4_e($ptcLink['label']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
