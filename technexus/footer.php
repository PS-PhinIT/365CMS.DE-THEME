<?php
/**
 * TechNexus Theme – Footer Template
 *
 * @package TechNexus_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Footer in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_FOOTER_RENDERED')) {
    return;
}
define('CMS_THEME_FOOTER_RENDERED', true);

$tnTitle    = tn_site_title();
$tnTitleEsc = tn_html_attr($tnTitle);
$tnIsLogged = theme_is_logged_in();

$footerText = (string) tn_get_setting(
    'footer',
    'footer_text',
    'Die führende Plattform für IT-Experten, Tech-Teams und Softwarehäuser.'
);
$showTechLinks = filter_var(
    tn_get_setting('footer', 'show_tech_links', true),
    FILTER_VALIDATE_BOOLEAN
);
$copyrightTemplate = (string) tn_get_setting(
    'footer',
    'copyright_text',
    '&copy; {year} {site_title}. Alle Rechte vorbehalten.'
);

// Copyright-Vorlage aus dem Customizer: Entities wie &copy; bleiben erhalten, Markup wird escaped.
$copyrightText = str_replace(
    ['{year}', '{site_title}'],
    [gmdate('Y'), $tnTitleEsc],
    htmlspecialchars($copyrightTemplate, ENT_QUOTES, 'UTF-8', false)
);

// Standardlinks: nur Ziele, die als Route oder veröffentlichte Seite existieren (365CMS 4.0.0).
// /it-experts, /companies, /events und /jobs sind Plugin-/Theme-Ziele ohne Core-Route.
$tnNetworkLinks = theme_v4_filter_links([
    ['label' => 'IT-Experten', 'url' => theme_route_url('experts')],
    ['label' => 'Tech-Unternehmen', 'url' => theme_route_url('companies')],
    ['label' => 'Tech Events', 'url' => theme_route_url('events')],
    ['label' => 'IT Jobs', 'url' => theme_route_url('jobs')],
    // 365CMS 4.2.0: Community-Forum nur bei aktivem Modul (sonst leer und herausgefiltert).
    theme_v4_community_link(null, 'Community-Forum', 'Community forum') ?? ['label' => 'Community', 'url' => ''],
    ['label' => 'Downloads', 'url' => theme_route_url('downloads')],
    ['label' => 'Wissensdatenbank', 'url' => theme_route_url('kb')],
    ['label' => 'Newsletter', 'url' => theme_route_url('newsletter')],
    ['label' => 'Kontakt', 'url' => theme_route_url('contact')],
]);
ob_start();
theme_nav_menu('footer-nav');
$tnFooterNav = (string) ob_get_clean();
?>
    </div><!-- #content .site-content -->

    <footer id="colophon" class="site-footer">

        <div class="footer-content-band">
            <div class="footer-container">
                <div class="footer-widgets">

                    <div class="footer-widget">
                        <h2 class="footer-widget-title"><?php echo $tnTitleEsc; ?></h2>
                        <p><?php echo nl2br(tn_html_attr($footerText)); ?></p>
                    </div>

                    <?php if ($showTechLinks) : ?>
                    <?php if ($tnNetworkLinks !== []) : ?>
                    <nav class="footer-widget" aria-label="Netzwerk">
                        <h2 class="footer-widget-title">Netzwerk</h2>
                        <ul>
                            <?php foreach ($tnNetworkLinks as $tnLink) : ?>
                            <li><a href="<?php echo tn_html_attr($tnLink['url']); ?>"><?php echo tn_html_attr($tnLink['label']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <nav class="footer-widget" aria-label="Mitglieder">
                        <h2 class="footer-widget-title">Mitglieder</h2>
                        <ul>
                            <?php if ($tnIsLogged) : ?>
                                <li><a href="<?php echo tn_html_attr(theme_route_url('member')); ?>">Mein Dashboard</a></li>
                                <li><a href="<?php echo tn_html_attr(theme_route_url('member/profile')); ?>">Mein Profil</a></li>
                                <?php // Abmelden als CSRF-geschütztes POST-Formular (vorher GET-Link mit Token in der URL). ?>
                                <li><?php echo theme_v4_logout_form('Abmelden', 'theme-v4-link-button tn-footer-logout'); ?></li>
                            <?php else : ?>
                                <li><a href="<?php echo tn_html_attr(theme_route_url('login')); ?>">Anmelden</a></li>
                                <li><a href="<?php echo tn_html_attr(theme_route_url('register')); ?>">Registrieren</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php elseif (trim($tnFooterNav) !== '') : ?>
                    <nav class="footer-widget" aria-label="Footer-Navigation">
                        <?php echo $tnFooterNav; // theme_nav_menu(): escaped ?>
                    </nav>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-container footer-bottom-inner">
                <p class="footer-copyright"><?php echo $copyrightText; ?></p>
                <?php
                ob_start();
                theme_nav_menu('footer-legal');
                $tnLegalNav = (string) ob_get_clean();
                if (trim($tnLegalNav) !== '') : ?>
                    <nav class="footer-legal-nav" aria-label="Rechtliche Links">
                        <?php echo $tnLegalNav; // theme_nav_menu(): escaped ?>
                    </nav>
                <?php endif; ?>
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
