<?php
declare(strict_types=1);

/**
 * Beitrags-Detailseite
 *
 * Datenvertrag (365CMS 3.4, ThemeRouter): $post (Objekt: title, content = bereits gerendertes, sanitiertes HTML, published_at, author_name, category_name, featured_image).
 */

if (!defined('ABSPATH')) {
    exit;
}

$safe    = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$siteUrl = rtrim((string) SITE_URL, '/');
$acLocale = theme_v4_locale($contentLocale ?? null);
$t = static fn(string $de, string $en): string => theme_v4_t($de, $en, $acLocale);
$safeUrl = static fn(string $url, string $fallback = ''): string => academy365_safe_url($url, $fallback);

$post = isset($post) && is_array($post) ? (object) $post : ($post ?? null);
$blogUrl = $siteUrl . '/blog';

$postTitle   = is_object($post) ? trim((string) ($post->title ?? '')) : '';
$postContent = is_object($post) ? (string) ($post->content ?? '') : '';
$postDate    = is_object($post) ? academy365_format_date($post->published_at ?? $post->created_at ?? '', $acLocale) : '';
$postAuthor  = is_object($post) ? trim((string) ($post->author_name ?? '')) : '';
$postCat     = is_object($post) ? trim((string) ($post->category_name ?? '')) : '';
$postImage   = is_object($post) ? $safeUrl((string) ($post->featured_image ?? '')) : '';
$metaParts   = array_values(array_filter([$postDate, $postAuthor, $postCat], static fn(string $v): bool => $v !== ''));
?>
<main id="main" class="ac-main-content ac-page-content" role="main">
    <div class="ac-container">
        <?php if (is_object($post)) : ?>
            <article class="ac-page-article">
                <?php if ($postTitle !== '') : ?>
                    <h1 class="ac-page-title"><?php echo $safe($postTitle); ?></h1>
                <?php endif; ?>
                <?php if ($metaParts !== []) : ?>
                    <p class="cms-post-meta"><?php echo $safe(implode(' · ', $metaParts)); ?></p>
                <?php endif; ?>
                <?php if ($postImage !== '') : ?>
                    <figure class="cms-post-image"><?php echo theme_v4_img($postImage, $postTitle, ['lcp' => true, 'item' => $post, 'sizes' => '(max-width: 900px) 100vw, 900px']); ?></figure>
                <?php endif; ?>
                <?php if (trim($postContent) !== '') : ?>
                    <div class="ac-page-body">
                        <?php echo $postContent; ?>
                    </div>
                <?php endif; ?>
                <p><a href="<?php echo $safe($blogUrl); ?>" class="ac-btn ac-btn-ghost">← <?php echo $safe($t('Alle Beiträge', 'All posts')); ?></a></p>
            </article>
        <?php else : ?>
            <div class="ac-empty-state">
                <p><?php echo $safe($t('Beitrag nicht gefunden.', 'Post not found.')); ?></p>
                <a href="<?php echo $safe($siteUrl . '/'); ?>" class="ac-btn ac-btn-primary"><?php echo $safe($t('Zur Startseite', 'Back to home')); ?></a>
            </div>
        <?php endif; ?>
    </div>
</main>
