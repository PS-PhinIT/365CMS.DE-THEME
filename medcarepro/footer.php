<?php
/**
 * MedCare Pro Theme – Footer Template
 *
 * @package MedCarePro_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Footer in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_FOOTER_RENDERED')) {
    return;
}
define('CMS_THEME_FOOTER_RENDERED', true);

$mcTitle    = mc_site_title();
$mcTitleEsc = htmlspecialchars($mcTitle, ENT_QUOTES, 'UTF-8');
$mcIsLoggedIn = theme_is_logged_in();

$mcFooterText   = (string) mc_get_setting('footer',        'footer_text',          'Die Gesundheitsplattform für Ärzte und Patienten.');
$mcImprintTitle = (string) mc_get_setting('dsgvo_medical', 'imprint_doctor_title', '');
$mcCopyrightTpl = (string) mc_get_setting('footer',        'copyright_text',       '&copy; {year} {site_title}. Alle Rechte vorbehalten.');
$mcDisclaimer   = (string) mc_get_setting('footer',        'footer_disclaimer',    '');

// Copyright: Platzhalter ersetzen, dann escapen – vorhandene Entities wie &copy; bleiben erhalten
// (bisher wurde der Customizer-Text unescaped ausgegeben).
$mcCopyright = htmlspecialchars(
    str_replace(['{year}', '{site_title}'], [gmdate('Y'), $mcTitle], $mcCopyrightTpl),
    ENT_QUOTES,
    'UTF-8',
    false
);

// Standardspalten: nur Ziele, die als Route oder veröffentlichte Seite existieren (365CMS 4.0.0); leere Spalten entfallen.
$mcFooterColumns = [
    'Für Patienten' => [
        ['label' => 'Arzt suchen',         'url' => theme_route_url('doctors')],
        ['label' => 'Termin buchen',       'url' => theme_route_url('booking')],
        ['label' => 'Fachgebiete',         'url' => theme_route_url('fields')],
        ['label' => 'Gesundheitsratgeber', 'url' => theme_route_url('blog')],
        ['label' => 'Newsletter',          'url' => mc_href('/newsletter')],
        ['label' => 'Kontakt',             'url' => mc_href('/kontakt')],
    ],
    'Für Ärzte' => array_merge(
        $mcIsLoggedIn || !mc_registration_enabled() ? [] : [['label' => 'Profil anlegen', 'url' => theme_route_url('register')]],
        [['label' => 'Pakete & Preise', 'url' => mc_href('/pricing')]],
        $mcIsLoggedIn ? [] : [['label' => 'Anmelden', 'url' => theme_route_url('login')]]
    ),
    'Rechtliches' => [
        ['label' => 'Datenschutz',       'url' => theme_route_url('privacy')],
        ['label' => 'Impressum',         'url' => theme_route_url('imprint')],
        ['label' => 'AGB',               'url' => theme_route_url('terms')],
        ['label' => 'Cookie-Richtlinie', 'url' => theme_route_url('cookies')],
    ],
];
$mcLegalLinks = theme_v4_filter_links(array_slice($mcFooterColumns['Rechtliches'], 0, 3));
?>
    </div><!-- #content -->
    <footer id="mc-footer" class="mc-footer">
        <div class="mc-container">
            <div class="mc-footer-widgets">
                <div class="mc-footer-col mc-footer-col--brand">
                    <p class="mc-footer-brand"><?php echo $mcTitleEsc; ?></p>
                    <p class="mc-footer-text"><?php echo nl2br(htmlspecialchars($mcFooterText, ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php if ($mcImprintTitle !== '') : ?>
                        <p class="mc-footer-imprint"><?php echo htmlspecialchars($mcImprintTitle, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
                <?php foreach ($mcFooterColumns as $mcColumnTitle => $mcColumnLinks) :
                    $mcColumnLinks = theme_v4_filter_links($mcColumnLinks);
                    if ($mcColumnLinks === []) {
                        continue;
                    }
                    $mcColumnId = 'mc-footer-' . strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $mcColumnTitle));
                    ?>
                <nav class="mc-footer-col" aria-labelledby="<?php echo htmlspecialchars($mcColumnId, ENT_QUOTES, 'UTF-8'); ?>">
                    <h2 class="mc-footer-heading" id="<?php echo htmlspecialchars($mcColumnId, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($mcColumnTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <ul>
                        <?php foreach ($mcColumnLinks as $mcLink) :
                            $mcLinkUrl = mc_safe_url((string) $mcLink['url']);
                            $mcLinkActive = theme_v4_is_current_path($mcLinkUrl);
                            ?>
                        <li><a href="<?php echo htmlspecialchars($mcLinkUrl, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $mcLinkActive ? ' aria-current="page"' : ''; ?>><?php echo htmlspecialchars($mcLink['label'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <?php endforeach; ?>
            </div>
            <?php if (trim($mcDisclaimer) !== '') : ?>
            <div class="mc-footer-disclaimer" role="note">
                <p><?php echo nl2br(htmlspecialchars($mcDisclaimer, ENT_QUOTES, 'UTF-8')); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <div class="mc-footer-bottom">
            <div class="mc-container">
                <span class="mc-footer-copyright"><?php echo $mcCopyright; ?></span>
                <?php if ($mcLegalLinks !== []) : ?>
                <nav class="mc-footer-legal" aria-label="Rechtliche Links">
                    <?php foreach ($mcLegalLinks as $mcLink) : ?>
                        <a href="<?php echo htmlspecialchars(mc_safe_url((string) $mcLink['url']), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($mcLink['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endforeach; ?>
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
