<?php
/**
 * CMS Newspaper Theme – Generic static page (CMS Page record)
 *
 * Datenvertrag (365CMS 4.0): $page (Array; content = vom Core gerendertes, sanitiertes HTML). Downloads und
 * Feeds rendert der Core ebenfalls hierüber ($page['id'] = 0, ohne Datum/Bild) – alle Felder optional.
 *
 * @package CmsNewspaper_Theme
 * @var array $page
 */

if (!defined('ABSPATH')) {
    exit;
}

if (empty($page) || !is_array($page)) {
    require __DIR__ . '/404.php';
    return;
}

$pageTitle   = trim((string) ($page['title'] ?? ''));
$pageContent = (string) ($page['content'] ?? '');
$updatedAt   = (string) ($page['updated_at'] ?? '');

$safe = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
?>

<section class="news-page-hero"<?php echo $pageTitle !== '' ? ' aria-label="' . $safe($pageTitle) . '"' : ''; ?>>
    <div class="news-container">
        <span class="news-kicker">Seite</span>
        <?php if ($pageTitle !== '') : ?>
            <h1><?php echo $safe($pageTitle); ?></h1>
        <?php endif; ?>
        <?php
        if (trim($updatedAt) !== '') :
            $ts = strtotime($updatedAt);
            if ($ts !== false) :
                ?>
                <p>Zuletzt aktualisiert: <?php echo $safe(date('d.m.Y', $ts)); ?></p>
            <?php
            endif;
        endif;
        ?>
    </div>
</section>

<div class="news-page-content">
    <div class="news-container">
        <?php if (trim($pageContent) !== '') : ?>
            <div class="news-prose">
                <?php
                // Vom Core gerendertes, sanitiertes HTML unverändert ausgeben. Bisher filterte strip_tags() u. a.
                // <form>, <button>, <picture>, <svg> und <nav> heraus und zerlegte damit Downloads, Feeds und Formulare.
                echo $pageContent;
                ?>
            </div>
        <?php else : ?>
            <p class="news-prose-empty">Diese Seite hat noch keinen Inhalt.</p>
        <?php endif; ?>
    </div>
</div>
