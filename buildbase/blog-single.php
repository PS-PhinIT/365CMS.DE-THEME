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
$bbLocale = theme_v4_locale($contentLocale ?? null);
$t = static fn(string $de, string $en): string => theme_v4_t($de, $en, $bbLocale);
$safeUrl = static fn(string $url, string $fallback = ''): string => buildbase_safe_url($url, $fallback);

$post = isset($post) && is_array($post) ? (object) $post : ($post ?? null);
$blogUrl = $siteUrl . '/blog';

$postTitle   = is_object($post) ? trim((string) ($post->title ?? '')) : '';
$postContent = is_object($post) ? (string) ($post->content ?? '') : '';
$postDate    = is_object($post) ? buildbase_format_date($post->published_at ?? $post->created_at ?? '', $bbLocale) : '';
$postAuthor  = is_object($post) ? trim((string) ($post->author_name ?? '')) : '';
$postCat     = is_object($post) ? trim((string) ($post->category_name ?? '')) : '';
$postImage   = is_object($post) ? $safeUrl((string) ($post->featured_image ?? '')) : '';
$metaParts   = array_values(array_filter([$postDate, $postAuthor, $postCat], static fn(string $v): bool => $v !== ''));
?>
<main id="main" class="bb-main bb-page-section" role="main">
    <div class="bb-container bb-container--narrow">
        <?php if (is_object($post)) : ?>
            <article class="bb-card bb-page-article">
                <?php if ($postTitle !== '') : ?>
                    <h1 class="bb-page-title"><?php echo $safe($postTitle); ?></h1>
                <?php endif; ?>
                <?php if ($metaParts !== []) : ?>
                    <p class="cms-post-meta"><?php echo $safe(implode(' · ', $metaParts)); ?></p>
                <?php endif; ?>
                <?php if ($postImage !== '') : ?>
                    <?php // LCP-Bild: width/height, fetchpriority="high" statt lazy, Fokus-Punkt (4.1.0) ?>
                    <figure class="cms-post-image"><?php echo theme_v4_img($postImage, $postTitle, ['lcp' => true, 'item' => $post, 'sizes' => '(max-width: 860px) 100vw, 860px']); ?></figure>
                <?php endif; ?>
                <?php if (trim($postContent) !== '') : ?>
                    <div class="prose">
                        <?php echo $postContent; // Core-HTML unverändert (bereits bereinigt) ?>
                    </div>
                <?php endif; ?>
                <p><a href="<?php echo $safe($blogUrl); ?>" class="bb-btn bb-btn-outline"><span aria-hidden="true">←</span> <?php echo $safe($t('Alle Beiträge', 'All posts')); ?></a></p>
            </article>
        <?php else : ?>
            <div class="bb-empty">
                <h1 class="bb-page-title"><?php echo $safe($t('Beitrag nicht gefunden', 'Post not found')); ?></h1>
                <a href="<?php echo $safe($siteUrl . '/'); ?>" class="bb-btn bb-btn-primary"><?php echo $safe($t('Zur Startseite', 'Back to home')); ?></a>
            </div>
        <?php endif; ?>
    </div>
</main>
