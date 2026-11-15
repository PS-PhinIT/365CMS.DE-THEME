<?php
/**
 * Academy365 – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0).
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 * Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks und Datenbank.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – Theme-Helfer dann nachladen.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$acError    = theme_v4_error_context(get_defined_vars());
$acLocale   = theme_v4_locale($contentLocale ?? null);
$acDefaults = theme_v4_error_texts($acError['code'], $acLocale);
$acTitle    = $acError['title'] !== '' ? $acError['title'] : $acDefaults['title'];
$acMessage  = $acError['message'] !== '' ? $acError['message'] : $acDefaults['message'];
$acHome     = theme_v4_site_url() . '/';
$acBack     = $acError['standalone'] ? '' : theme_v4_safe_back_url();
$acVersion  = defined('ACADEMY365_THEME_VERSION') ? ACADEMY365_THEME_VERSION : '4.0.0';

if ($acError['standalone']) {
    $acThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $acError['code'] . ' – ' . $acTitle,
        [$acThemeUrl . '/style.css?v=' . rawurlencode($acVersion), $acThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($acVersion)],
        $acLocale,
        'ac-body'
    );
}
?>
<main id="main" class="ac-main-content" <?php echo $acError['standalone'] ? '' : 'role="main"'; ?>>
    <div class="ac-container ac-error-page theme-v4-error">
        <p class="ac-error-code theme-v4-error__code" aria-hidden="true"><?php echo (int) $acError['code']; ?></p>
        <h1><?php echo theme_v4_e($acTitle); ?></h1>
        <p class="ac-muted theme-v4-error__message"><?php echo theme_v4_e($acMessage); ?></p>
        <?php if ($acError['retry_after'] > 0) : ?>
        <p class="theme-v4-error__retry"><?php echo theme_v4_e(theme_v4_t(
            'Bitte versuche es in etwa ' . max(1, (int) ceil($acError['retry_after'] / 60)) . ' Minute(n) erneut.',
            'Please try again in about ' . max(1, (int) ceil($acError['retry_after'] / 60)) . ' minute(s).',
            $acLocale
        )); ?></p>
        <?php endif; ?>
        <div class="ac-error-actions theme-v4-error__actions">
            <a href="<?php echo theme_v4_e($acHome); ?>" class="ac-btn ac-btn-primary"><?php echo theme_v4_e(theme_v4_t('Zurück zur Startseite', 'Back to home', $acLocale)); ?></a>
            <?php if ($acBack !== '') : ?>
            <a href="<?php echo theme_v4_e($acBack); ?>" class="ac-btn ac-btn-ghost"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $acLocale)); ?></a>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php
if ($acError['standalone']) {
    theme_v4_standalone_close();
}
