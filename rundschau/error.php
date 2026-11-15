<?php
declare(strict_types=1);

/**
 * Rundschau – Fehlerseite für alle Status außer 404 (Router::renderError(), 365CMS 4.0.0)
 *
 * Router-Vertrag: `$error_code` (Fallback `$errorCode`), `$error_title`, `$error_message`;
 * eigenständig (Wartungsmodus, Fatal-Handler in index.php) zusätzlich `$error_standalone` und bei 503
 * `$error_retry_after`. Eigenständig: vollständiges Dokument mit Theme-Stylesheet, ohne Header/Footer,
 * Hooks, Datenbank und Customizer – deshalb auch ohne functions.php lauffähig.
 * Sonst rendert der Core header.php/footer.php drumherum; header.php öffnet bereits <main id="main-content">.
 *
 * @package Rundschau_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auch vom Fatal-Handler (index.php) direkt eingebunden – dann die gemeinsame 4.x-Schicht nachladen.
if (!function_exists('theme_v4_error_context')) {
    require_once __DIR__ . '/includes/cms-v4-compat.php';
}

$rsError    = theme_v4_error_context(get_defined_vars());
$rsLocale   = theme_v4_locale($contentLocale ?? null);
$rsDefaults = theme_v4_error_texts($rsError['code'], $rsLocale);
$rsTitle    = $rsError['title'] !== '' ? $rsError['title'] : $rsDefaults['title'];
$rsMessage  = $rsError['message'] !== '' ? $rsError['message'] : $rsDefaults['message'];
$rsHome     = theme_v4_site_url() . '/';
$rsBack     = $rsError['standalone'] ? '' : theme_v4_safe_back_url();
$rsVersion  = defined('RUNDSCHAU_THEME_VERSION') ? (string) RUNDSCHAU_THEME_VERSION : '2.0.0';

if ($rsError['standalone']) {
    $rsThemeUrl = theme_v4_theme_url(__DIR__);
    theme_v4_standalone_open(
        $rsError['code'] . ' – ' . $rsTitle,
        [
            $rsThemeUrl . '/style.css?v=' . rawurlencode($rsVersion),
            $rsThemeUrl . '/css/cms-v4.css?v=' . rawurlencode($rsVersion),
        ],
        $rsLocale,
        'rundschau is-error'
    );
    echo '<main id="main-content" class="rs-main">' . "\n";
}
?>
<header class="rs-pagehead rs-pagehead--error">
    <div class="rs-container">
        <p class="rs-pagehead__kicker"><?php echo theme_v4_e(theme_v4_t('Fehler', 'Error', $rsLocale) . ' ' . $rsError['code']); ?></p>
        <h1 class="rs-pagehead__title"><?php echo theme_v4_e($rsTitle); ?></h1>
        <p class="rs-pagehead__text"><?php echo theme_v4_e($rsMessage); ?></p>
        <?php if ($rsError['retry_after'] > 0) : ?>
            <p class="rs-pagehead__text"><?php echo theme_v4_e(theme_v4_t(
                'Bitte versuchen Sie es in etwa ' . max(1, (int) ceil($rsError['retry_after'] / 60)) . ' Minute(n) erneut.',
                'Please try again in about ' . max(1, (int) ceil($rsError['retry_after'] / 60)) . ' minute(s).',
                $rsLocale
            )); ?></p>
        <?php endif; ?>
        <p class="rs-pagehead__actions">
            <a class="rs-button" href="<?php echo theme_v4_e($rsHome); ?>"><?php echo theme_v4_e(theme_v4_t('Zur Startseite', 'Back to home', $rsLocale)); ?></a>
            <?php if ($rsBack !== '') : ?>
                <a class="rs-button rs-button--ghost" href="<?php echo theme_v4_e($rsBack); ?>"><?php echo theme_v4_e(theme_v4_t('Zurück', 'Back', $rsLocale)); ?></a>
            <?php endif; ?>
        </p>
    </div>
</header>
<?php
if ($rsError['standalone']) {
    echo "</main>\n";
    theme_v4_standalone_close();
}
