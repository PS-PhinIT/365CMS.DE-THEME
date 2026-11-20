<?php
declare(strict_types=1);

/**
 * Feder – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 *
 * - Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks, Customizer
 *   und Datenbank; eigenes <main>.
 * - Sonst rendert der Core header.php/footer.php; header.php öffnet <main id="main-content"> bereits,
 *   deshalb hier nur der Inhalt.
 *
 * @package Feder_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann ohne functions.php.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$fdrError    = theme_v4_error_context(get_defined_vars());
$fdrLocale   = theme_v4_locale($contentLocale ?? null);
$fdrDefaults = theme_v4_error_texts($fdrError['code'], $fdrLocale);
$fdrTitle    = $fdrError['title'] !== '' ? $fdrError['title'] : $fdrDefaults['title'];
$fdrMessage  = $fdrError['message'] !== '' ? $fdrError['message'] : $fdrDefaults['message'];
$fdrHome     = theme_v4_site_url() . '/';
$fdrBack     = $fdrError['standalone'] ? '' : theme_v4_safe_back_url();
$fdrVersion  = defined('FEDER_THEME_VERSION') ? (string) FEDER_THEME_VERSION : '2.1.1';

if ($fdrError['standalone']) {
    $fdrThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $fdrError['code'] . ' – ' . $fdrTitle,
        [
            $fdrThemeUrl . '/style.css?v=' . rawurlencode($fdrVersion),
            $fdrThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($fdrVersion),
        ],
        $fdrLocale,
        'feder is-error'
    );
    echo '<main id="main-content" class="fdr-main">' . "\n";
}
?>
<section class="fdr-notfound fdr-measure" aria-labelledby="fdr-error-title">
    <p class="fdr-notfound__code" aria-hidden="true"><?php echo (int) $fdrError['code']; ?></p>
    <p class="fdr-kicker"><?php echo theme_v4_e(theme_v4_t('Fehler', 'Error', $fdrLocale) . ' ' . $fdrError['code']); ?></p>
    <h1 class="fdr-archive-head__title" id="fdr-error-title"><?php echo theme_v4_e($fdrTitle); ?></h1>
    <p class="fdr-archive-head__text theme-v4-error__message"><?php echo theme_v4_e($fdrMessage); ?></p>
    <?php if ($fdrError['retry_after'] > 0) : ?>
        <p class="fdr-meta theme-v4-error__retry"><?php echo theme_v4_e(theme_v4_t(
            'Bitte versuchen Sie es in etwa ' . max(1, (int) ceil($fdrError['retry_after'] / 60)) . ' Minute(n) erneut.',
            'Please try again in about ' . max(1, (int) ceil($fdrError['retry_after'] / 60)) . ' minute(s).',
            $fdrLocale
        )); ?></p>
    <?php endif; ?>
    <p class="fdr-error-actions">
        <a class="fdr-button" href="<?php echo theme_v4_e($fdrHome); ?>"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $fdrLocale)); ?></a>
        <?php if ($fdrBack !== '') : ?>
            <a class="fdr-button fdr-button--ghost" href="<?php echo theme_v4_e($fdrBack); ?>"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $fdrLocale)); ?></a>
        <?php endif; ?>
    </p>
</section>
<?php
if ($fdrError['standalone']) {
    echo "</main>\n";
    theme_v4_standalone_close();
}
