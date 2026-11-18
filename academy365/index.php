<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Fallback-Template (z. B. Blog, Kategorie, Schlagwort): Der Core übergibt $posts (Objekte)
// sowie $currentPage/$totalPages; bei Archiven zusätzlich $category bzw. $tag.
$posts       = isset($posts) && is_array($posts) ? $posts : [];
$currentPage = max(1, (int) ($currentPage ?? 1));
$totalPages  = max(1, (int) ($totalPages ?? 1));
$safe        = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$acLocale    = theme_v4_locale($contentLocale ?? null);
$t           = static fn(string $de, string $en): string => theme_v4_t($de, $en, $acLocale);
$siteUrl     = academy365_safe_url(rtrim((string) SITE_URL, '/') . '/', '/');

$archiveTitle = $t('Beiträge', 'Posts');
if (isset($category) && is_array($category) && trim((string) ($category['name'] ?? '')) !== '') {
    $archiveTitle = (string) $category['name'];
} elseif (isset($tag) && is_array($tag) && trim((string) ($tag['name'] ?? '')) !== '') {
    $archiveTitle = '#' . (string) $tag['name'];
}

// Normalisierter Pfad statt REQUEST_URI: `/\host` o. Ä. darf nicht als Link-Basis zurückgespiegelt werden.
$requestPath = (string) (parse_url(theme_v4_site_url(theme_v4_request_path()), PHP_URL_PATH) ?? '/');
$pageUrl = static function (int $number) use ($requestPath): string {
    $query = $_GET;
    unset($query['p']);
    if ($number > 1) {
        $query['p'] = $number;
    }
    $queryString = http_build_query(array_filter($query, 'is_scalar'), '', '&', PHP_QUERY_RFC3986);

    return $requestPath . ($queryString !== '' ? '?' . $queryString : '');
};
?>
<main id="main" class="ac-main-content ac-archive-content" role="main">
    <div class="ac-container">
        <header class="ac-archive-header">
            <h1 class="ac-archive-title"><?php echo $safe($archiveTitle); ?></h1>
        </header>
        <?php if ($posts !== []) : ?>
            <div class="ac-courses-grid">
                <?php foreach ($posts as $post) :
                    $post      = is_array($post) ? (object) $post : $post;
                    $postUrl   = academy365_safe_url(theme_post_url($post), $siteUrl);
                    $postTitle = (string) ($post->title ?? '');
                    $excerpt   = trim(strip_tags((string) ($post->excerpt ?? '')));
                ?>
                    <article class="ac-card">
                        <div class="ac-card-thumb"></div>
                        <div class="ac-card-body">
                            <h2 class="ac-card-title">
                                <a href="<?php echo $safe($postUrl); ?>"><?php echo $safe($postTitle); ?></a>
                            </h2>
                            <?php if ($excerpt !== '') : ?>
                                <div class="ac-card-excerpt"><?php echo $safe($excerpt); ?></div>
                            <?php endif; ?>
                            <a href="<?php echo $safe($postUrl); ?>" class="ac-btn ac-btn-secondary ac-btn-sm"><?php echo $safe($t('Weiterlesen', 'Read more')); ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($totalPages > 1) : ?>
                <nav class="ac-pagination" aria-label="<?php echo $safe($t('Seitennavigation', 'Pagination')); ?>">
                    <?php if ($currentPage > 1) : ?>
                        <a href="<?php echo $safe($pageUrl($currentPage - 1)); ?>" class="ac-btn ac-btn-ghost" rel="prev">← <?php echo $safe($t('Zurück', 'Previous')); ?></a>
                    <?php endif; ?>
                    <span><?php echo $safe($t('Seite', 'Page')); ?> <?php echo $currentPage; ?> <?php echo $safe($t('von', 'of')); ?> <?php echo $totalPages; ?></span>
                    <?php if ($currentPage < $totalPages) : ?>
                        <a href="<?php echo $safe($pageUrl($currentPage + 1)); ?>" class="ac-btn ac-btn-ghost" rel="next"><?php echo $safe($t('Weiter', 'Next')); ?> →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else : ?>
            <div class="ac-empty-state">
                <span class="ac-empty-emoji" aria-hidden="true">📚</span>
                <p><?php echo $safe($t('Noch keine Beiträge vorhanden.', 'No posts yet.')); ?></p>
                <a href="<?php echo $safe($siteUrl); ?>" class="ac-btn ac-btn-primary"><?php echo $safe($t('Zurück zur Startseite', 'Back to home')); ?></a>
            </div>
        <?php endif; ?>
    </div>
</main>
