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
    ['label' => 'Kontakt aufnehmen', 'url' => biz_href('/contact')],
    ['label' => 'Suche öffnen',      'url' => biz_href('/search')],
]);
?>

<section class="biz-page-hero">
    <div class="biz-container">
        <p class="biz-page-eyebrow">Fehler 404</p>
        <h1>Seite nicht gefunden</h1>
        <p>Die angeforderte Seite existiert nicht oder wurde verschoben.</p>
    </div>
</section>

<div class="biz-page-content">
    <div class="biz-container">
        <div class="biz-error-wrap">
            <p class="biz-error-icon" aria-hidden="true">🔍</p>
            <h2 class="biz-error-title">Ups – hier gibt es nichts zu sehen.</h2>
            <p class="biz-error-text">
                Möglicherweise wurde die Seite umbenannt oder gelöscht. Nutzen Sie die Navigation oder kehren Sie zur Startseite zurück.
            </p>
            <div class="biz-error-actions">
                <a href="<?php echo htmlspecialchars(biz_href('/'), ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-primary btn-biz-lg">Zur Startseite</a>
                <?php foreach ($bizNotFoundLinks as $bizLink) : ?>
                <a href="<?php echo htmlspecialchars($bizLink['url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-ghost btn-biz-lg"><?php echo htmlspecialchars($bizLink['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
