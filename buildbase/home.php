<?php
declare(strict_types=1);

/**
 * BuildBase Theme – Home / Startseite
 *
 * @package BuildBase_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

try {
    $c = \CMS\Services\ThemeCustomizer::instance();
} catch (\Throwable $e) {
    $c = null;
}

$bbLocale = theme_v4_locale($contentLocale ?? null);
$t = static fn(string $de, string $en): string => theme_v4_t($de, $en, $bbLocale);

$heroBadge    = (string) ($c?->get('build_hero', 'hero_badge',              '🔨 Handwerk trifft Qualität')                                       ?? '🔨 Handwerk trifft Qualität');
$heroHeadline = (string) ($c?->get('build_hero', 'hero_headline',           'Ihr Bau- & Handwerker-Verzeichnis')                                 ?? 'Ihr Bau- & Handwerker-Verzeichnis');
$heroSubline  = (string) ($c?->get('build_hero', 'hero_subline',            'Finden Sie qualifizierte Handwerker und Baufirmen in Ihrer Region.') ?? '');
$heroCta      = (string) ($c?->get('build_hero', 'hero_cta_label',          'Handwerker finden')                                                 ?? 'Handwerker finden');
$heroSecCta   = (string) ($c?->get('build_hero', 'hero_quote_request_label','Kostenvoranschlag anfordern')                                       ?? 'Kostenvoranschlag anfordern');

// Kennzahlen nur mit eigenen, belegbaren Werten: keine erfundenen Standardzahlen oder Bewertungen.
$showStats    = filter_var($c?->get('build_hero', 'show_hero_stats', false) ?? false, FILTER_VALIDATE_BOOLEAN);
$heroStats    = [];
foreach ([1 => ['Handwerker im Netzwerk', 'Tradespeople in the network'], 2 => ['Abgeschlossene Projekte', 'Completed projects'], 3 => ['Ø Kundenbewertung', 'Avg. customer rating']] as $statNo => $statDefaults) {
    $statValue = trim((string) ($c?->get('build_hero', 'hero_stat_' . $statNo . '_value', '') ?? ''));
    $statLabel = trim((string) ($c?->get('build_hero', 'hero_stat_' . $statNo . '_label', $statDefaults[0]) ?? ''));
    if ($bbLocale === 'en' && $statLabel === $statDefaults[0]) {
        $statLabel = $statDefaults[1]; // unveränderter deutscher Standardtext
    }
    if ($statValue !== '' && $statLabel !== '') {
        $heroStats[] = ['value' => $statValue, 'label' => $statLabel];
    }
}

$portfolioTitle = (string) ($c?->get('build_content', 'portfolio_section_title',    'Referenz-Projekte')                       ?? 'Referenz-Projekte');
$portfolioSub   = (string) ($c?->get('build_content', 'portfolio_section_subtitle', 'Realisierte Projekte mit Qualitätsnachweis') ?? '');
$ctaTitle       = (string) ($c?->get('build_content', 'cta_section_title',          'Qualität statt Kompromisse')              ?? 'Qualität statt Kompromisse');
$ctaText        = (string) ($c?->get('build_content', 'cta_section_text',           'Registrieren Sie Ihren Betrieb kostenlos und werden Sie sichtbar für Kunden in Ihrer Region.') ?? '');
$ctaBtnLabel    = (string) ($c?->get('build_content', 'cta_button_label',           'Jetzt Betrieb registrieren')              ?? 'Jetzt Betrieb registrieren');

$siteUrl    = buildbase_safe_url((string) SITE_URL, '/');
$isLoggedIn = theme_is_logged_in();
$safe       = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

$buildUrl = static function (string $baseUrl, string $path): string {
    $trimmed = trim($path);
    if ($trimmed === '') {
        return buildbase_safe_url(rtrim($baseUrl, '/') . '/', rtrim($baseUrl, '/') . '/');
    }
    if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
        return buildbase_safe_url($trimmed, rtrim($baseUrl, '/') . '/');
    }
    if (str_starts_with($trimmed, '#')) {
        return buildbase_safe_url(rtrim($baseUrl, '/') . '/' . $trimmed, rtrim($baseUrl, '/') . '/');
    }
    return buildbase_safe_url(rtrim($baseUrl, '/') . '/' . ltrim($trimmed, '/'), rtrim($baseUrl, '/') . '/');
};

$heroCtaUrl  = $buildUrl($siteUrl, '/handwerker');
$heroSecUrl  = $buildUrl($siteUrl, '/angebot');
$projectsUrl = $buildUrl($siteUrl, '/projekte');
$registerUrl = function_exists('theme_route_url') ? theme_route_url('register') : $buildUrl($siteUrl, '/register');

// Standardziele (/handwerker, /angebot, /projekte) sind optionale Seiten: nur verlinken, wenn sie existieren.
$showHeroCta  = theme_v4_link_available($heroCtaUrl);
$showHeroSec  = theme_v4_link_available($heroSecUrl);
$showProjects = theme_v4_link_available($projectsUrl);
?>

<main id="main" class="bb-main" role="main">

    <section class="bb-hero" aria-label="<?php echo $safe($t('Startseiten-Hero', 'Home hero')); ?>">
        <div class="bb-container">
            <?php if ($heroBadge !== '') : ?>
                <div class="bb-hero-badge"><?php echo $safe($heroBadge); ?></div>
            <?php endif; ?>
            <h1><?php echo $safe($heroHeadline); ?></h1>
            <?php if ($heroSubline !== '') : ?>
                <p class="bb-hero-sub"><?php echo $safe($heroSubline); ?></p>
            <?php endif; ?>
            <?php if ($showHeroCta || $showHeroSec) : ?>
            <div class="bb-cta-group">
                <?php if ($showHeroCta) : ?>
                <a href="<?php echo $safe($heroCtaUrl); ?>" class="bb-btn bb-btn-primary"><?php echo $safe($heroCta); ?></a>
                <?php endif; ?>
                <?php if ($showHeroSec) : ?>
                <a href="<?php echo $safe($heroSecUrl); ?>" class="bb-btn bb-btn-outline"><?php echo $safe($heroSecCta); ?></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if ($showStats && $heroStats !== []) : ?>
                <div class="bb-stats-row" role="list" aria-label="<?php echo $safe($t('Kennzahlen', 'Key figures')); ?>">
                    <?php foreach ($heroStats as $heroStat) : ?>
                    <div class="bb-stat" role="listitem">
                        <span class="bb-stat-number"><?php echo $safe($heroStat['value']); ?></span>
                        <span class="bb-stat-label"><?php echo $safe($heroStat['label']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($showProjects) : ?>
    <section class="bb-section" aria-label="<?php echo $safe($portfolioTitle); ?>">
        <div class="bb-container">
            <div class="bb-section-header">
                <h2><?php echo $safe($portfolioTitle); ?></h2>
                <?php if ($portfolioSub !== '') : ?>
                    <p class="bb-section-lead"><?php echo $safe($portfolioSub); ?></p>
                <?php endif; ?>
            </div>
            <div class="bb-grid">
                <div class="bb-card bb-card--portfolio-cta">
                    <a href="<?php echo $safe($projectsUrl); ?>" class="bb-btn bb-btn-primary">
                        <?php echo $safe($t('Alle Referenzen ansehen', 'View all references')); ?> <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!$isLoggedIn) : ?>
        <section class="bb-section bb-section--alt" aria-label="<?php echo $safe($t('Betrieb registrieren', 'Register your business')); ?>">
            <div class="bb-container bb-section-center">
                <h2><?php echo $safe($ctaTitle); ?></h2>
                <?php if ($ctaText !== '') : ?>
                    <p class="bb-section-lead"><?php echo $safe($ctaText); ?></p>
                <?php endif; ?>
                <a href="<?php echo $safe($registerUrl); ?>" class="bb-btn bb-btn-primary"><?php echo $safe($ctaBtnLabel); ?></a>
            </div>
        </section>
    <?php endif; ?>

</main>
