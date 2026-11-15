<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}


// Der Core übergibt die Beiträge ($posts, Objekte) samt Pagination-Daten an das Template; Kategorie- und
// Schlagwort-Archive zusätzlich `$category` bzw. `$tag` (Name, Slug).
$posts = isset($posts) && is_array($posts) ? $posts : [];
$safe  = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$siteUrl = buildbase_safe_url((string) SITE_URL, '/');

$bbArchiveName = static fn(mixed $source): string =>
    trim((string) (is_array($source) ? ($source['name'] ?? '') : (is_object($source) ? ($source->name ?? '') : '')));
$bbTitle = 'Beiträge';
if (isset($category) && $bbArchiveName($category) !== '') {
    $bbTitle = 'Kategorie: ' . $bbArchiveName($category);
} elseif (isset($tag) && !is_string($tag) && $bbArchiveName($tag) !== '') {
    $bbTitle = 'Schlagwort: ' . $bbArchiveName($tag);
}

$bbPage  = max(1, (int) ($currentPage ?? 1));
$bbPages = max(1, (int) ($totalPages ?? 1));
$bbBase  = theme_v4_site_url(theme_v4_request_path());
?>
<main id="main" class="bb-main bb-page-section" role="main">
    <div class="bb-container">
        <h1 class="bb-page-title"><?php echo $safe($bbTitle); ?></h1>

        <?php if (!empty($posts)) : ?>
            <div class="bb-grid">
                <?php foreach ($posts as $post) :
                    $postObj   = is_array($post) ? (object) $post : $post;
                    $postUrl   = buildbase_safe_url(theme_post_url($post), $siteUrl);
                    $postTitle = isset($postObj->title)   ? (string) $postObj->title   : '';
                    $excerpt   = isset($postObj->excerpt) ? trim(strip_tags((string) $postObj->excerpt)) : '';
                ?>
                    <article class="bb-card">
                        <h2 class="bb-post-title">
                            <a href="<?php echo $safe($postUrl); ?>"><?php echo $safe($postTitle); ?></a>
                        </h2>
                        <?php if ($excerpt !== '') : ?>
                            <p class="bb-post-excerpt"><?php echo $safe($excerpt); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($bbPages > 1) : ?>
            <nav class="cms-pagination" aria-label="Seitennavigation">
                <?php if ($bbPage > 1) : ?>
                    <a class="bb-btn bb-btn-outline" href="<?php echo $safe($bbBase . ($bbPage - 1 > 1 ? '?p=' . ($bbPage - 1) : '')); ?>" rel="prev"><span aria-hidden="true">←</span> Neuere Beiträge</a>
                <?php endif; ?>
                <span aria-current="page">Seite <?php echo $bbPage; ?> von <?php echo $bbPages; ?></span>
                <?php if ($bbPage < $bbPages) : ?>
                    <a class="bb-btn bb-btn-outline" href="<?php echo $safe($bbBase . '?p=' . ($bbPage + 1)); ?>" rel="next">Ältere Beiträge <span aria-hidden="true">→</span></a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        <?php else : ?>
            <p class="bb-empty">Noch keine Beiträge.</p>
        <?php endif; ?>
    </div>
</main>
