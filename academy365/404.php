<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

http_response_code(404);
$siteUrl = rtrim(academy365_safe_url((string) SITE_URL, '/'), '/');
$siteUrl = $siteUrl !== '' ? $siteUrl : '/';
$safe = fn(string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$acLocale = theme_v4_locale($contentLocale ?? null);
$t = static fn(string $de, string $en): string => theme_v4_t($de, $en, $acLocale);
try {
    $c = \CMS\Services\ThemeCustomizer::instance();
    // Die Gruppe `error_pages` hat keine Customizer-Felder: Die Texte kommen aus den (zweisprachigen) Standardwerten.
    $notFoundTitle    = $c->get('error_pages', 'not_found_title',   $t('Seite nicht gefunden', 'Page not found'));
    $notFoundMessage  = $c->get('error_pages', 'not_found_message', $t('Diese Seite existiert leider nicht. Entdecke stattdessen unsere Kurse!', 'This page does not exist. Explore our courses instead!'));
    $notFoundCta      = $c->get('error_pages', 'not_found_cta',     $t('Kurse entdecken', 'Explore courses'));
} catch (\Throwable $e) {
    $notFoundTitle = $t('Seite nicht gefunden', 'Page not found'); $notFoundMessage = $t('Diese Seite existiert leider nicht.', 'This page does not exist.'); $notFoundCta = $t('Kurse entdecken', 'Explore courses');
}
?>
<main id="main" class="ac-main-content" role="main">
    <div class="ac-container ac-error-page">
        <div class="ac-error-code" aria-hidden="true">404</div>
        <span class="ac-error-emoji" aria-hidden="true">🎓</span>
        <h1><?php echo $safe($notFoundTitle); ?></h1>
        <p class="ac-muted"><?php echo $safe($notFoundMessage); ?></p>
        <div class="ac-error-actions">
            <?php if (theme_v4_link_available($siteUrl . '/courses')) : ?>
            <a href="<?php echo $safe(academy365_safe_url($siteUrl . '/courses', $siteUrl)); ?>" class="ac-btn ac-btn-primary"><?php echo $safe($notFoundCta); ?></a>
            <?php endif; ?>
            <a href="<?php echo $safe(academy365_safe_url($siteUrl . '/search', $siteUrl)); ?>" class="ac-btn ac-btn-ghost"><?php echo $safe($t('Suche öffnen', 'Open search')); ?></a>
            <a href="<?php echo $safe($siteUrl); ?>" class="ac-btn ac-btn-ghost"><?php echo $safe($t('Zur Startseite', 'Back to home')); ?></a>
        </div>
    </div>
</main>
