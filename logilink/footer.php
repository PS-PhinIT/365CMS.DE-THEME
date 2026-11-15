<?php
/**
 * LogiLink Theme – Footer Template
 *
 * @package LogiLink_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Footer in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_FOOTER_RENDERED')) {
    return;
}
define('CMS_THEME_FOOTER_RENDERED', true);

$llTitle    = ll_site_title();
$llTitleEsc = htmlspecialchars($llTitle, ENT_QUOTES, 'UTF-8');
$llSafe     = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

$llFooterText = (string) ll_get_setting(
    'footer',
    'footer_text',
    'Schnelle, zuverlässige Logistiklösungen für nationale und internationale Sendungen.'
);
$llCopyTpl    = (string) ll_get_setting(
    'footer',
    'copyright_text',
    '&copy; {year} {site_title}. Alle Rechte vorbehalten.'
);
// Copyright-Vorlage aus dem Customizer: Entities wie &copy; bleiben erhalten, Markup wird escaped.
$llCopyright = str_replace(
    ['{year}', '{site_title}'],
    [gmdate('Y'), $llTitleEsc],
    htmlspecialchars($llCopyTpl, ENT_QUOTES, 'UTF-8', false)
);

$llSiteUrl = ll_site_url();
// Standardspalten: nur Ziele verlinken, die als Route oder veröffentlichte Seite existieren (365CMS 4.0.0).
// /tracking, /partners und /routes sind Theme-Ziele ohne Core-Route.
$llFooterColumns = [
    'Service' => [
        ['label' => 'Sendung verfolgen', 'url' => theme_route_url('tracking')],
        ['label' => 'Spediteure', 'url' => theme_route_url('partners')],
        ['label' => 'Routen', 'url' => theme_route_url('routes')],
        ['label' => 'Downloads', 'url' => $llSiteUrl . '/downloads'],
        ['label' => 'Wissensdatenbank', 'url' => $llSiteUrl . '/kb'],
    ],
    'Konto' => [
        ['label' => 'Registrieren', 'url' => theme_route_url('register')],
        ['label' => 'Anmelden', 'url' => theme_route_url('login')],
        ['label' => 'Dashboard', 'url' => theme_route_url('member')],
        ['label' => 'Newsletter', 'url' => $llSiteUrl . '/newsletter'],
        ['label' => 'Kontakt', 'url' => $llSiteUrl . '/contact'],
    ],
];
ob_start();
theme_nav_menu('footer-nav');
$llFooterNav = (string) ob_get_clean();
$llHomeUrl     = $llSafe(theme_route_url('home'));
?>
    </div><!-- #content -->

    <footer id="ll-footer" class="ll-footer">
        <div class="ll-container">
            <div class="ll-footer-widgets">
                <div class="ll-footer-brand">
                    <a href="<?php echo $llHomeUrl; ?>" class="ll-brand-line">
                        <span aria-hidden="true">🚛</span>
                        <?php echo $llTitleEsc; ?>
                    </a>
                    <p><?php echo nl2br($llSafe($llFooterText)); ?></p>
                </div>
                <?php foreach ($llFooterColumns as $llColumnTitle => $llColumnLinks) :
                    $llColumnLinks = theme_v4_filter_links($llColumnLinks);
                    if ($llColumnLinks === []) {
                        continue;
                    } ?>
                <nav aria-label="<?php echo $llSafe($llColumnTitle); ?>">
                    <h2 class="ll-footer-heading"><?php echo $llSafe($llColumnTitle); ?></h2>
                    <ul>
                        <?php foreach ($llColumnLinks as $llLink) : ?>
                        <li><a href="<?php echo $llSafe($llLink['url']); ?>"><?php echo $llSafe($llLink['label']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <?php endforeach; ?>
                <?php if (trim($llFooterNav) !== '') : ?>
                <nav aria-label="<?php echo $llSafe(theme_v4_t('Fußzeilen-Navigation', 'Footer navigation')); ?>">
                    <h2 class="ll-footer-heading">Navigation</h2>
                    <?php echo $llFooterNav; // theme_nav_menu(): escaped ?>
                </nav>
                <?php endif; ?>
            </div>
        </div>

        <div class="ll-footer-bottom">
            <div class="ll-container ll-footer-bottom-inner">
                <div><?php echo $llCopyright; ?></div>
                <div class="ll-footer-legal">
                    <?php theme_nav_menu('footer-legal'); ?>
                </div>
            </div>
        </div>
    </footer>

</div><!-- #page -->
<?php
// before_footer (Theme-Scripts) und body_end (Core-Cookie-Consent, Web-Vitals, PhotoSwipe)
// laufen pro Request nur einmal – der Core löst sie ebenfalls aus.
\CMS\Hooks::doAction('before_footer');
\CMS\Hooks::doAction('body_end');
?>
</body>
</html>
