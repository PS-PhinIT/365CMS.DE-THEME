<?php
declare(strict_types=1);

/**
 * Business Theme – Index / Fallback
 *
 * @package IT_Business_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$bizLocale = theme_v4_locale($contentLocale ?? null);
// Fallback für Blog-/Kategorie-/Schlagwort-Archive: Der Core übergibt dann $posts.
if (isset($posts) && is_array($posts)) {
    require __DIR__ . '/blog.php';
    return;
}
?>

<section class="biz-page-hero">
    <div class="biz-container">
        <h1><?php echo theme_v4_e(theme_v4_t('Seite nicht gefunden', 'Page not found', $bizLocale)); ?></h1>
        <p><?php echo theme_v4_e(theme_v4_t('Die gesuchte Seite existiert nicht oder wurde verschoben.', 'The page you are looking for does not exist or has been moved.', $bizLocale)); ?></p>
    </div>
</section>

<div class="biz-page-content">
    <div class="biz-container">
        <div class="biz-error-wrap-narrow">
            <p class="biz-error-text"><?php echo theme_v4_e(theme_v4_t('Bitte nutzen Sie die Navigation oder kehren Sie zur Startseite zurück.', 'Please use the navigation or return to the home page.', $bizLocale)); ?></p>
            <a href="<?php echo htmlspecialchars(biz_href('/'), ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-primary"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $bizLocale)); ?></a>
        </div>
    </div>
</div>
