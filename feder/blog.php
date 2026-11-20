<?php
declare(strict_types=1);

/**
 * Feder – Beitragsarchiv (Blog, Kategorie, Schlagwort, Autor:in)
 *
 * Datenvertrag (365CMS 3.4, ThemeRouter): $posts (Objekte), $currentPage, $totalPages,
 * optional $total, $category / $tag (Arrays), $author (Array), $query (Suche im Archiv),
 * $isOverview + $overviewItems (Kategorie-/Schlagwort-Übersicht).
 *
 * @package Feder_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$posts = isset($posts) && is_array($posts) ? $posts : [];
$currentPage = max(1, (int) ($currentPage ?? 1));
$totalPages = max(1, (int) ($totalPages ?? 1));
$total = (int) ($total ?? count($posts));
$archiveQuery = isset($query) && is_string($query) ? trim($query) : '';
$isOverview = !empty($isOverview);
$overviewItems = isset($overviewItems) && is_array($overviewItems) ? $overviewItems : [];
$showReadingTime = feder_flag('feder_article', 'show_reading_time', true);

$kicker = feder_t('Archiv', 'Archive');
$title = feder_t('Alle Texte', 'All posts');
$description = '';
$pageParam = 'p';
$searchable = false;

if (isset($author) && is_array($author)) {
    $kicker = feder_t('Autor:in', 'Author');
    $title = trim((string) ($author['display_name'] ?? feder_t('Autor:in', 'Author')));
    $description = trim((string) ($author['bio'] ?? ''));
    $pageParam = 'page';
} elseif (isset($category) && is_array($category)) {
    $kicker = $isOverview ? feder_t('Übersicht', 'Overview') : feder_t('Thema', 'Topic');
    $title = trim((string) ($category['name'] ?? feder_t('Thema', 'Topic')));
    $description = trim((string) ($category['description'] ?? ''));
    $searchable = true;
} elseif (isset($tag) && is_array($tag)) {
    $kicker = $isOverview ? feder_t('Übersicht', 'Overview') : feder_t('Schlagwort', 'Tag');
    $title = $isOverview ? trim((string) ($tag['name'] ?? feder_t('Schlagwörter', 'Tags'))) : '#' . trim((string) ($tag['name'] ?? ''));
    $description = trim((string) ($tag['description'] ?? ''));
    $searchable = true;
}

$authorAvatar = isset($author) && is_array($author) ? feder_media_url($author['avatar_url'] ?? '') : '';
$formAction = (string) (parse_url(theme_v4_site_url(theme_v4_request_path()), PHP_URL_PATH) ?? '/'); // normalisiert statt REQUEST_URI
?>

<header class="fdr-archive-head fdr-measure">
    <?php if ($authorAvatar !== '') : ?>
        <img class="fdr-archive-head__avatar" src="<?php echo feder_e($authorAvatar); ?>" alt="" width="80" height="80">
    <?php endif; ?>
    <p class="fdr-kicker"><?php echo feder_e($kicker); ?></p>
    <h1 class="fdr-archive-head__title"><?php echo feder_e($title); ?></h1>
    <?php if ($description !== '') : ?>
        <p class="fdr-archive-head__text"><?php echo feder_e($description); ?></p>
    <?php endif; ?>
    <p class="fdr-meta">
        <?php if ($isOverview) : ?>
            <?php echo feder_e((int) $total . ' ' . ($total === 1 ? feder_t('Eintrag', 'entry') : feder_t('Einträge', 'entries'))); ?>
        <?php else : ?>
            <?php echo feder_e((int) $total . ' ' . ($total === 1 ? feder_t('Beitrag', 'post') : feder_t('Beiträge', 'posts'))); ?><?php if ($totalPages > 1) : ?> · <?php echo feder_e(sprintf(feder_t('Seite %d von %d', 'Page %d of %d'), $currentPage, $totalPages)); ?><?php endif; ?>
        <?php endif; ?>
    </p>
    <?php if ($searchable) : ?>
        <form class="fdr-searchform fdr-searchform--inline" method="get" action="<?php echo feder_e($formAction); ?>" role="search">
            <label for="fdr-archive-q" class="fdr-visually-hidden"><?php echo feder_e(feder_t('In diesem Archiv suchen', 'Search this archive')); ?></label>
            <input id="fdr-archive-q" type="search" name="q" value="<?php echo feder_e($archiveQuery); ?>" placeholder="<?php echo feder_e(sprintf(feder_t('In „%s“ suchen …', 'Search in “%s” …'), $title)); ?>" maxlength="120">
            <button type="submit" class="fdr-button fdr-button--ghost"><?php echo feder_icon('search'); ?><span><?php echo feder_e(feder_t('Filtern', 'Filter')); ?></span></button>
        </form>
    <?php endif; ?>
</header>

<?php if ($isOverview) : ?>
    <section class="fdr-overview fdr-wide" aria-label="<?php echo feder_e($title); ?>">
        <?php if ($overviewItems === []) : ?>
            <p class="fdr-empty fdr-measure"><?php echo feder_e(feder_t('Keine Einträge gefunden.', 'No entries found.')); ?></p>
        <?php else : ?>
            <ul class="fdr-overview__grid">
                <?php foreach ($overviewItems as $item) :
                    $itemUrl = feder_safe_url((string) ($item['url'] ?? ''), '');
                    if ($itemUrl === '') {
                        continue;
                    }
                    $count = (int) ($item['count'] ?? 0);
                    ?>
                    <li>
                        <a class="fdr-overview__card" href="<?php echo feder_e(feder_url($itemUrl)); ?>">
                            <span class="fdr-overview__title"><?php echo feder_e((string) ($item['title'] ?? '')); ?></span>
                            <?php if (trim((string) ($item['description'] ?? '')) !== '') : ?>
                                <span class="fdr-overview__text"><?php echo feder_e((string) $item['description']); ?></span>
                            <?php endif; ?>
                            <span class="fdr-overview__count"><?php echo feder_e($count . ' ' . ($count === 1 ? feder_t('Beitrag', 'post') : feder_t('Beiträge', 'posts'))); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
<?php else : ?>
    <section class="fdr-archive fdr-measure" aria-label="<?php echo feder_e(feder_t('Beiträge', 'Posts')); ?>">
        <?php if ($posts === []) : ?>
            <div class="fdr-empty">
                <p><?php echo $archiveQuery !== '' ? feder_e(sprintf(feder_t('Keine Beiträge passen zu „%s“.', 'No posts match “%s”.'), $archiveQuery)) : feder_e(feder_t('Hier ist noch nichts erschienen.', 'Nothing has been published here yet.')); ?></p>
                <p><a class="fdr-link-arrow" href="<?php echo feder_e(feder_url('/blog')); ?>"><?php echo feder_e(feder_t('Zu allen Texten', 'Go to all posts')); ?><?php echo feder_icon('arrow'); ?></a></p>
            </div>
        <?php else : ?>
            <ol class="fdr-cards">
                <?php foreach ($posts as $item) :
                    $item = is_array($item) ? (object) $item : $item;
                    if (!is_object($item)) {
                        continue;
                    }
                    $itemUrl = feder_post_link($item);
                    $itemImage = feder_media_url($item->featured_image ?? '');
                    $itemCategory = trim((string) ($item->category_name ?? ''));
                    $itemExcerpt = feder_excerpt($item, 200);
                    $itemDate = $item->published_at ?? $item->created_at ?? '';
                    ?>
                    <li class="fdr-card<?php echo $itemImage !== '' ? ' fdr-card--media' : ''; ?>">
                        <article>
                            <div class="fdr-card__body">
                                <p class="fdr-meta">
                                    <time datetime="<?php echo feder_e(feder_format_date($itemDate, 'iso')); ?>"><?php echo feder_e(feder_format_date($itemDate)); ?></time>
                                    <?php if ($itemCategory !== '') : ?>
                                        <span class="fdr-dot" aria-hidden="true">·</span>
                                        <a href="<?php echo feder_e(feder_archive_url('category', (string) ($item->category_slug ?? ''))); ?>"><?php echo feder_e($itemCategory); ?></a>
                                    <?php endif; ?>
                                </p>
                                <h2 class="fdr-card__title"><a href="<?php echo feder_e($itemUrl); ?>"><?php echo feder_e((string) ($item->title ?? '')); ?></a></h2>
                                <?php if ($itemExcerpt !== '') : ?>
                                    <p class="fdr-card__excerpt"><?php echo feder_e($itemExcerpt); ?></p>
                                <?php endif; ?>
                                <?php if ($showReadingTime && isset($item->content)) : ?>
                                    <p class="fdr-meta"><?php echo feder_icon('clock'); ?><span><?php echo feder_e(sprintf(feder_t('%d Min. Lesezeit', '%d min read'), feder_reading_time((string) $item->content))); ?></span></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($itemImage !== '') : ?>
                                <a class="fdr-card__media" href="<?php echo feder_e($itemUrl); ?>" tabindex="-1" aria-hidden="true">
                                    <?php echo feder_img($itemImage, '', ['item' => $item], 320, 200); ?>
                                </a>
                            <?php endif; ?>
                        </article>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php feder_pagination($currentPage, $totalPages, $pageParam); ?>
