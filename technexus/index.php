<?php
/**
 * TechNexus Theme – Index / Archiv
 *
 * @package TechNexus_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}


// Der Core übergibt die Beiträge ($posts, Objekte) samt Pagination-Daten an das Template.
$posts = isset($posts) && is_array($posts) ? $posts : [];
?>

<main id="main" class="site-main tn-section">
    <div class="container">
        <header class="tn-page-hero">
            <h1>Beiträge</h1>
        </header>

        <?php if ($posts !== []) : ?>
            <div class="tech-grid">
                <?php foreach ($posts as $post) :
                    $pTitle   = htmlspecialchars((string) ($post->title   ?? ''), ENT_QUOTES, 'UTF-8');
                    $pExcerpt = htmlspecialchars((string) ($post->excerpt ?? ''), ENT_QUOTES, 'UTF-8');
                    $pUrl     = htmlspecialchars(theme_post_url($post), ENT_QUOTES, 'UTF-8');
                    $pTs      = !empty($post->published_at ?? $post->created_at ?? '')
                        ? strtotime((string) ($post->published_at ?? $post->created_at))
                        : false;
                    $pDate    = $pTs !== false ? gmdate('d.m.Y', $pTs) : '';
                    $pIsoDate = $pTs !== false ? gmdate('Y-m-d', $pTs) : '';
                ?>
                    <article class="tech-card tn-post-card">
                        <header>
                            <?php if ($pDate !== '') : ?>
                                <time class="tn-post-card__date" datetime="<?php echo tn_html_attr($pIsoDate); ?>">
                                    <?php echo $pDate; ?>
                                </time>
                            <?php endif; ?>
                            <h2 class="tn-post-card__title">
                                <a href="<?php echo $pUrl; ?>"><?php echo $pTitle; ?></a>
                            </h2>
                        </header>
                        <?php if ($pExcerpt !== '') : ?>
                            <p class="tech-card__meta"><?php echo $pExcerpt; ?></p>
                        <?php endif; ?>
                        <a href="<?php echo $pUrl; ?>" class="tech-badge tn-read-more">Weiterlesen<span class="tn-visually-hidden">: <?php echo $pTitle; ?></span></a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="tech-card tech-card--placeholder">
                <p>Noch keine Beiträge vorhanden.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

