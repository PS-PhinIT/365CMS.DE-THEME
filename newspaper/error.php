<?php
/**
 * CMS Newspaper Theme – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler in index.php) zusätzlich `$error_standalone` und bei 503
 * `$error_retry_after`. Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer,
 * Hooks, Datenbank und Customizer – deshalb ohne functions.php lauffähig.
 * Sonst rendert der Core header.php/footer.php drumherum; header.php öffnet bereits <main id="main-content">.
 *
 * @package CmsNewspaper_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann die gemeinsame 4.x-Schicht nachladen.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$newsError    = theme_v4_error_context(get_defined_vars());
$newsLocale   = theme_v4_locale($contentLocale ?? null);
$newsDefaults = theme_v4_error_texts($newsError['code'], $newsLocale);
$newsTitle    = $newsError['title'] !== '' ? $newsError['title'] : $newsDefaults['title'];
$newsMessage  = $newsError['message'] !== '' ? $newsError['message'] : $newsDefaults['message'];
$newsHome     = theme_v4_site_url() . '/';
$newsBack     = $newsError['standalone'] ? '' : theme_v4_safe_back_url();
$newsVersion  = defined('CMSNEWSPAPER_THEME_VERSION') ? (string) CMSNEWSPAPER_THEME_VERSION : '2.0.0';

if ($newsError['standalone']) {
    $newsThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $newsError['code'] . ' – ' . $newsTitle,
        [
            $newsThemeUrl . '/style.css?v=' . rawurlencode($newsVersion),
            $newsThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($newsVersion),
        ],
        $newsLocale,
        'news-body'
    );
    echo '<main id="main-content" class="news-content">' . "\n";
}
?>

<section class="news-page-hero" aria-labelledby="news-error-title">
    <div class="news-container">
        <span class="news-kicker"><?php echo theme_v4_e(theme_v4_t('Fehler', 'Error', $newsLocale) . ' ' . $newsError['code']); ?></span>
        <h1 id="news-error-title"><?php echo theme_v4_e($newsTitle); ?></h1>
        <p><?php echo theme_v4_e($newsMessage); ?></p>
    </div>
</section>

<div class="news-page-content">
    <div class="news-container">
        <div class="news-error-wrap">
            <div class="news-error-code" aria-hidden="true"><?php echo (int) $newsError['code']; ?></div>
            <?php if ($newsError['retry_after'] > 0) : ?>
                <p class="news-error-text"><?php echo theme_v4_e(theme_v4_t(
                    'Bitte versuchen Sie es in etwa ' . max(1, (int) ceil($newsError['retry_after'] / 60)) . ' Minute(n) erneut.',
                    'Please try again in about ' . max(1, (int) ceil($newsError['retry_after'] / 60)) . ' minute(s).',
                    $newsLocale
                )); ?></p>
            <?php endif; ?>
            <div class="news-error-actions">
                <a href="<?php echo theme_v4_e($newsHome); ?>" class="news-btn news-btn-primary"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $newsLocale)); ?></a>
                <?php if ($newsBack !== '') : ?>
                    <a href="<?php echo theme_v4_e($newsBack); ?>" class="news-btn news-btn-ghost"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $newsLocale)); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
if ($newsError['standalone']) {
    echo "</main>\n";
    theme_v4_standalone_close();
}
