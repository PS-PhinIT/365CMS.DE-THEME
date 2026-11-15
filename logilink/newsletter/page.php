<?php
/**
 * Theme-Override der Core-View `views/newsletter/page.php` (Newsletter-Anmeldung und Archiv /newsletter).
 *
 * Bindet die Core-Vorlage unverändert ein – alle Variablen des Controllers bleiben verfügbar
 * (Knowledgebase u. a. `$settings['show_category_sidebar']`, Newsletter `$newsletter` inkl. CSRF-/Honeypot-Formular).
 * theme_v4_render_module_view() passt nur den <main>-Landmark an den Theme-Rahmen an
 * (kein doppeltes <main>, Skip-Link-Ziel vorhanden) und setzt die Token-Brücke aus css/cms-v4.css.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$themeV4CoreView = rtrim((string) ABSPATH, '/\\') . '/views/newsletter/page.php';
if (!is_file($themeV4CoreView) || !function_exists('theme_v4_render_module_view')) {
    if (is_file($themeV4CoreView)) {
        include $themeV4CoreView;
    }
    return;
}

$themeV4Level = ob_get_level();
ob_start();
try {
    include $themeV4CoreView;
} catch (\Throwable $themeV4Error) {
    while (ob_get_level() > $themeV4Level) {
        ob_end_clean();
    }
    throw $themeV4Error;
}
echo theme_v4_render_module_view((string) ob_get_clean(), 'newsletter');
