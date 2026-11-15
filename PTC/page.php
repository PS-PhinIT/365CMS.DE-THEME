<?php
/**
 * PTC Theme – Generische Seite
 *
 * @package PTC_Theme
 * @var array $page
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (empty($page) || !is_array($page)) {
    if (!headers_sent()) {
        http_response_code(404); // der Core setzt den Status meist schon vor header.php
    }
    require __DIR__ . '/404.php'; // Header/Footer rendert der Core bereits
    return;
}

/* ─── Customizer-Einstellungen ─── */
$showTitle      = filter_var(ptc_customizer_get('blog', 'page_show_title',          '1'), FILTER_VALIDATE_BOOLEAN);
$showUpdated    = filter_var(ptc_customizer_get('blog', 'page_show_updated',        '1'), FILTER_VALIDATE_BOOLEAN);
$showFeatured   = filter_var(ptc_customizer_get('blog', 'page_show_featured_image', '1'), FILTER_VALIDATE_BOOLEAN);

// Auch Core-Module rendern über page.php (Downloads, Feeds: `id` 0, ohne `updated_at`/`featured_image`).
$pageTitle      = trim((string) ($page['title'] ?? ''));
$pageContent    = (string) ($page['content'] ?? '');
$updatedAt      = ptc_format_date($page['updated_at'] ?? ($page['content_updated_at'] ?? ''));
$featuredImage  = ptc_safe_url((string) ($page['featured_image'] ?? ''));
$isModulePage   = (int) ($page['id'] ?? 0) === 0; // Core-Modul: volle Inhaltsbreite statt Lesespalte
?>

<?php if ($showTitle && $pageTitle !== '') : ?>
<section class="ptc-page-hero">
    <div class="ptc-container">
        <h1><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <?php if ($showUpdated && $updatedAt !== '') : ?>
            <p>Zuletzt aktualisiert: <?php echo htmlspecialchars($updatedAt, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
    </div>
</section>
<?php elseif ($pageTitle !== '') : ?>
<h1 class="theme-v4-sr-only"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
<?php endif; ?>

<?php if ($showFeatured && $featuredImage !== '' && !str_starts_with($featuredImage, '#')) : ?>
<div class="ptc-page-featured">
    <div class="ptc-container">
        <?php echo theme_v4_img($featuredImage, $pageTitle, ['lcp' => true, 'item' => $page, 'sizes' => '(max-width: 1240px) 100vw, 1240px']); ?>
    </div>
</div>
<?php endif; ?>

<div class="ptc-page-content">
    <div class="ptc-container">
        <?php if (trim($pageContent) !== '') : ?>
            <div class="ptc-prose<?php echo $isModulePage ? ' ptc-prose--module' : ' ptc-prose--constrained'; ?> sun-editor-editable">
                <?php
                // Vom Core gerendertes und bereinigtes HTML (Editor.js, Downloads, Feeds, Formulare) – unverändert
                // ausgeben. Das frühere strip_tags() entfernte u. a. <picture>, <form>, <button> und <svg>.
                echo $pageContent;
                ?>
            </div>
        <?php else : ?>
            <p class="ptc-text-muted">Diese Seite hat noch keinen Inhalt.</p>
        <?php endif; ?>
    </div>
</div>
