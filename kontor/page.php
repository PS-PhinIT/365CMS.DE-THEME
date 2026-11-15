<?php
declare(strict_types=1);

/**
 * Kontor – Statische Seite (z. B. Über uns, Leistungen, Kontakt, Impressum)
 *
 * Datenvertrag (365CMS 4.0): $page (Array; content = gerendertes, sanitiertes HTML). Der Core rendert auch
 * Downloads und den Feed-Aggregator über diese Vorlage – dann mit `$page['id'] = 0` und ggf. ohne Titel.
 *
 * @package Kontor_Theme
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
$image = kontor_media_url($page['featured_image'] ?? '');
$slug = strtolower(trim((string) ($page['slug'] ?? '')));
$isLegal = in_array($slug, ['impressum', 'datenschutz', 'datenschutzerklaerung', 'agb', 'imprint', 'privacy'], true);
// Core-Module (Downloads, Feeds) bringen eigene Navigation mit – dort keine Kontaktkarte daneben.
$isModule = (int) ($page['id'] ?? 0) <= 0;
$showSidebar = kontor_flag('layout', 'page_sidebar', true) && !$isLegal && !$isModule;
?>

<?php if ($title !== '' && !$hideTitle) : ?>
    <header class="kt-pagehero">
        <div class="kt-container">
            <nav class="kt-breadcrumb" aria-label="Brotkrumen">
                <ol>
                    <li><a href="<?php echo kontor_e(kontor_url('/')); ?>">Start</a></li>
                    <li><span aria-current="page"><?php echo kontor_e($title); ?></span></li>
                </ol>
            </nav>
            <h1 class="kt-pagehero__title"><?php echo kontor_e($title); ?></h1>
            <?php if ($lead !== '') : ?>
                <p class="kt-pagehero__text"><?php echo kontor_e($lead); ?></p>
            <?php endif; ?>
        </div>
    </header>
<?php elseif ($title !== '') : ?>
    <h1 class="kt-visually-hidden"><?php echo kontor_e($title); ?></h1>
<?php endif; ?>

<div class="kt-container kt-content<?php echo $showSidebar ? ' kt-content--sidebar' : ' kt-content--narrow'; ?>">
    <article class="kt-content__main"<?php echo $title !== '' ? ' aria-label="' . kontor_e($title) . '"' : ''; ?>>
        <?php if ($image !== '') : ?>
            <figure class="kt-content__media">
                <?php echo kontor_img($image, $title, ['lcp' => true, 'item' => $page], 1200, 675); ?>
            </figure>
        <?php endif; ?>
        <div class="kt-prose entry-content">
            <?php if (trim($content) !== '') : ?>
                <?php echo $content; // Vom Core gerendertes und sanitiertes HTML (Editor.js, Downloads, Feeds) ?>
            <?php else : ?>
                <p class="kt-empty">Diese Seite hat noch keinen Inhalt.</p>
            <?php endif; ?>
        </div>
    </article>

    <?php if ($showSidebar) : ?>
        <div class="kt-content__aside">
            <div class="kt-sticky">
                <?php kontor_contact_card(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
