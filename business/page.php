<?php
declare(strict_types=1);

/**
 * Business Theme – Generische Seite
 *
 * @package IT_Business_Theme
 * @var array $page
 */

if (!defined('ABSPATH')) {
    exit;
}

if (empty($page) || !is_array($page)) {
    if (!headers_sent()) {
        http_response_code(404);
    }
    require __DIR__ . '/404.php'; // Header/Footer rendert der Core bereits
    return;
}

// Auch Core-Module rendern über page.php (Downloads, Feeds: `id` 0, `content_updated_at`, sonst keine Felder).
$pageTitle   = (string) ($page['title']      ?? '');
$pageContent = (string) ($page['content']    ?? '');
$updatedAt   = (string) ($page['updated_at'] ?? ($page['content_updated_at'] ?? ''));
$isModule    = (int) ($page['id'] ?? 0) === 0;
?>

<section class="biz-page-hero">
    <div class="biz-container">
        <h1><?php echo htmlspecialchars(trim($pageTitle) !== '' ? $pageTitle : (string) \CMS\ThemeManager::instance()->getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></h1>
        <?php
        if (trim($updatedAt) !== '') :
            $ts = strtotime($updatedAt);
            if ($ts !== false) :
                ?>
                <p>Zuletzt aktualisiert: <?php echo htmlspecialchars(date('d.m.Y', $ts), ENT_QUOTES, 'UTF-8'); ?></p>
            <?php
            endif;
        endif;
        ?>
    </div>
</section>

<div class="biz-page-content">
    <div class="biz-container">
        <?php if (trim($pageContent) !== '') : ?>
            <div class="biz-prose<?php echo $isModule ? ' biz-prose--module' : ''; ?>">
                <?php
                // Vom Core gerendertes und bereinigtes HTML (Editor.js, Downloads, Feeds, Formulare) unverändert
                // ausgeben; der frühere Theme-Filter entfernte u. a. <picture>, <form>, <button>, <svg> und style.
                echo $pageContent;
                ?>
            </div>
        <?php else : ?>
            <p class="biz-prose-empty">Diese Seite hat noch keinen Inhalt.</p>
        <?php endif; ?>
    </div>
</div>
