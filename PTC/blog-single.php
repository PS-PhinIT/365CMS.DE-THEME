<?php
/**
 * PTC Theme – Einzelner Blogbeitrag
 *
 * Vom Router bereitgestellte Variable:
 *   $post – stdObject mit Post-Daten
 *
 * @package PTC_Theme
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (empty($post)) {
    // Header ist bereits gesendet (der Core rendert header.php vorher) – kein Location-Header mehr möglich.
    if (!headers_sent()) {
        http_response_code(404);
    }
    require __DIR__ . '/404.php';
    return;
}
$post = is_array($post) ? (object) $post : $post;

$siteUrl = ptc_site_url();

/* ─── Customizer-Einstellungen ─── */
$showFeatured    = ptc_customizer_get('blog', 'blog_single_show_featured',     '1');
$showAuthor      = ptc_customizer_get('blog', 'blog_single_show_author',       '1');
$showDate        = ptc_customizer_get('blog', 'blog_single_show_date',         '1');
$showTags        = ptc_customizer_get('blog', 'blog_single_show_tags',         '1');
$showRelated     = ptc_customizer_get('blog', 'blog_single_show_related',      '1');
$showReadingTime = ptc_customizer_get('blog', 'blog_single_show_reading_time', '1');
$maxWidth        = (int) ptc_customizer_get('blog', 'blog_single_max_width',   '820');

/* ─── Post-Daten aufbereiten ─── */
$pTitleRaw = (string) ($post->title ?? '');
$pTitle    = htmlspecialchars($pTitleRaw, ENT_QUOTES, 'UTF-8');
$pContent  = (string) ($post->content ?? '');
$pExcerpt  = htmlspecialchars(strip_tags((string) ($post->excerpt ?? '')), ENT_QUOTES, 'UTF-8');
$pDateLong = ptc_format_date($post->published_at ?? '', 'd.m.Y');
$pDateIso  = htmlspecialchars(ptc_format_date($post->published_at ?? '', 'c'), ENT_QUOTES, 'UTF-8');
$pAuthorRaw = (string) ($post->author_name ?? 'Redaktion');
$pAuthor   = htmlspecialchars($pAuthorRaw, ENT_QUOTES, 'UTF-8');
$pAuthIni  = htmlspecialchars(mb_strtoupper(mb_substr($pAuthorRaw, 0, 2)), ENT_QUOTES, 'UTF-8');
$pCat      = htmlspecialchars((string) ($post->category_name ?? ''), ENT_QUOTES, 'UTF-8');
$pCatSlug  = (string) ($post->category_slug ?? '');
$pViews    = (int) ($post->views ?? 0);
$pImage    = ptc_safe_url((string) ($post->featured_image ?? ''));
$pImage    = str_starts_with($pImage, '#') ? '' : $pImage;
$pTags     = $post->tags ?? [];
if (is_string($pTags)) {
    $pTags = array_filter(array_map('trim', explode(',', $pTags)));
}
$pTags     = is_array($pTags) ? array_values(array_filter($pTags, 'is_string')) : [];

/* ─── Lesezeit ─── */
$wordCount = str_word_count(strip_tags($pContent));
$readMins  = max(1, (int) ceil($wordCount / 200));

/* ─── Verwandte Beiträge ─── */
$relatedPosts = [];
if ($showRelated) {
    try {
        $db     = \CMS\Database::instance();
        $prefix = $db->getPrefix();
        $catId  = (int) ($post->category_id ?? 0);
        $postId = (int) ($post->id ?? 0);
        if ($catId > 0) {
            // Ein Query mit JOIN (keine N+1-Schleife), nur veröffentlichte Beiträge (4.1.0 kennt `pending`).
            $stmt = $db->execute(
                "SELECT p.id, p.title, p.slug, p.excerpt, p.published_at, p.featured_image,
                        c.name AS category_name
                 FROM {$prefix}posts p
                 LEFT JOIN {$prefix}post_categories c ON c.id = p.category_id
                 WHERE p.category_id = ? AND p.id != ? AND p.status = 'published'
                 ORDER BY p.published_at DESC
                 LIMIT 3",
                [$catId, $postId]
            );
            $relatedPosts = $stmt->fetchAll() ?: [];
        }
    } catch (\Throwable $e) {
        // Keine verwandten Beiträge bei Fehler
    }
}
?>

<!-- ── Breadcrumb ── -->
<nav class="ptc-breadcrumb" aria-label="Breadcrumb">
    <div class="ptc-container">
        <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/">Startseite</a>
        <span class="ptc-breadcrumb__sep" aria-hidden="true">›</span>
        <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/blog">Blog</a>
        <?php if ($pCat && $pCatSlug !== '') : ?>
            <span class="ptc-breadcrumb__sep" aria-hidden="true">›</span>
            <a href="<?php echo htmlspecialchars(ptc_archive_url('category', $pCatSlug), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $pCat; ?></a>
        <?php endif; ?>
        <span class="ptc-breadcrumb__sep" aria-hidden="true">›</span>
        <span class="ptc-breadcrumb__current" aria-current="page"><?php echo $pTitle; ?></span>
    </div>
</nav>

<!-- ── Article ── -->
<article class="ptc-article ptc-prose--constrained">
    <div class="ptc-container">

        <!-- Article Header -->
        <header class="ptc-article__header">
            <?php if ($pCat && $pCatSlug !== '') : ?>
                <a href="<?php echo htmlspecialchars(ptc_archive_url('category', $pCatSlug), ENT_QUOTES, 'UTF-8'); ?>"
                   class="ptc-article__category"><?php echo $pCat; ?></a>
            <?php endif; ?>

            <h1 class="ptc-article__title"><?php echo $pTitle; ?></h1>

            <?php if ($pExcerpt) : ?>
                <p class="ptc-article__intro"><?php echo $pExcerpt; ?></p>
            <?php endif; ?>

            <div class="ptc-article__meta">
                <?php if ($showAuthor) : ?>
                    <div class="ptc-article__author">
                        <span class="ptc-article__avatar" aria-hidden="true"><?php echo $pAuthIni; ?></span>
                        <span><?php echo $pAuthor; ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($showDate && $pDateLong) : ?>
                    <time datetime="<?php echo $pDateIso; ?>"><span aria-hidden="true">📅</span> <?php echo htmlspecialchars($pDateLong, ENT_QUOTES, 'UTF-8'); ?></time>
                <?php endif; ?>
                <?php if ($showReadingTime) : ?>
                    <span><span aria-hidden="true">📖</span> <?php echo $readMins; ?> Min. Lesezeit</span>
                <?php endif; ?>
                <?php if ($pViews > 0) : ?>
                    <span><span aria-hidden="true">👁️</span> <?php echo number_format($pViews, 0, ',', '.'); ?> Aufrufe</span>
                <?php endif; ?>
            </div>
        </header>

        <!-- Featured Image -->
        <?php if ($showFeatured && $pImage) : ?>
        <figure class="ptc-article__featured">
            <?php // LCP-Bild: width/height, fetchpriority="high" statt lazy, Fokus-Punkt (4.1.0) ?>
            <?php echo theme_v4_img($pImage, $pTitleRaw, ['lcp' => true, 'item' => $post, 'sizes' => '(max-width: 900px) 100vw, 900px']); ?>
        </figure>
        <?php endif; ?>

        <!-- Article Content -->
        <div class="ptc-article__content ptc-prose sun-editor-editable">
            <?php
            // Vom Core gerendertes und bereinigtes HTML (Editor.js inkl. <picture>, Formular- und Download-Blöcke)
            // unverändert ausgeben; das frühere strip_tags() zerstörte Blöcke mit <picture>, <form>, <button>, <svg>.
            echo $pContent;
            ?>
        </div>

        <!-- Tags -->
        <?php if ($showTags && !empty($pTags)) : ?>
        <div class="ptc-article__tags">
            <span><span aria-hidden="true">🏷️</span> Tags:</span>
            <?php foreach ($pTags as $tag) :
                $tEsc = htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');
            ?>
            <a href="<?php echo htmlspecialchars(ptc_archive_url('tag', $tag), ENT_QUOTES, 'UTF-8'); ?>"
               class="ptc-tag"><?php echo $tEsc; ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Share / Back -->
        <div class="ptc-article__actions">
            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/blog"
               class="ptc-btn ptc-btn--outline">← Zurück zum Blog</a>
        </div>

    </div>
</article>

<!-- ── Verwandte Beiträge ── -->
<?php if ($showRelated && !empty($relatedPosts)) : ?>
<section class="ptc-related">
    <div class="ptc-container">
        <h2><span aria-hidden="true">📌</span> Ähnliche Beiträge</h2>
        <div class="ptc-related__grid">
            <?php foreach ($relatedPosts as $rel) :
                $rArr   = (array) $rel;
                $rTitle = htmlspecialchars((string) ($rArr['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                $rImage = ptc_safe_url((string) ($rArr['featured_image'] ?? ''));
                $rImage = str_starts_with($rImage, '#') ? '' : $rImage;
                $rDate  = ptc_format_date($rArr['published_at'] ?? '');
                $rCat   = htmlspecialchars((string) ($rArr['category_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $rUrl   = htmlspecialchars(theme_post_url($rel), ENT_QUOTES, 'UTF-8');
                $rExcerpt = (string) ($rArr['excerpt'] ?? '');
                if (mb_strlen($rExcerpt) > 100) {
                    $rExcerpt = mb_substr($rExcerpt, 0, 97) . '…';
                }
                $rExcerpt = htmlspecialchars(strip_tags($rExcerpt), ENT_QUOTES, 'UTF-8');
            ?>
            <article class="ptc-related__card">
                <?php if ($rImage) : ?>
                <a href="<?php echo $rUrl; ?>" class="ptc-related__image" tabindex="-1" aria-hidden="true">
                    <?php echo theme_v4_img($rImage, '', ['item' => $rel]); ?>
                </a>
                <?php endif; ?>
                <div class="ptc-related__body">
                    <?php if ($rCat) : ?>
                        <span class="ptc-blog-card__category"><?php echo $rCat; ?></span>
                    <?php endif; ?>
                    <h3><a href="<?php echo $rUrl; ?>"><?php echo $rTitle; ?></a></h3>
                    <?php if ($rExcerpt) : ?>
                        <p><?php echo $rExcerpt; ?></p>
                    <?php endif; ?>
                    <?php if ($rDate) : ?>
                        <span class="ptc-related__date"><span aria-hidden="true">📅</span> <?php echo htmlspecialchars($rDate, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
