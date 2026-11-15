<?php
declare(strict_types=1);

/**
 * Kompass – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler) zusätzlich `$error_standalone` und bei 503 `$error_retry_after`.
 *
 * - Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer, Hooks, Customizer
 *   und Datenbank; eigenes <main>.
 * - Sonst rendert der Core header.php/footer.php; header.php öffnet <main id="main-content"> bereits,
 *   deshalb hier nur der Inhalt.
 *
 * @package Kompass_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann ohne functions.php.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$kpError    = theme_v4_error_context(get_defined_vars());
$kpLocale   = theme_v4_locale($contentLocale ?? null);
$kpDefaults = theme_v4_error_texts($kpError['code'], $kpLocale);
$kpTitle    = $kpError['title'] !== '' ? $kpError['title'] : $kpDefaults['title'];
$kpMessage  = $kpError['message'] !== '' ? $kpError['message'] : $kpDefaults['message'];
$kpHome     = theme_v4_site_url() . '/';
$kpBack     = $kpError['standalone'] ? '' : theme_v4_safe_back_url();
$kpVersion  = defined('KOMPASS_THEME_VERSION') ? (string) KOMPASS_THEME_VERSION : '2.0.0';

if ($kpError['standalone']) {
    $kpThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $kpError['code'] . ' – ' . $kpTitle,
        [
            $kpThemeUrl . '/style.css?v=' . rawurlencode($kpVersion),
            $kpThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($kpVersion),
        ],
        $kpLocale,
        'kompass is-error'
    );
    echo '<main id="main-content" class="kp-main">' . "\n";
}
?>
<header class="kp-pagehead kp-pagehead--center">
    <div class="kp-container kp-notfound">
        <p class="kp-eyebrow"><?php echo theme_v4_e(theme_v4_t('Fehler', 'Error', $kpLocale) . ' ' . $kpError['code']); ?></p>
        <h1 class="kp-pagehead__title"><?php echo theme_v4_e($kpTitle); ?></h1>
        <p class="kp-pagehead__lead"><?php echo theme_v4_e($kpMessage); ?></p>
        <?php if ($kpError['retry_after'] > 0) : ?>
            <p class="kp-pagehead__lead"><?php echo theme_v4_e(theme_v4_t(
                'Bitte versuchen Sie es in etwa ' . max(1, (int) ceil($kpError['retry_after'] / 60)) . ' Minute(n) erneut.',
                'Please try again in about ' . max(1, (int) ceil($kpError['retry_after'] / 60)) . ' minute(s).',
                $kpLocale
            )); ?></p>
        <?php endif; ?>
        <p class="kp-notfound__actions">
            <a class="kp-button kp-button--primary" href="<?php echo theme_v4_e($kpHome); ?>"><span><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $kpLocale)); ?></span></a>
            <?php if ($kpBack !== '') : ?>
                <a class="kp-button kp-button--ghost" href="<?php echo theme_v4_e($kpBack); ?>"><span><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $kpLocale)); ?></span></a>
            <?php endif; ?>
        </p>
    </div>
</header>
<?php
if ($kpError['standalone']) {
    echo "</main>\n";
    theme_v4_standalone_close();
}
