<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Der Core übergibt die Seite als Array ($page); 'content' ist bereits gerendertes, sanitiertes HTML.
$page        = isset($page) && is_array($page) ? $page : [];
$pageTitle   = trim((string) ($page['title'] ?? ''));
$pageContent = (string) ($page['content'] ?? '');
$safe        = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$acLocale    = theme_v4_locale($contentLocale ?? null);
$t           = static fn(string $de, string $en): string => theme_v4_t($de, $en, $acLocale);
?>
<main id="main" class="ac-main-content ac-page-content" role="main">
    <div class="ac-container">
        <?php if ($page !== []) : ?>
            <article class="ac-page-article">
                <?php if ($pageTitle !== '') : ?>
                    <header class="ac-page-header">
                        <h1 class="ac-page-title"><?php echo $safe($pageTitle); ?></h1>
                    </header>
                <?php endif; ?>
                <?php if (trim($pageContent) !== '') : ?>
                    <div class="ac-page-body">
                        <?php echo $pageContent; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php else : ?>
            <div class="ac-empty-state">
                <p><?php echo $safe($t('Seite nicht gefunden.', 'Page not found.')); ?></p>
                <a href="<?php echo $safe(academy365_safe_url(rtrim((string) SITE_URL, '/') . '/', '/')); ?>" class="ac-btn ac-btn-primary"><?php echo $safe($t('Zurück zur Startseite', 'Back to home')); ?></a>
            </div>
        <?php endif; ?>
    </div>
</main>
