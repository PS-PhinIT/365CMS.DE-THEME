<?php
declare(strict_types=1);

/**
 * Kontor – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 *
 * - Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks, Customizer
 *   und Datenbank; eigenes <main>.
 * - Sonst rendert der Core header.php/footer.php; header.php öffnet <main id="main-content"> bereits,
 *   deshalb hier nur der Inhalt.
 *
 * @package Kontor_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann ohne functions.php.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$ktError    = theme_v4_error_context(get_defined_vars());
$ktLocale   = theme_v4_locale($contentLocale ?? null);
$ktDefaults = theme_v4_error_texts($ktError['code'], $ktLocale);
$ktTitle    = $ktError['title'] !== '' ? $ktError['title'] : $ktDefaults['title'];
$ktMessage  = $ktError['message'] !== '' ? $ktError['message'] : $ktDefaults['message'];
$ktHome     = theme_v4_site_url() . '/';
$ktBack     = $ktError['standalone'] ? '' : theme_v4_safe_back_url();
$ktVersion  = defined('KONTOR_THEME_VERSION') ? (string) KONTOR_THEME_VERSION : '2.0.0';

if ($ktError['standalone']) {
    $ktThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $ktError['code'] . ' – ' . $ktTitle,
        [
            $ktThemeUrl . '/style.css?v=' . rawurlencode($ktVersion),
            $ktThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($ktVersion),
        ],
        $ktLocale,
        'kontor is-error'
    );
    echo '<main id="main-content" class="kt-main">' . "\n";
}
?>
<section class="kt-notfound" aria-labelledby="kt-error-title">
    <div class="kt-container kt-notfound__inner">
        <p class="kt-notfound__code" aria-hidden="true"><?php echo (int) $ktError['code']; ?></p>
        <p class="kt-eyebrow kt-eyebrow--light"><?php echo theme_v4_e(theme_v4_t('Fehler', 'Error', $ktLocale) . ' ' . $ktError['code']); ?></p>
        <h1 class="kt-hero__title" id="kt-error-title"><?php echo theme_v4_e($ktTitle); ?></h1>
        <p class="kt-hero__text"><?php echo theme_v4_e($ktMessage); ?></p>
        <?php if ($ktError['retry_after'] > 0) : ?>
            <p class="kt-hero__text"><?php echo theme_v4_e(theme_v4_t(
                'Bitte versuchen Sie es in etwa ' . max(1, (int) ceil($ktError['retry_after'] / 60)) . ' Minute(n) erneut.',
                'Please try again in about ' . max(1, (int) ceil($ktError['retry_after'] / 60)) . ' minute(s).',
                $ktLocale
            )); ?></p>
        <?php endif; ?>
        <div class="kt-hero__actions">
            <a class="kt-button kt-button--accent" href="<?php echo theme_v4_e($ktHome); ?>"><span><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $ktLocale)); ?></span></a>
            <?php if ($ktBack !== '') : ?>
                <a class="kt-button kt-button--outline-light" href="<?php echo theme_v4_e($ktBack); ?>"><span><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $ktLocale)); ?></span></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
if ($ktError['standalone']) {
    echo "</main>\n";
    theme_v4_standalone_close();
}
