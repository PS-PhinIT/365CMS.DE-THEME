<?php
/**
 * PTC Theme – Blog-Übersicht
 *
 * Vom Router bereitgestellte Variablen:
 *   $posts        – array of stdObjects (Beiträge der aktuellen Seite)
 *   $total        – int, Gesamtanzahl
 *   $currentPage  – int, aktuelle Seite
 *   $totalPages   – int, Gesamtzahl Seiten
 *   $perPage      – int, Beiträge pro Seite
 *
 * @package PTC_Theme
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/* ─── Standardwerte ─── */
$posts       = $posts       ?? [];
$total       = $total       ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages  = $totalPages  ?? 1;
$siteUrl     = ptc_site_url();

/* ─── Customizer-Einstellungen ─── */
$blogLayout      = ptc_customizer_get('blog', 'blog_layout',              'grid');
$blogColumns     = ptc_customizer_get('blog', 'blog_columns',             '3');
$showSidebar     = ptc_customizer_get('blog', 'blog_show_sidebar',        '1');
$showFeatured    = ptc_customizer_get('blog', 'blog_show_featured_image', '1');
$showExcerpt     = ptc_customizer_get('blog', 'blog_show_excerpt',        '1');
$showDate        = ptc_customizer_get('blog', 'blog_show_date',           '1');
$showAuthor      = ptc_customizer_get('blog', 'blog_show_author',         '1');
$showCategory    = ptc_customizer_get('blog', 'blog_show_category',       '1');
$heroTitle       = ptc_customizer_get('blog', 'blog_hero_title',          'Unser Blog');
$heroSubtitle    = ptc_customizer_get('blog', 'blog_hero_subtitle',       'Aktuelle Beiträge, Einblicke und Neuigkeiten');

/* ─── Archiv-Kontext ─── */
// Kategorie- und Schlagwort-Archive (/kategorie/<slug>, /tag/<slug>) rendern über index.php → blog.php;
// der Core übergibt dann `$category` bzw. `$tag`. /blog?category=… leitet der Core per 301 dorthin um.
$archiveRead = static fn(mixed $source, string $key): string =>
    (string) (is_array($source) ? ($source[$key] ?? '') : (is_object($source) ? ($source->{$key} ?? '') : ''));
$activeCategory = isset($category) ? $archiveRead($category, 'slug') : '';
$activeTag      = isset($tag) && !is_string($tag) ? $archiveRead($tag, 'slug') : '';
$categoryName   = isset($category) ? $archiveRead($category, 'name') : '';
$tagName        = isset($tag) && !is_string($tag) ? $archiveRead($tag, 'name') : '';

$pageTitle = (string) $heroTitle;
if ($activeCategory !== '') {
    $pageTitle = 'Kategorie: ' . ($categoryName !== '' ? $categoryName : $activeCategory);
} elseif ($activeTag !== '') {
    $pageTitle = 'Tag: ' . ($tagName !== '' ? $tagName : $activeTag);
}

/* ─── Sidebar-Daten ─── */
$categories = [];
$tagCloud   = [];
if ($showSidebar) {
    try {
        $db     = \CMS\Database::instance();
        $prefix = $db->getPrefix();

        $catStmt = $db->execute(
            "SELECT c.name, c.slug, COUNT(p.id) AS post_count
             FROM {$prefix}post_categories c
             LEFT JOIN {$prefix}posts p ON p.category_id = c.id AND p.status = 'published'
             GROUP BY c.id
             ORDER BY c.name ASC
             LIMIT 15"
        );
        $categories = $catStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $tagStmt = $db->execute(
            "SELECT tags FROM {$prefix}posts WHERE status = 'published' AND tags IS NOT NULL AND tags != '' ORDER BY published_at DESC LIMIT 500"
        );
        $tagRows = $tagStmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        $tagCounts = [];
        foreach ($tagRows as $row) {
            foreach (array_map('trim', explode(',', $row)) as $tag) {
                if ($tag) {
                    $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                }
            }
        }
        arsort($tagCounts);
        $tagCloud = array_keys(array_slice($tagCounts, 0, 20));
    } catch (\Throwable $e) {
        // Tabellen ggf. nicht vorhanden
    }
}

$gridClass = $blogLayout === 'list'
    ? 'ptc-blog-grid ptc-blog-grid--list'
    : 'ptc-blog-grid ptc-blog-grid--cols-' . ((int) $blogColumns);
?>

<!-- ── Blog Hero ── -->
<section class="ptc-blog-hero">
    <div class="ptc-container">
        <h1><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <?php if ($heroSubtitle && $activeCategory === '' && $activeTag === '') : ?>
            <p><?php echo htmlspecialchars($heroSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <div class="ptc-blog-hero__meta">
            <span><?php echo (int) $total; ?> Beiträge</span>
            <?php if ($activeCategory !== '' || $activeTag !== '') : ?>
                <span>·</span>
                <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/blog">Alle anzeigen</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── Blog Content ── -->
<div class="ptc-blog-content">
    <div class="ptc-container">
        <div class="ptc-blog-layout<?php echo $showSidebar ? ' ptc-blog-layout--with-sidebar' : ''; ?>">

            <!-- Posts -->
            <div class="ptc-blog-posts">
                <?php if (!empty($posts)) : ?>
                    <div class="<?php echo $gridClass; ?>">
                        <?php foreach ($posts as $post) :
                            $p         = (array) $post;
                            $pTitle    = htmlspecialchars((string) ($p['title'] ?? 'Beitrag'), ENT_QUOTES, 'UTF-8');
                            $pExcerpt  = (string) ($p['excerpt'] ?? '');
                            if (mb_strlen($pExcerpt) > 160) {
                                $pExcerpt = mb_substr($pExcerpt, 0, 157) . '…';
                            }
                            $pExcerpt  = htmlspecialchars(strip_tags($pExcerpt), ENT_QUOTES, 'UTF-8');
                            $pDate     = ptc_format_date($p['published_at'] ?? '');
                            $pCat      = htmlspecialchars((string) ($p['category_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $pCatSlug  = (string) ($p['category_slug'] ?? '');
                            $pImage    = ptc_safe_url((string) ($p['featured_image'] ?? ''));
                            $pUrl      = htmlspecialchars(theme_post_url($post), ENT_QUOTES, 'UTF-8');
                            $pAuthor   = htmlspecialchars((string) ($p['author_name'] ?? 'Redaktion'), ENT_QUOTES, 'UTF-8');
                            $pTags     = is_string($p['tags'] ?? null) ? array_filter(array_map('trim', explode(',', $p['tags']))) : [];
                            $hasImage  = $showFeatured && $pImage !== '' && !str_starts_with($pImage, '#');
                        ?>
                        <article class="ptc-blog-card<?php echo $hasImage ? ' ptc-blog-card--has-image' : ''; ?>">
                            <?php if ($hasImage) : ?>
                            <a href="<?php echo $pUrl; ?>" class="ptc-blog-card__image" tabindex="-1" aria-hidden="true">
                                <?php echo theme_v4_img($pImage, '', ['item' => $post, 'sizes' => '(max-width: 768px) 100vw, 400px']); ?>
                            </a>
                            <?php endif; ?>
                            <div class="ptc-blog-card__body">
                                <h2 class="ptc-blog-card__title">
                                    <a href="<?php echo $pUrl; ?>"><?php echo $pTitle; ?></a>
                                </h2>
                                <div class="ptc-blog-card__tags-row">
                                    <?php if ($showCategory && $pCat) : ?>
                                        <a href="<?php echo htmlspecialchars(ptc_archive_url('category', $pCatSlug), ENT_QUOTES, 'UTF-8'); ?>"
                                           class="ptc-blog-card__category"><?php echo $pCat; ?></a>
                                    <?php endif; ?>
                                    <?php foreach ($pTags as $pTag) :
                                        $tEsc = htmlspecialchars($pTag, ENT_QUOTES, 'UTF-8');
                                    ?>
                                        <a href="<?php echo htmlspecialchars(ptc_archive_url('tag', $pTag), ENT_QUOTES, 'UTF-8'); ?>"
                                           class="ptc-blog-card__tag"><?php echo $tEsc; ?></a>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($showExcerpt && $pExcerpt) : ?>
                                    <p class="ptc-blog-card__excerpt"><?php echo $pExcerpt; ?></p>
                                <?php endif; ?>
                                <div class="ptc-blog-card__footer">
                                    <?php if ($showAuthor) : ?>
                                        <span class="ptc-blog-card__author"><span aria-hidden="true">👤</span> <?php echo $pAuthor; ?></span>
                                    <?php endif; ?>
                                    <?php if ($showDate && $pDate) : ?>
                                        <span class="ptc-blog-card__date"><span aria-hidden="true">📅</span> <?php echo htmlspecialchars($pDate, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginierung -->
                    <?php if ($totalPages > 1) : ?>
                    <nav class="ptc-blog-pagination" aria-label="Seitennavigation">
                        <?php
                        // Blätter-Links auf der aktuellen Archiv-URL (/blog, /kategorie/<slug>, /tag/<slug>).
                        $baseUrl     = theme_v4_site_url(theme_v4_request_path());
                        $qBase       = '?';
                        $currentPage = (int) $currentPage;
                        $totalPages  = (int) $totalPages;
                        ?>
                        <?php if ($currentPage > 1) : ?>
                        <a href="<?php echo htmlspecialchars($baseUrl . $qBase . 'p=' . ($currentPage - 1), ENT_QUOTES, 'UTF-8'); ?>"
                           class="ptc-blog-pagination__btn" aria-label="Vorherige Seite">← Zurück</a>
                        <?php endif; ?>
                        <span class="ptc-blog-pagination__info">Seite <?php echo $currentPage; ?> von <?php echo $totalPages; ?></span>
                        <?php if ($currentPage < $totalPages) : ?>
                        <a href="<?php echo htmlspecialchars($baseUrl . $qBase . 'p=' . ($currentPage + 1), ENT_QUOTES, 'UTF-8'); ?>"
                           class="ptc-blog-pagination__btn" aria-label="Nächste Seite">Weiter →</a>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>

                <?php else : ?>
                    <!-- Empty State -->
                    <div class="ptc-blog-empty">
                        <p class="ptc-empty-icon" aria-hidden="true">📭</p>
                        <p><strong>Keine Artikel gefunden</strong></p>
                        <?php if ($activeCategory !== '' || $activeTag !== '') : ?>
                            <p>Für diese Auswahl gibt es noch keine Beiträge.</p>
                            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/blog"
                               class="btn-ptc btn-ptc-primary ptc-section-cta--spaced">Alle Artikel anzeigen</a>
                        <?php else : ?>
                            <p>Die ersten Artikel erscheinen hier, sobald sie veröffentlicht werden.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <?php if ($showSidebar) : ?>
            <aside class="ptc-blog-sidebar" aria-label="Blog Sidebar">

                <!-- Suche -->
                <div class="ptc-blog-sidebar__widget">
                    <h2 class="ptc-blog-sidebar__title" id="ptc-sidebar-search-title"><span aria-hidden="true">🔍</span> Suche</h2>
                    <form action="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/search" method="GET" role="search" aria-labelledby="ptc-sidebar-search-title">
                        <div class="ptc-blog-sidebar__search">
                            <input type="search" name="q" placeholder="Artikel suchen …" autocomplete="off" aria-label="Suchbegriff"
                                   value="<?php echo htmlspecialchars(is_string($_GET['q'] ?? null) ? $_GET['q'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" aria-label="Suchen"><span aria-hidden="true">🔍</span></button>
                        </div>
                    </form>
                </div>

                <!-- Kategorien -->
                <?php if (!empty($categories)) : ?>
                <div class="ptc-blog-sidebar__widget">
                    <h2 class="ptc-blog-sidebar__title"><span aria-hidden="true">📂</span> Kategorien</h2>
                    <ul class="ptc-blog-sidebar__list">
                        <?php foreach ($categories as $cat) :
                            $cName  = htmlspecialchars((string) ($cat['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $cSlug  = (string) ($cat['slug'] ?? '');
                            $cCount = (int)($cat['post_count'] ?? 0);
                            $isActive = ($activeCategory !== '' && $activeCategory === $cSlug);
                        ?>
                        <li<?php echo $isActive ? ' class="active"' : ''; ?>>
                            <a href="<?php echo htmlspecialchars(ptc_archive_url('category', $cSlug), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                                <?php echo $cName; ?>
                                <span class="ptc-blog-sidebar__count"><?php echo $cCount; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Tags -->
                <?php if (!empty($tagCloud)) : ?>
                <div class="ptc-blog-sidebar__widget">
                    <h2 class="ptc-blog-sidebar__title"><span aria-hidden="true">🏷️</span> Tags</h2>
                    <div class="ptc-blog-sidebar__tags">
                        <?php foreach ($tagCloud as $tag) :
                            $tEsc    = htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');
                            $isActive = ($activeTag !== '' && $activeTag === $tag);
                        ?>
                        <a href="<?php echo htmlspecialchars(ptc_archive_url('tag', $tag), ENT_QUOTES, 'UTF-8'); ?>"
                           class="ptc-tag<?php echo $isActive ? ' ptc-tag--active' : ''; ?>"<?php echo $isActive ? ' aria-current="page"' : ''; ?>><?php echo $tEsc; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </aside>
            <?php endif; ?>

        </div>
    </div>
</div>
