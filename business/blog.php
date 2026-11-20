<?php
declare(strict_types=1);

/**
 * Beitragsübersicht (Blog, Kategorie, Schlagwort)
 *
 * Datenvertrag (365CMS 4.x, ThemeRouter): $posts (Objekte), $currentPage, $totalPages; bei Archiven zusätzlich $category bzw. $tag.
 */

if (!defined('ABSPATH')) {
    exit;
}

$safe    = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$siteUrl = rtrim((string) SITE_URL, '/');
$bizLocale = theme_v4_locale($contentLocale ?? null);
$t = static fn(string $de, string $en): string => theme_v4_t($de, $en, $bizLocale);
$safeUrl = static fn(string $url, string $fallback = ''): string => biz_safe_url($url, $fallback);

$posts       = isset($posts) && is_array($posts) ? $posts : [];
$currentPage = max(1, (int) ($currentPage ?? 1));
$totalPages  = max(1, (int) ($totalPages ?? 1));
$archiveTitle = $t('Beiträge', 'Posts');
if (isset($category) && is_array($category) && trim((string) ($category['name'] ?? '')) !== '') {
    $archiveTitle = (string) $category['name'];
} elseif (isset($tag) && is_array($tag) && trim((string) ($tag['name'] ?? '')) !== '') {
    $archiveTitle = '#' . (string) $tag['name'];
}
// Normalisierter Pfad statt REQUEST_URI: `/\host` o. Ä. darf nicht als Link-Basis zurückgespiegelt werden.
$requestPath = (string) (parse_url(theme_v4_site_url(theme_v4_request_path()), PHP_URL_PATH) ?? '/');
$pageUrl = static function (int $number) use ($requestPath): string {
    $params = array_filter($_GET, 'is_scalar');
    unset($params['p']);
    if ($number > 1) {
        $params['p'] = $number;
    }
    $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    return $requestPath . ($queryString !== '' ? '?' . $queryString : '');
};
?>
<section class="biz-page-hero">
    <div class="biz-container">
        <h1><?php echo $safe($archiveTitle); ?></h1>
    </div>
</section>

<div class="biz-page-content">
    <div class="biz-container">
        <?php if ($posts !== []) : ?>
        <div class="biz-grid">
            <?php foreach ($posts as $item) :
                $item    = is_array($item) ? (object) $item : $item;
                $itemUrl = $safeUrl(theme_post_url($item), $siteUrl . '/blog');
                $itemTitle = (string) ($item->title ?? '');
                $itemText  = trim(strip_tags((string) ($item->excerpt ?? '')));
                $itemMeta  = biz_format_date($item->published_at ?? $item->created_at ?? '', $bizLocale);
            ?>
                <article class="biz-card">
                    <?php if ($itemMeta !== '') : ?>
                        <p class="biz-meta"><?php echo $safe($itemMeta); ?></p>
                    <?php endif; ?>
                    <h2 class="biz-card-title"><a href="<?php echo $safe($itemUrl); ?>"><?php echo $safe($itemTitle); ?></a></h2>
                    <?php if ($itemText !== '') : ?>
                        <p class="biz-card-text"><?php echo $safe(function_exists('mb_substr') ? mb_substr($itemText, 0, 220) : substr($itemText, 0, 220)); ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1) : ?>
            <nav class="cms-pagination" aria-label="Seitennavigation">
                <?php if ($currentPage > 1) : ?>
                    <a href="<?php echo $safe($pageUrl($currentPage - 1)); ?>" class="btn-biz btn-biz-ghost" rel="prev"><span aria-hidden="true">←</span> <?php echo $safe($t('Zurück', 'Previous')); ?></a>
                <?php endif; ?>
                <span aria-current="page"><?php echo $safe($t('Seite', 'Page')); ?> <?php echo $currentPage; ?> <?php echo $safe($t('von', 'of')); ?> <?php echo $totalPages; ?></span>
                <?php if ($currentPage < $totalPages) : ?>
                    <a href="<?php echo $safe($pageUrl($currentPage + 1)); ?>" class="btn-biz btn-biz-ghost" rel="next"><?php echo $safe($t('Weiter', 'Next')); ?> <span aria-hidden="true">→</span></a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
        <?php else : ?>
            <p class="biz-prose-empty"><?php echo $safe($t('Noch keine Beiträge vorhanden.', 'No posts yet.')); ?></p>
        <?php endif; ?>
    </div>
</div>
