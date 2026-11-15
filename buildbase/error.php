<?php
declare(strict_types=1);

/**
 * BuildBase – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 * Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks, Datenbank und Customizer.
 * Sonst rendert der Core header.php/footer.php; header.php öffnet kein <main>, deshalb setzt die Vorlage es selbst.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann nur die 4.x-Helfer nachladen (ohne Datenbank).
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$bbError    = theme_v4_error_context(get_defined_vars());
$bbLocale   = theme_v4_locale($contentLocale ?? null);
$bbDefaults = theme_v4_error_texts($bbError['code'], $bbLocale);
$bbTitle    = $bbError['title'] !== '' ? $bbError['title'] : $bbDefaults['title'];
$bbMessage  = $bbError['message'] !== '' ? $bbError['message'] : $bbDefaults['message'];
$bbHome     = theme_v4_site_url() . '/';
$bbBack     = $bbError['standalone'] ? '' : theme_v4_safe_back_url();

if ($bbError['standalone']) {
    $bbVersion  = defined('BUILDBASE_THEME_VERSION') ? BUILDBASE_THEME_VERSION : '4.0.0';
    $bbThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $bbError['code'] . ' – ' . $bbTitle,
        [
            $bbThemeUrl . '/style.css?v=' . rawurlencode($bbVersion),
            $bbThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($bbVersion),
        ],
        $bbLocale,
        'bb-body'
    );
}
?>
<main id="main" class="bb-main bb-error-screen">
    <div class="bb-card bb-error-card theme-v4-error">
        <p class="bb-error-code bb-error-code--system theme-v4-error__code" aria-hidden="true"><?php echo (int) $bbError['code']; ?></p>
        <h1 class="bb-error-title"><?php echo theme_v4_e($bbTitle); ?></h1>
        <p class="bb-error-text theme-v4-error__message"><?php echo theme_v4_e($bbMessage); ?></p>
        <?php if ($bbError['retry_after'] > 0) : ?>
        <p class="bb-error-text theme-v4-error__retry"><?php echo theme_v4_e(theme_v4_t(
            'Bitte versuchen Sie es in etwa ' . max(1, (int) ceil($bbError['retry_after'] / 60)) . ' Minute(n) erneut.',
            'Please try again in about ' . max(1, (int) ceil($bbError['retry_after'] / 60)) . ' minute(s).',
            $bbLocale
        )); ?></p>
        <?php endif; ?>
        <div class="bb-error-actions theme-v4-error__actions">
            <a href="<?php echo theme_v4_e($bbHome); ?>" class="bb-btn bb-btn-primary"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $bbLocale)); ?></a>
            <?php if ($bbBack !== '') : ?>
            <a href="<?php echo theme_v4_e($bbBack); ?>" class="bb-btn bb-btn-outline"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $bbLocale)); ?></a>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php
if ($bbError['standalone']) {
    theme_v4_standalone_close();
}
