<?php
declare(strict_types=1);

/**
 * Feder – Seite nicht gefunden (404)
 *
 * @package Feder_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!headers_sent()) {
    http_response_code(404);
}

$recent = feder_get_posts(['limit' => 4]);
?>

<section class="fdr-notfound fdr-measure" aria-labelledby="fdr-notfound-title">
    <p class="fdr-notfound__code" aria-hidden="true">404</p>
    <p class="fdr-kicker"><?php echo feder_e(feder_t('Seite nicht gefunden', 'Page not found')); ?></p>
    <h1 class="fdr-archive-head__title" id="fdr-notfound-title"><?php echo feder_e(feder_t('Diese Seite ist wohl zwischen den Zeilen verloren gegangen.', 'This page seems to have gone missing between the lines.')); ?></h1>
    <p class="fdr-archive-head__text"><?php echo feder_e(feder_t('Vielleicht wurde der Text verschoben oder die Adresse hat sich geändert. Die Suche oder einer der neuesten Beiträge helfen weiter.', 'The text may have been moved or the address has changed. The search or one of the latest posts may help.')); ?></p>
    <?php feder_search_form('fdr-search-404', '', 'fdr-searchform--large'); ?>

    <?php if ($recent !== []) : ?>
        <h2 class="fdr-section-title"><?php echo feder_e(feder_t('Zuletzt erschienen', 'Recently published')); ?></h2>
        <ul class="fdr-simplelist">
            <?php foreach ($recent as $item) : ?>
                <li>
                    <a href="<?php echo feder_e(feder_post_link($item)); ?>"><?php echo feder_e((string) ($item->title ?? '')); ?></a>
                    <time datetime="<?php echo feder_e(feder_format_date($item->published_at ?? $item->created_at ?? '', 'iso')); ?>"><?php echo feder_e(feder_format_date($item->published_at ?? $item->created_at ?? '', 'short')); ?></time>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a class="fdr-button" href="<?php echo feder_e(feder_url('/')); ?>"><?php echo feder_e(feder_t('Zur Startseite', 'Back to home')); ?></a></p>
</section>
