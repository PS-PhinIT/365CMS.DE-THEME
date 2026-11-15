<?php
/**
 * PersonalFlow Theme – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0).
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 * Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks und Datenbank.
 *
 * @package PersonalFlow_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – Theme-Helfer dann nachladen.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$pfError    = theme_v4_error_context(get_defined_vars());
$pfLocale   = theme_v4_locale($contentLocale ?? null);
$pfDefaults = theme_v4_error_texts($pfError['code'], $pfLocale);
$pfTitle    = $pfError['title'] !== '' ? $pfError['title'] : $pfDefaults['title'];
$pfMessage  = $pfError['message'] !== '' ? $pfError['message'] : $pfDefaults['message'];
$pfHome     = theme_v4_site_url() . '/';
$pfBack     = $pfError['standalone'] ? '' : theme_v4_safe_back_url();
$pfVersion  = defined('PERSONALFLOW_THEME_VERSION') ? PERSONALFLOW_THEME_VERSION : '2.0.0';

if ($pfError['standalone']) {
    $pfThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $pfError['code'] . ' – ' . $pfTitle,
        [$pfThemeUrl . '/style.css?v=' . rawurlencode($pfVersion), $pfThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($pfVersion)],
        $pfLocale,
        'pf-body no-sticky-header'
    );
}
// Ohne pf-reveal: Die eigenständige Seite lädt kein navigation.js, die Karte bliebe sonst unsichtbar.
?>
<main id="main" class="pf-main pf-error-shell">
    <div class="pf-container theme-v4-error">
        <div class="pf-error-card">
            <p class="pf-error-code pf-error-code--system" aria-hidden="true"><?php echo (int) $pfError['code']; ?></p>
            <h1><?php echo theme_v4_e($pfTitle); ?></h1>
            <p class="theme-v4-error__message"><?php echo theme_v4_e($pfMessage); ?></p>
            <?php if ($pfError['retry_after'] > 0) : ?>
            <p class="theme-v4-error__retry"><?php echo theme_v4_e(theme_v4_t(
                'Bitte versuche es in etwa ' . max(1, (int) ceil($pfError['retry_after'] / 60)) . ' Minute(n) erneut.',
                'Please try again in about ' . max(1, (int) ceil($pfError['retry_after'] / 60)) . ' minute(s).',
                $pfLocale
            )); ?></p>
            <?php endif; ?>
            <div class="pf-error-actions theme-v4-error__actions">
                <a href="<?php echo theme_v4_e($pfHome); ?>" class="pf-btn pf-btn-primary"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $pfLocale)); ?></a>
                <?php if ($pfBack !== '') : ?>
                <a href="<?php echo theme_v4_e($pfBack); ?>" class="pf-btn pf-btn-ghost"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $pfLocale)); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php
if ($pfError['standalone']) {
    theme_v4_standalone_close();
}
