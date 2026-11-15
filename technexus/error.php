<?php
/**
 * TechNexus Theme – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0).
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 * Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks und Datenbank.
 *
 * @package TechNexus_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – Theme-Helfer dann nachladen.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$tnError    = theme_v4_error_context(get_defined_vars());
$tnLocale   = theme_v4_locale($contentLocale ?? null);
$tnDefaults = theme_v4_error_texts($tnError['code'], $tnLocale);
$tnTitle    = $tnError['title'] !== '' ? $tnError['title'] : $tnDefaults['title'];
$tnMessage  = $tnError['message'] !== '' ? $tnError['message'] : $tnDefaults['message'];
$tnHome     = theme_v4_site_url() . '/';
$tnBack     = $tnError['standalone'] ? '' : theme_v4_safe_back_url();
$tnVersion  = defined('TECHNEXUS_THEME_VERSION') ? TECHNEXUS_THEME_VERSION : '2.0.0';

if ($tnError['standalone']) {
    $tnThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $tnError['code'] . ' – ' . $tnTitle,
        [$tnThemeUrl . '/style.css?v=' . rawurlencode($tnVersion), $tnThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($tnVersion)],
        $tnLocale,
        'tn-body'
    );
}
?>
<main id="main" class="site-main error-page tn-section">
    <div class="container tn-error-layout theme-v4-error">
        <div class="tech-card tn-error-card">
            <p class="tn-error-code tn-error-code--system" aria-hidden="true"><?php echo (int) $tnError['code']; ?></p>
            <h1><?php echo theme_v4_e($tnTitle); ?></h1>
            <p class="tech-card__meta theme-v4-error__message"><?php echo theme_v4_e($tnMessage); ?></p>
            <?php if ($tnError['retry_after'] > 0) : ?>
            <p class="theme-v4-error__retry"><?php echo theme_v4_e(theme_v4_t(
                'Bitte versuche es in etwa ' . max(1, (int) ceil($tnError['retry_after'] / 60)) . ' Minute(n) erneut.',
                'Please try again in about ' . max(1, (int) ceil($tnError['retry_after'] / 60)) . ' minute(s).',
                $tnLocale
            )); ?></p>
            <?php endif; ?>
            <div class="tn-error-actions theme-v4-error__actions">
                <a href="<?php echo theme_v4_e($tnHome); ?>" class="btn btn-primary"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $tnLocale)); ?></a>
                <?php if ($tnBack !== '') : ?>
                <a href="<?php echo theme_v4_e($tnBack); ?>" class="btn btn-outline"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $tnLocale)); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php
if ($tnError['standalone']) {
    theme_v4_standalone_close();
}
