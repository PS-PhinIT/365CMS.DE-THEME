<?php
declare(strict_types=1);

/**
 * Business Theme – 404 Fehlerseite
 *
 * @package IT_Business_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!headers_sent()) {
    http_response_code(404); // der Core setzt den Status meist schon vor header.php
}
$bizLocale = theme_v4_locale($contentLocale ?? null);
// Standard-CTAs nur zu vorhandenen Zielen (Kontakt und Suche sind ab 365CMS 4.0.0 Core-Routen).
$bizNotFoundLinks = theme_v4_filter_links([
    ['label' => theme_v4_t('Kontakt aufnehmen', 'Get in touch', $bizLocale), 'url' => biz_href('/contact')],
    ['label' => theme_v4_t('Suche öffnen', 'Open search', $bizLocale),      'url' => biz_href('/search')],
]);
?>

<section class="biz-page-hero">
    <div class="biz-container">
        <p class="biz-page-eyebrow"><?php echo theme_v4_e(theme_v4_t('Fehler 404', 'Error 404', $bizLocale)); ?></p>
        <h1><?php echo theme_v4_e(theme_v4_t('Seite nicht gefunden', 'Page not found', $bizLocale)); ?></h1>
        <p><?php echo theme_v4_e(theme_v4_t('Die angeforderte Seite existiert nicht oder wurde verschoben.', 'The requested page does not exist or has been moved.', $bizLocale)); ?></p>
    </div>
</section>

<div class="biz-page-content">
    <div class="biz-container">
        <div class="biz-error-wrap">
            <p class="biz-error-icon" aria-hidden="true">🔍</p>
            <h2 class="biz-error-title"><?php echo theme_v4_e(theme_v4_t('Ups – hier gibt es nichts zu sehen.', 'Oops – there is nothing to see here.', $bizLocale)); ?></h2>
            <p class="biz-error-text">
                <?php echo theme_v4_e(theme_v4_t('Möglicherweise wurde die Seite umbenannt oder gelöscht. Nutzen Sie die Navigation oder kehren Sie zur Startseite zurück.', 'The page may have been renamed or deleted. Use the navigation or return to the home page.', $bizLocale)); ?>
            </p>
            <div class="biz-error-actions">
                <a href="<?php echo htmlspecialchars(biz_href('/'), ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-primary btn-biz-lg"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $bizLocale)); ?></a>
                <?php foreach ($bizNotFoundLinks as $bizLink) : ?>
                <a href="<?php echo htmlspecialchars($bizLink['url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-ghost btn-biz-lg"><?php echo htmlspecialchars($bizLink['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
