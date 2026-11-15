<?php
/**
 * MedCare Pro – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler in index.php) zusätzlich `$error_standalone` und bei 503
 * `$error_retry_after`. Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer,
 * Hooks, Datenbank und Customizer – deshalb ohne functions.php lauffähig (bisher rief die Seite selbst
 * get_header()/get_footer() auf und lud im Fatal-Handler functions.php samt Datenbankzugriffen).
 * Im Seitenrahmen rendert der Core header.php/footer.php; header.php öffnet kein <main>, deshalb setzt
 * diese Vorlage <main id="main"> in beiden Fällen selbst.
 *
 * @package MedCarePro_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann die gemeinsame 4.x-Schicht nachladen.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$mcError    = theme_v4_error_context(get_defined_vars());
$mcLocale   = theme_v4_locale($contentLocale ?? null);
$mcDefaults = theme_v4_error_texts($mcError['code'], $mcLocale);
$mcTitle    = $mcError['title'] !== '' ? $mcError['title'] : $mcDefaults['title'];
$mcMessage  = $mcError['message'] !== '' ? $mcError['message'] : $mcDefaults['message'];
$mcHome     = theme_v4_site_url() . '/';
$mcBack     = $mcError['standalone'] ? '' : theme_v4_safe_back_url();
$mcVersion  = defined('MEDCAREPRO_THEME_VERSION') ? (string) MEDCAREPRO_THEME_VERSION : '2.0.0';

if ($mcError['standalone']) {
    $mcThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $mcError['code'] . ' – ' . $mcTitle,
        [
            $mcThemeUrl . '/style.css?v=' . rawurlencode($mcVersion),
            $mcThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($mcVersion),
        ],
        $mcLocale,
        'mc-body no-sticky-header'
    );
}
?>
<main id="main" class="mc-main mc-status-page">
    <div class="mc-container mc-status-page__container">
        <div class="mc-card mc-status-card is-visible">
            <p class="mc-status-code mc-status-code--error" aria-hidden="true"><?php echo (int) $mcError['code']; ?></p>
            <h1 class="mc-status-title"><?php echo theme_v4_e($mcTitle); ?></h1>
            <p class="mc-status-text"><?php echo theme_v4_e($mcMessage); ?></p>
            <?php if ($mcError['retry_after'] > 0) : ?>
            <p class="mc-status-text"><?php echo theme_v4_e(theme_v4_t(
                'Bitte versuchen Sie es in etwa ' . max(1, (int) ceil($mcError['retry_after'] / 60)) . ' Minute(n) erneut.',
                'Please try again in about ' . max(1, (int) ceil($mcError['retry_after'] / 60)) . ' minute(s).',
                $mcLocale
            )); ?></p>
            <?php endif; ?>
            <div class="mc-status-actions">
                <a href="<?php echo theme_v4_e($mcHome); ?>" class="mc-btn mc-btn-primary"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $mcLocale)); ?></a>
                <?php if ($mcBack !== '') : ?>
                <a href="<?php echo theme_v4_e($mcBack); ?>" class="mc-btn mc-btn-outline"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $mcLocale)); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php
if ($mcError['standalone']) {
    theme_v4_standalone_close();
}
