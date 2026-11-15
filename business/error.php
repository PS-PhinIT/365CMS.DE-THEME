<?php
declare(strict_types=1);

/**
 * Business Theme – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 * Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks, Datenbank und Customizer.
 * Sonst rendert der Core header.php/footer.php; header.php öffnet bereits <main id="main-content">.
 *
 * @package IT_Business_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann nur die 4.x-Helfer nachladen (ohne Datenbank).
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$bizError    = theme_v4_error_context(get_defined_vars());
$bizLocale   = theme_v4_locale($contentLocale ?? null);
$bizDefaults = theme_v4_error_texts($bizError['code'], $bizLocale);
$bizTitle    = $bizError['title'] !== '' ? $bizError['title'] : $bizDefaults['title'];
$bizMessage  = $bizError['message'] !== '' ? $bizError['message'] : $bizDefaults['message'];
$bizHome     = theme_v4_site_url() . '/';
$bizBack     = $bizError['standalone'] ? '' : theme_v4_safe_back_url();

if ($bizError['standalone']) {
    $bizVersion  = defined('BUSINESS_THEME_VERSION') ? BUSINESS_THEME_VERSION : '4.0.0';
    $bizThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $bizError['code'] . ' – ' . $bizTitle,
        [
            $bizThemeUrl . '/style.css?v=' . rawurlencode($bizVersion),
            $bizThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($bizVersion),
        ],
        $bizLocale,
        'biz-body'
    );
    echo '<main id="main-content" class="biz-error-standalone">' . "\n";
}
?>
<section class="biz-page-hero">
    <div class="biz-container">
        <p class="biz-page-eyebrow"><?php echo theme_v4_e(theme_v4_t('Fehler', 'Error', $bizLocale)); ?> <?php echo (int) $bizError['code']; ?></p>
        <h1><?php echo theme_v4_e($bizTitle); ?></h1>
    </div>
</section>

<div class="biz-page-content">
    <div class="biz-container">
        <div class="biz-error-wrap theme-v4-error">
            <p class="biz-error-text theme-v4-error__message"><?php echo theme_v4_e($bizMessage); ?></p>
            <?php if ($bizError['retry_after'] > 0) : ?>
            <p class="biz-error-text theme-v4-error__retry"><?php echo theme_v4_e(theme_v4_t(
                'Bitte versuchen Sie es in etwa ' . max(1, (int) ceil($bizError['retry_after'] / 60)) . ' Minute(n) erneut.',
                'Please try again in about ' . max(1, (int) ceil($bizError['retry_after'] / 60)) . ' minute(s).',
                $bizLocale
            )); ?></p>
            <?php endif; ?>
            <div class="biz-error-actions theme-v4-error__actions">
                <a href="<?php echo theme_v4_e($bizHome); ?>" class="btn-biz btn-biz-primary btn-biz-lg"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $bizLocale)); ?></a>
                <?php if ($bizBack !== '') : ?>
                <a href="<?php echo theme_v4_e($bizBack); ?>" class="btn-biz btn-biz-ghost btn-biz-lg"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $bizLocale)); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
if ($bizError['standalone']) {
    echo "</main>\n";
    theme_v4_standalone_close();
}
