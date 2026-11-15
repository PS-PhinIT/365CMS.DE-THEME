<?php
/**
 * LogiLink Theme – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0).
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 * Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks und Datenbank.
 *
 * @package LogiLink_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – Theme-Helfer dann nachladen.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$llError    = theme_v4_error_context(get_defined_vars());
$llLocale   = theme_v4_locale($contentLocale ?? null);
$llDefaults = theme_v4_error_texts($llError['code'], $llLocale);
$llTitle    = $llError['title'] !== '' ? $llError['title'] : $llDefaults['title'];
$llMessage  = $llError['message'] !== '' ? $llError['message'] : $llDefaults['message'];
$llHome     = theme_v4_site_url() . '/';
$llBack     = $llError['standalone'] ? '' : theme_v4_safe_back_url();
$llVersion  = defined('LOGILINK_THEME_VERSION') ? LOGILINK_THEME_VERSION : '2.0.0';

if ($llError['standalone']) {
    $llThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $llError['code'] . ' – ' . $llTitle,
        [$llThemeUrl . '/style.css?v=' . rawurlencode($llVersion), $llThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($llVersion)],
        $llLocale,
        'll-body'
    );
}
?>
<main id="main" class="ll-main ll-error-wrap">
    <div class="ll-container theme-v4-error">
        <div class="ll-card ll-error-card">
            <p class="ll-error-code ll-error-code--alert" aria-hidden="true"><?php echo (int) $llError['code']; ?></p>
            <h1><?php echo theme_v4_e($llTitle); ?></h1>
            <p class="theme-v4-error__message"><?php echo theme_v4_e($llMessage); ?></p>
            <?php if ($llError['retry_after'] > 0) : ?>
            <p class="theme-v4-error__retry"><?php echo theme_v4_e(theme_v4_t(
                'Bitte versuche es in etwa ' . max(1, (int) ceil($llError['retry_after'] / 60)) . ' Minute(n) erneut.',
                'Please try again in about ' . max(1, (int) ceil($llError['retry_after'] / 60)) . ' minute(s).',
                $llLocale
            )); ?></p>
            <?php endif; ?>
            <div class="ll-error-actions theme-v4-error__actions">
                <a href="<?php echo theme_v4_e($llHome); ?>" class="ll-btn ll-btn-primary"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $llLocale)); ?></a>
                <?php if ($llBack !== '') : ?>
                <a href="<?php echo theme_v4_e($llBack); ?>" class="ll-btn ll-btn-ghost"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $llLocale)); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php
if ($llError['standalone']) {
    theme_v4_standalone_close();
}
