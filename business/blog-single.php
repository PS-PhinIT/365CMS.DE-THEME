<?php
declare(strict_types=1);

/**
 * Beitrags-Detailseite
 *
 * Datenvertrag (365CMS 4.x, ThemeRouter): $post (Objekt: title, content = bereits gerendertes, sanitiertes HTML, published_at, author_name, category_name, featured_image, optional focal_x/focal_y).
 */

if (!defined('ABSPATH')) {
    exit;
}

$safe    = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$siteUrl = rtrim((string) SITE_URL, '/');
$safeUrl = static function (string $url, string $fallback = '') use ($siteUrl): string {
    $url = trim($url);
    if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
        return $fallback;
    }
    if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
        return $siteUrl . $url;
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

    return in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) ? $url : $fallback;
};
$formatDate = static function (mixed $value): string {
    $ts = is_string($value) && $value !== '' ? strtotime($value) : false;

    return $ts !== false ? date('d.m.Y', $ts) : '';
};

$post = isset($post) && is_array($post) ? (object) $post : ($post ?? null);
$blogUrl = $siteUrl . '/blog';

$postTitle   = is_object($post) ? trim((string) ($post->title ?? '')) : '';
$postContent = is_object($post) ? (string) ($post->content ?? '') : '';
$postDate    = is_object($post) ? $formatDate($post->published_at ?? $post->created_at ?? '') : '';
$postAuthor  = is_object($post) ? trim((string) ($post->author_name ?? '')) : '';
$postCat     = is_object($post) ? trim((string) ($post->category_name ?? '')) : '';
$postImage   = is_object($post) ? $safeUrl((string) ($post->featured_image ?? '')) : '';
$metaParts   = array_values(array_filter([$postDate, $postAuthor, $postCat], static fn(string $v): bool => $v !== ''));
?>
<?php if (is_object($post)) : ?>
<section class="biz-page-hero">
    <div class="biz-container">
        <?php if ($postTitle !== '') : ?>
            <h1><?php echo $safe($postTitle); ?></h1>
        <?php endif; ?>
        <?php if ($metaParts !== []) : ?>
            <p><?php echo $safe(implode(' · ', $metaParts)); ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="biz-page-content">
    <div class="biz-container">
        <?php if ($postImage !== '') : ?>
            <?php // LCP-Bild: width/height, fetchpriority="high" statt lazy, Fokus-Punkt (4.1.0) ?>
            <figure class="cms-post-image"><?php echo theme_v4_img($postImage, $postTitle, ['lcp' => true, 'item' => $post, 'sizes' => '(max-width: 1200px) 100vw, 1200px']); ?></figure>
        <?php endif; ?>
        <?php if (trim($postContent) !== '') : ?>
            <div class="biz-prose">
                <?php echo $postContent; // Core-HTML unverändert (bereits bereinigt) ?>
            </div>
        <?php endif; ?>
        <p><a href="<?php echo $safe($blogUrl); ?>" class="btn-biz btn-biz-ghost"><span aria-hidden="true">←</span> Alle Beiträge</a></p>
    </div>
</div>
<?php else : ?>
<div class="biz-page-content">
    <div class="biz-container">
        <h1 class="biz-error-title">Beitrag nicht gefunden</h1>
        <a href="<?php echo $safe($siteUrl . '/'); ?>" class="btn-biz btn-biz-primary">Zur Startseite</a>
    </div>
</div>
<?php endif; ?>
