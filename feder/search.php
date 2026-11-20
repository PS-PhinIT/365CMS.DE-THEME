<?php
declare(strict_types=1);

/**
 * Feder – Suche
 *
 * Datenvertrag (365CMS 3.4, ThemeRouter::renderSearch): $results (Arrays mit _type,
 * _type_label, slug, title, meta_description …) und $query (Suchbegriff).
 *
 * @package Feder_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$results = isset($results) && is_array($results) ? $results : [];
$searchQuery = trim(strip_tags((string) ($query ?? '')));
$searchQuery = mb_substr($searchQuery, 0, 120, 'UTF-8');
$count = count($results);
$topics = $count === 0 ? feder_get_categories(8) : [];
?>

<header class="fdr-archive-head fdr-measure">
    <p class="fdr-kicker"><?php echo feder_e(feder_t('Suche', 'Search')); ?></p>
    <h1 class="fdr-archive-head__title">
        <?php if ($searchQuery !== '') : ?>
            <?php echo feder_e(feder_t(sprintf('%d Treffer für „%s“', $count, $searchQuery), sprintf($count === 1 ? '%d result for “%s”' : '%d results for “%s”', $count, $searchQuery))); ?>
        <?php else : ?>
            <?php echo feder_e(feder_t('Was möchten Sie lesen?', 'What would you like to read?')); ?>
        <?php endif; ?>
    </h1>
    <?php feder_search_form('fdr-search-page', $searchQuery, 'fdr-searchform--large'); ?>
</header>

<section class="fdr-archive fdr-measure" aria-label="<?php echo feder_e(feder_t('Suchergebnisse', 'Search results')); ?>">
    <?php if ($results !== []) : ?>
        <ol class="fdr-results">
            <?php foreach ($results as $result) :
                $result = is_object($result) ? get_object_vars($result) : $result;
                if (!is_array($result)) {
                    continue;
                }
                $resultUrl = feder_safe_url(theme_search_result_url($result), feder_url('/'));
                $resultTitle = trim((string) ($result['title'] ?? $result['name'] ?? ''));
                $resultText = feder_excerpt($result, 220, false);
                $resultType = trim((string) ($result['_type_label'] ?? ''));
                $resultDate = (string) ($result['published_at'] ?? '');
                ?>
                <li class="fdr-results__item">
                    <p class="fdr-meta">
                        <?php if ($resultType !== '') : ?><span class="fdr-badge"><?php echo feder_e($resultType); ?></span><?php endif; ?>
                        <?php if ($resultDate !== '' && feder_format_date($resultDate) !== '') : ?>
                            <time datetime="<?php echo feder_e(feder_format_date($resultDate, 'iso')); ?>"><?php echo feder_e(feder_format_date($resultDate)); ?></time>
                        <?php endif; ?>
                    </p>
                    <h2 class="fdr-results__title"><a href="<?php echo feder_e($resultUrl); ?>"><?php echo feder_e($resultTitle !== '' ? $resultTitle : $resultUrl); ?></a></h2>
                    <?php if ($resultText !== '') : ?>
                        <p class="fdr-results__text"><?php echo feder_e($resultText); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php elseif ($searchQuery !== '') : ?>
        <div class="fdr-empty">
            <p><?php echo feder_e(sprintf(feder_t('Zu „%s“ wurde nichts gefunden. Versuchen Sie einen anderen Begriff oder stöbern Sie in den Themen.', 'Nothing found for “%s”. Try a different term or browse the topics.'), $searchQuery)); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($topics !== []) : ?>
        <h2 class="fdr-section-title"><?php echo feder_e(feder_t('Themen', 'Topics')); ?></h2>
        <ul class="fdr-chips">
            <?php foreach ($topics as $topic) : ?>
                <li><a class="fdr-chip" href="<?php echo feder_e($topic['url']); ?>"><?php echo feder_e($topic['name']); ?><span class="fdr-chip__count"><?php echo (int) $topic['count']; ?></span></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
