<?php
declare(strict_types=1);

/**
 * Business Theme – Footer
 *
 * @package IT_Business_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Footer in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_FOOTER_RENDERED')) {
    return;
}
define('CMS_THEME_FOOTER_RENDERED', true);

$siteUrl   = biz_site_url();
$siteTitle = biz_site_title();
$tagline   = (string) biz_config('footer_tagline', 'Ihr Partner für digitale Innovation.');

try {
    $copyRaw = (string) \CMS\Services\ThemeCustomizer::instance()->get(
        'footer',
        'footer_copyright',
        '© {year} {site_title}. Alle Rechte vorbehalten.'
    );
} catch (\Throwable) {
    $copyRaw = '© {year} {site_title}. Alle Rechte vorbehalten.';
}
$copyText = htmlspecialchars(
    strtr($copyRaw, [
        '{year}'       => gmdate('Y'),
        '{site_title}' => \CMS\ThemeManager::instance()->getSiteTitle(),
    ]),
    ENT_QUOTES,
    'UTF-8'
);

// Spalten vorab rendern: Menüs (bzw. Standardeinträge) und Standardlinks nur mit vorhandenen Zielen,
// leere Spalten entfallen (365CMS 4.0.0).
$bizFooterMenu = static function (string $location): string {
    ob_start();
    biz_nav_menu($location);
    return trim((string) ob_get_clean());
};
$bizFooterNavHtml   = $bizFooterMenu('footer-nav');
$bizFooterLegalHtml = $bizFooterMenu('footer-legal');
$bizServiceLinks = theme_v4_filter_links([
    ['label' => 'Beratung',    'url' => biz_href('#leistungen')],
    ['label' => 'Entwicklung', 'url' => biz_href('#leistungen')],
    ['label' => 'Support',     'url' => biz_href('#leistungen')],
    ['label' => 'Schulungen',  'url' => biz_href('#leistungen')],
    ['label' => 'Downloads',   'url' => biz_href('/downloads')],
]);
$bizContactLinks = theme_v4_filter_links([
    ['label' => 'Kontaktformular', 'url' => biz_href('/contact')],
    ['label' => 'Newsletter',      'url' => biz_href('/newsletter')],
    ['label' => 'Impressum',       'url' => biz_href('/impressum')],
    ['label' => 'Datenschutz',     'url' => biz_href('/datenschutz')],
]);
?>
    </main><!-- /#main-content -->

    <footer class="biz-footer" id="biz-footer">
        <div class="biz-container">
            <div class="biz-footer-grid">

                <!-- Brand -->
                <div class="biz-footer-brand">
                    <a href="<?php echo htmlspecialchars(biz_href('/'), ENT_QUOTES, 'UTF-8'); ?>" class="biz-logo biz-footer-brand-link biz-focus-shadow" rel="home">
                        <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"
                             aria-hidden="true" focusable="false" class="biz-footer-brand-icon">
                            <rect width="32" height="32" rx="6" fill="#c08a2e"/>
                            <path d="M8 10h4v12H8V10zm6 0h4v12h-4V10zm6 4h4v8h-4v-8z" fill="#0c1320"/>
                        </svg>
                        <span class="biz-logo-text"><?php echo $siteTitle; ?></span>
                    </a>
                    <p><?php echo htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php // Die Social-Icons verlinkten bisher fest auf „#“ (ohne Ziel) und sind entfernt. ?>
                </div>

                <!-- Navigation -->
                <?php if ($bizFooterNavHtml !== '') : ?>
                <nav class="biz-footer-col" aria-labelledby="biz-footer-nav-title">
                    <h2 class="biz-footer-heading" id="biz-footer-nav-title">Navigation</h2>
                    <?php echo $bizFooterNavHtml; // biz_nav_menu(): alle Werte escaped ?>
                </nav>
                <?php endif; ?>

                <!-- Services -->
                <?php if ($bizServiceLinks !== []) : ?>
                <div class="biz-footer-col">
                    <h2 class="biz-footer-heading">Leistungen</h2>
                    <ul>
                        <?php foreach ($bizServiceLinks as $bizLink) : ?>
                        <li><a href="<?php echo htmlspecialchars($bizLink['url'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($bizLink['label'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Kontakt -->
                <?php if ($bizContactLinks !== []) : ?>
                <div class="biz-footer-col">
                    <h2 class="biz-footer-heading">Kontakt</h2>
                    <ul>
                        <?php foreach ($bizContactLinks as $bizLink) : ?>
                        <li><a href="<?php echo htmlspecialchars($bizLink['url'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($bizLink['label'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

            </div>

            <!-- Bottom Bar -->
            <div class="biz-footer-bottom">
                <span><?php echo $copyText; ?></span>
                <?php if ($bizFooterLegalHtml !== '') : ?>
                <nav class="biz-footer-legal" aria-label="Rechtliche Links">
                    <?php echo $bizFooterLegalHtml; // biz_nav_menu(): alle Werte escaped ?>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </footer>

</div><!-- /.biz-site -->

<?php
// before_footer (Theme-Scripts) und body_end (Core-Cookie-Consent, Web-Vitals, PhotoSwipe)
// laufen pro Request nur einmal – der Core löst sie ebenfalls aus.
\CMS\Hooks::doAction('before_footer');
\CMS\Hooks::doAction('body_end');
?>

</body>
</html>
