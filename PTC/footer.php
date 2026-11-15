<?php
/**
 * PTC Theme – Footer
 *
 * @package PTC_Theme
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

$siteUrl   = ptc_site_url();
$siteTitle = ptc_site_title();

// ── Customizer-Werte ─────────────────────────────────────────────────────────
$tagline   = (string) ptc_customizer_get('footer', 'footer_tagline', 'Ihr Partner für Personalvermittlung, Zeitarbeit und berufliche Weiterbildung.');
$address   = (string) ptc_customizer_get('footer', 'footer_address', '');
$phone     = (string) ptc_customizer_get('footer', 'footer_phone', '');
$email     = (string) ptc_customizer_get('footer', 'footer_email', '');

// Copyright mit Platzhaltern
$copyrightTpl  = (string) ptc_customizer_get('footer', 'copyright_text', '© {year} {site_title}. Alle Rechte vorbehalten.');
// Vorlage escapen, dann Platzhalter einsetzen ($siteTitle ist bereits escaped).
$copyrightText = str_replace(
    ['{year}', '{site_title}'],
    [gmdate('Y'), $siteTitle],
    htmlspecialchars($copyrightTpl, ENT_QUOTES, 'UTF-8')
);

// Footer-Breite: full = volle Breite, content = gleich wie Content (Standard)
$footerWidth = (string) ptc_customizer_get('footer', 'footer_width', 'full');
$footerClass = 'ptc-footer' . ($footerWidth === 'full' ? ' ptc-footer--full' : '');
$networkBarClass = 'ptc-network-bar' . ($footerWidth === 'full' ? ' ptc-network-bar--full' : '');

// Social-Links
$socialLinks = [
    'facebook'  => ['url' => (string) ptc_customizer_get('footer', 'social_facebook', ''),  'label' => 'Facebook',  'icon' => 'f'],
    'instagram' => ['url' => (string) ptc_customizer_get('footer', 'social_instagram', ''), 'label' => 'Instagram', 'icon' => '📷'],
    'linkedin'  => ['url' => (string) ptc_customizer_get('footer', 'social_linkedin', ''),  'label' => 'LinkedIn',  'icon' => 'in'],
    'xing'      => ['url' => (string) ptc_customizer_get('footer', 'social_xing', ''),      'label' => 'Xing',      'icon' => 'X'],
    'youtube'   => ['url' => (string) ptc_customizer_get('footer', 'social_youtube', ''),    'label' => 'YouTube',   'icon' => '▶'],
];
// Nur http(s)-Ziele (kein javascript:/data:).
foreach ($socialLinks as $key => $social) {
    $socialLinks[$key]['url'] = preg_match('#^https?://#i', $social['url']) === 1 ? ptc_safe_url($social['url']) : '';
}
$activeSocials = array_filter($socialLinks, fn($s) => $s['url'] !== '');

// Footer-Menüspalten vorab rendern: leere Spalten (kein Menü, keine vorhandenen Standardziele) entfallen.
$ptcFooterMenu = static function (string $location): string {
    ob_start();
    ptc_nav_menu($location);
    return trim((string) ob_get_clean());
};
$footerNavHtml      = $ptcFooterMenu('footer-nav');
$footerServicesHtml = $ptcFooterMenu('footer-services');
$footerLegalHtml    = $ptcFooterMenu('footer-legal');
$hasContact         = $address !== '' || $phone !== '' || filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
?>
    </main><!-- /#main-content -->

    <?php \CMS\Hooks::doAction('before_footer'); ?>

    <footer class="<?php echo $footerClass; ?>" id="ptc-footer">
        <div class="ptc-container">
            <div class="ptc-footer-grid">

                <!-- Brand -->
                <div class="ptc-footer-brand">
                    <?php
                    $footerLogo = theme_v4_site_logo(ptc_safe_url((string) ptc_customizer_get('header', 'logo_url', '')));
                    ?>
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>/" class="ptc-logo ptc-logo--footer" rel="home">
                        <?php if ($footerLogo !== ''): ?>
                            <?php echo theme_v4_img($footerLogo, html_entity_decode($siteTitle, ENT_QUOTES, 'UTF-8'), ['class' => 'ptc-logo-img ptc-logo-img--footer']); ?>
                        <?php else: ?>
                            <svg width="28" height="28" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                <rect width="36" height="36" rx="8" fill="var(--color-accent)"/>
                                <path d="M9 11h6v2H9zm0 5h6v2H9zm0 5h4v2H9zm10-10h8v2h-8zm0 5h6v2h-6zm0 5h5v2h-5z" fill="var(--color-primary)"/>
                            </svg>
                            <span class="ptc-logo-text"><?php echo $siteTitle; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($tagline !== ''): ?>
                        <p><?php echo htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($activeSocials)): ?>
                        <div class="ptc-social">
                            <?php foreach ($activeSocials as $social): ?>
                                <a href="<?php echo htmlspecialchars($social['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                   aria-label="<?php echo htmlspecialchars($social['label'] . ' (öffnet in neuem Fenster)', ENT_QUOTES, 'UTF-8'); ?>"
                                   target="_blank" rel="noopener noreferrer">
                                    <span aria-hidden="true"><?php echo htmlspecialchars($social['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Navigation -->
                <?php if ($footerNavHtml !== ''): ?>
                <nav class="ptc-footer-col" aria-labelledby="ptc-footer-nav-title">
                    <h2 class="ptc-footer-heading" id="ptc-footer-nav-title">Navigation</h2>
                    <?php echo $footerNavHtml; // theme_nav_menu(): alle Werte escaped ?>
                </nav>
                <?php endif; ?>

                <!-- Dienstleistungen -->
                <?php if ($footerServicesHtml !== ''): ?>
                <nav class="ptc-footer-col" aria-labelledby="ptc-footer-services-title">
                    <h2 class="ptc-footer-heading" id="ptc-footer-services-title">Dienstleistungen</h2>
                    <?php echo $footerServicesHtml; // theme_nav_menu(): alle Werte escaped ?>
                </nav>
                <?php endif; ?>

                <!-- Kontakt -->
                <?php if ($hasContact): ?>
                <div class="ptc-footer-col">
                    <h2 class="ptc-footer-heading">Kontakt</h2>
                    <ul class="ptc-footer-contact">
                        <?php if ($address !== ''): ?>
                            <li class="ptc-footer-address"><?php echo nl2br(htmlspecialchars($address, ENT_QUOTES, 'UTF-8')); ?></li>
                        <?php endif; ?>
                        <?php if ($phone !== ''): ?>
                            <li><span aria-hidden="true">📞</span> <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^+0-9]/', '', $phone), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>
                            </a></li>
                        <?php endif; ?>
                        <?php if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false): ?>
                            <li><span aria-hidden="true">✉️</span> <a href="mailto:<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>

            </div>

            <!-- Bottom Bar -->
            <div class="ptc-footer-bottom">
                <span><?php echo $copyrightText; // oben escaped ?></span>
                <?php if ($footerLegalHtml !== ''): ?>
                <nav class="ptc-footer-legal" aria-label="Rechtliche Links">
                    <?php echo $footerLegalHtml; // theme_nav_menu(): alle Werte escaped ?>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <?php \CMS\Hooks::doAction('footer'); ?>

    <?php
    // ── Network Bar ──────────────────────────────────────────────────────────
    $showNetworkBar = filter_var(ptc_customizer_get('footer', 'show_network_bar', true), FILTER_VALIDATE_BOOLEAN);
    if ($showNetworkBar):
        $nbName = trim((string) ptc_customizer_get('footer', 'network_bar_name', ''));
        $nbLinks = [];
        for ($i = 1; $i <= 3; $i++) {
            $lbl = trim((string) ptc_customizer_get('footer', "network_bar_link{$i}_label", ''));
            $url = ptc_safe_url(trim((string) ptc_customizer_get('footer', "network_bar_link{$i}_url", '')));
            if ($lbl !== '' && $url !== '' && !str_starts_with($url, '#')) {
                $nbLinks[] = ['label' => $lbl, 'url' => $url];
            }
        }
    ?>
    <div class="<?php echo $networkBarClass; ?>">
        <div class="ptc-container">
            <div class="ptc-network-inner">
                <?php if ($nbName !== ''): ?>
                    <span class="ptc-network-label"><?php echo htmlspecialchars($nbName, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
                <?php if (!empty($nbLinks)): ?>
                    <div class="ptc-network-links">
                        <?php foreach ($nbLinks as $link): ?>
                            <a href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>"
                               target="_blank" rel="noopener noreferrer">
                                <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?><span class="theme-v4-sr-only"> (öffnet in neuem Fenster)</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /.ptc-site -->

<?php \CMS\Hooks::doAction('body_end'); ?>

</body>
</html>
