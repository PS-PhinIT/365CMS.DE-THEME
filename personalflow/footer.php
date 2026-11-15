<?php
/**
 * PersonalFlow Theme – Footer Template
 *
 * @package PersonalFlow_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Footer in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_FOOTER_RENDERED')) {
    return;
}
define('CMS_THEME_FOOTER_RENDERED', true);

$pfTitle    = pf_site_title();
$pfTitleEsc = htmlspecialchars($pfTitle, ENT_QUOTES, 'UTF-8');

$pfFooterText        = (string) pf_get_setting('footer', 'footer_text', 'Die HR-Plattform für Talent-Matching und strukturierte Recruiting-Pipelines.');
$pfShowPipelineLinks = filter_var(pf_get_setting('footer', 'show_pipeline_links', true), FILTER_VALIDATE_BOOLEAN);
$pfShowEmployerLinks = filter_var(pf_get_setting('footer', 'show_employer_links', true), FILTER_VALIDATE_BOOLEAN);
$pfCopyrightTpl      = (string) pf_get_setting('footer', 'copyright_text', '&copy; {year} {site_title}. Alle Rechte vorbehalten.');

// Copyright-Vorlage aus dem Customizer: Entities wie &copy; bleiben erhalten, Markup wird escaped.
$pfCopyright = str_replace(
    ['{year}', '{site_title}'],
    [gmdate('Y'), $pfTitleEsc],
    htmlspecialchars($pfCopyrightTpl, ENT_QUOTES, 'UTF-8', false)
);
$pfSafe = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

// Standardspalten: nur Ziele verlinken, die als Route oder veröffentlichte Seite existieren (365CMS 4.0.0).
// /jobs, /kandidaten, /arbeitgeber, /pipeline und /preise sind Theme-Ziele ohne Core-Route.
$pfFooterColumns = [];
if ($pfShowPipelineLinks) {
    $pfFooterColumns['Für Kandidaten'] = [
        ['label' => 'Offene Stellen', 'url' => theme_route_url('jobs')],
        ['label' => 'Profil anlegen', 'url' => theme_route_url('register') . '?type=candidate'],
        ['label' => 'Bewerbungs-Pipeline', 'url' => theme_route_url('pipeline')],
    ];
}
if ($pfShowEmployerLinks) {
    $pfFooterColumns['Für Arbeitgeber'] = [
        ['label' => 'Talente entdecken', 'url' => theme_route_url('candidates')],
        ['label' => 'Arbeitgeber-Login', 'url' => theme_route_url('employers')],
        ['label' => 'Preise & Pakete', 'url' => theme_route_url('pricing')],
    ];
}
$pfFooterColumns['Wissen & Kontakt'] = [
    ['label' => 'Downloads', 'url' => pf_site_url() . '/downloads'],
    ['label' => 'Wissensdatenbank', 'url' => pf_site_url() . '/kb'],
    ['label' => 'Newsletter', 'url' => pf_site_url() . '/newsletter'],
    ['label' => 'Kontakt', 'url' => pf_site_url() . '/contact'],
];
ob_start();
theme_nav_menu('footer-nav');
$pfFooterNav = (string) ob_get_clean();
?>
    </div><!-- #content .pf-site-content -->

    <footer id="pfFooter" class="pf-footer">
        <div class="pf-footer-content">
            <div class="pf-footer-container">
                <div class="pf-footer-widgets">

                    <div class="pf-footer-widget">
                        <h2 class="pf-footer-heading"><?php echo $pfTitleEsc; ?></h2>
                        <p><?php echo nl2br(htmlspecialchars($pfFooterText, ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>

                    <?php foreach ($pfFooterColumns as $pfColumnTitle => $pfColumnLinks) :
                        $pfColumnLinks = theme_v4_filter_links($pfColumnLinks);
                        if ($pfColumnLinks === []) {
                            continue;
                        } ?>
                        <nav class="pf-footer-widget" aria-label="<?php echo $pfSafe($pfColumnTitle); ?>">
                            <h2 class="pf-footer-heading"><?php echo $pfSafe($pfColumnTitle); ?></h2>
                            <ul>
                                <?php foreach ($pfColumnLinks as $pfLink) : ?>
                                <li><a href="<?php echo $pfSafe($pfLink['url']); ?>"><?php echo $pfSafe($pfLink['label']); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    <?php endforeach; ?>

                    <?php if (trim($pfFooterNav) !== '') : ?>
                    <nav class="pf-footer-widget" aria-label="Service">
                        <h2 class="pf-footer-heading">Service</h2>
                        <?php echo $pfFooterNav; // theme_nav_menu(): escaped ?>
                    </nav>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <div class="pf-footer-bottom">
            <div class="pf-footer-container">
                <div class="pf-footer-bottom-row">
                    <div><?php echo $pfCopyright; ?></div>
                    <nav class="pf-footer-legal-nav" aria-label="Rechtliche Links">
                        <?php theme_nav_menu('footer-legal'); ?>
                    </nav>
                </div>
            </div>
        </div>
    </footer><!-- #pfFooter -->

</div><!-- #page -->

<?php
// before_footer (Theme-Scripts) und body_end (Core-Cookie-Consent, Web-Vitals, PhotoSwipe)
// laufen pro Request nur einmal – der Core löst sie ebenfalls aus.
\CMS\Hooks::doAction('before_footer');
\CMS\Hooks::doAction('body_end');
?>
</body>
</html>
