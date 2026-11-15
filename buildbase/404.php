<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}


$safe     = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$siteUrl  = rtrim(buildbase_safe_url((string) SITE_URL, '/'), '/');
$siteUrl  = $siteUrl !== '' ? $siteUrl : '/';
$bbLocale = theme_v4_locale($contentLocale ?? null);

// Standard-CTAs nur zu vorhandenen Zielen (Seite „handwerker“ ist optional, Suche ist eine Core-Route).
$bbNotFoundLinks = theme_v4_filter_links([
    ['label' => 'Handwerker finden', 'url' => rtrim($siteUrl, '/') . '/handwerker'],
    ['label' => theme_v4_t('Suche öffnen', 'Open search', $bbLocale), 'url' => rtrim($siteUrl, '/') . '/search'],
]);
?>
<main id="main" class="bb-main bb-error-screen" role="main">
    <div class="bb-card bb-error-card">
        <p class="bb-error-code" aria-hidden="true">404</p>
        <h1 class="bb-error-title"><?php echo $safe(theme_v4_t('Seite nicht gefunden', 'Page not found', $bbLocale)); ?></h1>
        <p class="bb-error-text"><?php echo $safe(theme_v4_t('Die gesuchte Seite existiert nicht oder wurde verschoben.', 'The requested page does not exist or has been moved.', $bbLocale)); ?></p>
        <div class="bb-error-actions">
            <a href="<?php echo $safe($siteUrl); ?>" class="bb-btn bb-btn-primary"><?php echo $safe(theme_v4_t('Zur Startseite', 'Back to home', $bbLocale)); ?></a>
            <?php foreach ($bbNotFoundLinks as $bbLink) : ?>
            <a href="<?php echo $safe(buildbase_safe_url($bbLink['url'], $siteUrl)); ?>" class="bb-btn bb-btn-outline"><?php echo $safe($bbLink['label']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</main>
