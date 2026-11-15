<?php
declare(strict_types=1);

/**
 * Feder – Statische Seite
 *
 * Datenvertrag (365CMS 4.0): $page (Array; content = gerendertes, sanitiertes HTML,
 * title, meta_description, featured_image, hide_title, updated_at/content_updated_at).
 * Der Core rendert auch Downloads und den Feed-Aggregator über diese Vorlage – dann mit
 * `$page['id'] = 0` und ggf. ohne Titel/Datum; alle Felder sind deshalb optional.
 *
 * @package Feder_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($page) || !is_array($page)) {
    require __DIR__ . '/404.php';
    return;
}

$title = trim((string) ($page['title'] ?? ''));
$content = (string) ($page['content'] ?? '');
$lead = trim((string) ($page['meta_description'] ?? $page['excerpt'] ?? ''));
$hideTitle = !empty($page['hide_title']);
$image = feder_media_url($page['featured_image'] ?? '');
$updated = (string) ($page['content_updated_at'] ?? $page['updated_at'] ?? '');
?>

<article class="fdr-page"<?php echo $title !== '' ? ' aria-label="' . feder_e($title) . '"' : ''; ?>>
    <?php if ($title !== '' && !$hideTitle) : ?>
        <header class="fdr-page__header fdr-measure">
            <h1 class="fdr-page__title"><?php echo feder_e($title); ?></h1>
            <?php if ($lead !== '') : ?>
                <p class="fdr-article__dek"><?php echo feder_e($lead); ?></p>
            <?php endif; ?>
            <?php if ($updated !== '' && feder_format_date($updated) !== '') : ?>
                <p class="fdr-meta">Zuletzt aktualisiert am <time datetime="<?php echo feder_e(feder_format_date($updated, 'iso')); ?>"><?php echo feder_e(feder_format_date($updated)); ?></time></p>
            <?php endif; ?>
        </header>
    <?php elseif ($title !== '') : ?>
        <h1 class="fdr-visually-hidden"><?php echo feder_e($title); ?></h1>
    <?php endif; ?>

    <?php if ($image !== '') : ?>
        <figure class="fdr-article__media fdr-wide">
            <?php echo feder_img($image, $title, ['lcp' => true, 'item' => $page, 'sizes' => '(max-width: 1160px) 100vw, 1120px'], 1600, 900); ?>
        </figure>
    <?php endif; ?>

    <div class="fdr-prose entry-content fdr-measure">
        <?php if (trim($content) !== '') : ?>
            <?php echo $content; // Vom Core gerendertes und sanitiertes HTML (Editor.js, Downloads, Feeds) ?>
        <?php else : ?>
            <p class="fdr-empty">Diese Seite hat noch keinen Inhalt.</p>
        <?php endif; ?>
    </div>
</article>
