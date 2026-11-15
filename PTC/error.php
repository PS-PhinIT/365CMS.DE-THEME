<?php
/**
 * PTC Theme – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 * Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks, Datenbank und Customizer.
 * Sonst rendert der Core header.php/footer.php; header.php öffnet bereits <main id="main-content">.
 *
 * @package PTC_Theme
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann nur die 4.x-Helfer nachladen (ohne Datenbank).
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$ptcError    = theme_v4_error_context(get_defined_vars());
$ptcLocale   = theme_v4_locale($contentLocale ?? null);
$ptcDefaults = theme_v4_error_texts($ptcError['code'], $ptcLocale);
$ptcTitle    = $ptcError['title'] !== '' ? $ptcError['title'] : $ptcDefaults['title'];
$ptcMessage  = $ptcError['message'] !== '' ? $ptcError['message'] : $ptcDefaults['message'];
$ptcHome     = theme_v4_site_url() . '/';
$ptcBack     = $ptcError['standalone'] ? '' : theme_v4_safe_back_url();

if ($ptcError['standalone']) {
    $ptcVersion  = defined('PTC_THEME_VERSION') ? PTC_THEME_VERSION : '4.0.0';
    $ptcThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $ptcError['code'] . ' – ' . $ptcTitle,
        [
            $ptcThemeUrl . '/style.css?v=' . rawurlencode($ptcVersion),
            $ptcThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($ptcVersion),
        ],
        $ptcLocale,
        'ptc-body'
    );
    echo '<main id="main-content" class="ptc-error-standalone">' . "\n";
}
?>
<section class="ptc-page-hero">
    <div class="ptc-container">
        <p class="ptc-hero-overline"><?php echo theme_v4_e(theme_v4_t('Fehler', 'Error', $ptcLocale)); ?> <?php echo (int) $ptcError['code']; ?></p>
        <h1><?php echo theme_v4_e($ptcTitle); ?></h1>
    </div>
</section>

<div class="ptc-page-content">
    <div class="ptc-container ptc-center-block theme-v4-error">
        <p class="ptc-text-muted theme-v4-error__message"><?php echo theme_v4_e($ptcMessage); ?></p>
        <?php if ($ptcError['retry_after'] > 0) : ?>
        <p class="theme-v4-error__retry"><?php echo theme_v4_e(theme_v4_t(
            'Bitte versuche es in etwa ' . max(1, (int) ceil($ptcError['retry_after'] / 60)) . ' Minute(n) erneut.',
            'Please try again in about ' . max(1, (int) ceil($ptcError['retry_after'] / 60)) . ' minute(s).',
            $ptcLocale
        )); ?></p>
        <?php endif; ?>
        <div class="ptc-error-actions theme-v4-error__actions">
            <a href="<?php echo theme_v4_e($ptcHome); ?>" class="btn-ptc btn-ptc-primary btn-ptc-lg"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $ptcLocale)); ?></a>
            <?php if ($ptcBack !== '') : ?>
            <a href="<?php echo theme_v4_e($ptcBack); ?>" class="btn-ptc btn-ptc-ghost btn-ptc-lg"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $ptcLocale)); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
if ($ptcError['standalone']) {
    echo "</main>\n";
    theme_v4_standalone_close();
}
