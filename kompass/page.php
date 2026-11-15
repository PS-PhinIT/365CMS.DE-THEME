<?php
declare(strict_types=1);

/**
 * Kompass – Informationsseite
 *
 * Seitenkopf mit Brotkrumen → Inhaltsverzeichnis „Auf dieser Seite“ (aus H2/H3) →
 * Inhalt → Stand, Drucken, Link kopieren, PDF → „War diese Seite hilfreich?“.
 *
 * Datenvertrag (365CMS 4.0): $page (Array; content = gerendertes, sanitiertes HTML). Der Core rendert auch
 * Downloads und den Feed-Aggregator über diese Vorlage – dann mit $page['id'] = 0 und ggf. ohne Titel.
 *
 * @package Kompass_Theme
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
$image = kompass_media_url($page['featured_image'] ?? '');
$updated = (string) ($page['updated_at'] ?? $page['created_at'] ?? '');

$toc = ['html' => $content, 'items' => []];
if (kompass_flag('layout', 'show_toc', true)) {
    $toc = kompass_toc($content, kompass_int('layout', 'toc_min_headings', 3, 2, 8));
}
$hasToc = $toc['items'] !== [];
$content = $toc['html'];
?>

<header class="kp-pagehead<?php echo $hideTitle || $title === '' ? ' kp-pagehead--compact' : ''; ?>">
    <div class="kp-container">
        <nav class="kp-breadcrumb" aria-label="Brotkrumen">
            <ol>
                <li><a href="<?php echo kompass_e(kompass_url('/')); ?>">Start</a></li>
                <?php if ($title !== '') : ?>
                    <li><span aria-current="page"><?php echo kompass_e($title); ?></span></li>
                <?php endif; ?>
            </ol>
        </nav>
        <?php if ($title !== '' && !$hideTitle) : ?>
            <h1 class="kp-pagehead__title"><?php echo kompass_e($title); ?></h1>
            <?php if ($lead !== '') : ?>
                <p class="kp-pagehead__lead"><?php echo kompass_e($lead); ?></p>
            <?php endif; ?>
        <?php elseif ($title !== '') : ?>
            <h1 class="kp-visually-hidden"><?php echo kompass_e($title); ?></h1>
        <?php endif; ?>
    </div>
</header>

<div class="kp-container kp-layout<?php echo $hasToc ? ' kp-layout--toc' : ''; ?>">
    <?php if ($hasToc) : ?>
        <div class="kp-layout__toc">
            <div class="kp-sticky"><?php kompass_render_toc($toc['items']); ?></div>
        </div>
    <?php endif; ?>

    <article class="kp-layout__main"<?php echo $title !== '' ? ' aria-label="' . kompass_e($title) . '"' : ''; ?>>
        <?php if ($image !== '') : ?>
            <figure class="kp-media">
                <?php echo kompass_img($image, '', ['lcp' => true, 'item' => $page, 'sizes' => '(max-width: 860px) 100vw, 48rem'], 1200, 675); ?>
            </figure>
        <?php endif; ?>

        <div class="kp-prose entry-content<?php echo $hasToc ? ' kp-prose--own-toc' : ''; ?>">
            <?php if (trim($content) !== '') : ?>
                <?php echo $content; // Vom Core gerendertes und sanitiertes HTML (Editor.js, Downloads, Feeds) ?>
            <?php else : ?>
                <p class="kp-empty">Diese Seite hat noch keinen Inhalt.</p>
            <?php endif; ?>
        </div>

        <?php // PDF-Ausgabe nur für echte Seiten (Downloads/Feeds rendert der Core mit $page['id'] = 0). ?>
        <?php kompass_page_tools($updated, (int) ($page['id'] ?? 0) > 0); ?>
        <?php kompass_feedback($title !== '' ? $title : kompass_site_title()); ?>
    </article>
</div>
