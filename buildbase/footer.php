<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Der Core rendert den Footer in ThemeManager::render(); doppelte Einbindung verhindern.
if (defined('CMS_THEME_FOOTER_RENDERED')) {
    return;
}
define('CMS_THEME_FOOTER_RENDERED', true);

$themeManager = \CMS\ThemeManager::instance();
$siteTitle    = (string) $themeManager->getSiteTitle();
$isLoggedIn   = theme_is_logged_in();
$siteUrl      = rtrim(buildbase_safe_url((string) SITE_URL, '/'), '/');
$siteUrl      = $siteUrl !== '' ? $siteUrl : '/';

try {
    $c            = \CMS\Services\ThemeCustomizer::instance();
    $footerText   = (string) $c->get('footer', 'footer_text', 'Qualität aus Handwerkerhand.');
    $showLinks    = filter_var($c->get('footer', 'footer_show_crafts', true), FILTER_VALIDATE_BOOLEAN);
    $copyrightTpl = (string) $c->get('footer', 'copyright_text', '&copy; {year} {site_title}. Alle Rechte vorbehalten.');
} catch (\Throwable $e) {
    $footerText   = '';
    $showLinks    = true;
    $copyrightTpl = '&copy; {year} {site_title}.';
}

$safe = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$bbLocale = theme_v4_locale($contentLocale ?? null);
// Unveränderter deutscher Standardtext: auf englischen Seiten die englische Fassung zeigen (eigene Texte bleiben unberührt).
if ($bbLocale === 'en' && $copyrightTpl === '&copy; {year} {site_title}. Alle Rechte vorbehalten.') {
    $copyrightTpl = '&copy; {year} {site_title}. All rights reserved.';
}

// Vorlage enthält z. B. `&copy;`: erst Entities auflösen, dann einmal escapen (bisher als „&amp;copy;“ sichtbar).
$copyright = str_replace(
    ['{year}', '{site_title}'],
    [gmdate('Y'), $siteTitle],
    html_entity_decode(strip_tags($copyrightTpl), ENT_QUOTES | ENT_HTML5, 'UTF-8')
);

$baseUrl = rtrim($siteUrl, '/');

// Standardspalten: nur Ziele verlinken, die als Route oder veröffentlichte Seite existieren (365CMS 4.0.0);
// leere Spalten entfallen.
$bbFooterColumns = [];
if ($showLinks) {
    $bbFooterColumns['Handwerk'] = [
        ['label' => 'Handwerker finden', 'url' => $baseUrl . '/handwerker'],
        ['label' => 'Baufirmen',         'url' => $baseUrl . '/baufirmen'],
        ['label' => 'Angebot anfragen',  'url' => $baseUrl . '/angebot'],
        ['label' => 'Referenzprojekte',  'url' => $baseUrl . '/projekte'],
    ];
    $bbFooterColumns['Für Fachbetriebe'] = [
        ['label' => 'Profil anlegen',    'url' => $baseUrl . '/register'],
        ['label' => 'Pakete & Preise',   'url' => $baseUrl . '/pricing'],
        ['label' => 'Downloads',         'url' => $baseUrl . '/downloads'],
    ];
}
$bbFooterColumns['Service'] = [
    ['label' => 'Kontakt',           'url' => $baseUrl . '/contact'],
    ['label' => 'Newsletter',        'url' => $baseUrl . '/newsletter'],
    ['label' => 'Wissensdatenbank',  'url' => $baseUrl . '/kb'],
    // 365CMS 4.2.0: Community nur bei aktivem Modul (sonst leer und herausgefiltert).
    theme_v4_community_link() ?? ['label' => 'Community', 'url' => ''],
];

// Im Admin gepflegte Footer-Menüs (bisher registriert, aber nie ausgegeben).
$bbFooterMenu = static function (string $location): string {
    ob_start();
    theme_nav_menu($location);
    return trim((string) ob_get_clean());
};
$bbFooterNavHtml   = $bbFooterMenu('footer-nav');
$bbFooterLegalHtml = $bbFooterMenu('footer-legal');
?>
    </div><!-- #content -->

    <footer id="bb-footer" class="bb-footer">
        <div class="bb-container">
            <div class="bb-footer-widgets">
                <div>
                    <h2 class="bb-footer-heading"><?php echo $safe($siteTitle); ?></h2>
                    <?php if ($footerText !== '') : ?>
                        <p><?php echo nl2br($safe($footerText)); ?></p>
                    <?php endif; ?>
                </div>
                <?php foreach ($bbFooterColumns as $bbColumnTitle => $bbColumnLinks) :
                    $bbColumnLinks = theme_v4_filter_links($bbColumnLinks);
                    if ($bbColumnLinks === []) {
                        continue;
                    } ?>
                    <div>
                        <h2 class="bb-footer-heading"><?php echo $safe($bbColumnTitle); ?></h2>
                        <ul>
                            <?php foreach ($bbColumnLinks as $bbLink) : ?>
                            <li><a href="<?php echo $safe(buildbase_safe_url($bbLink['url'], $siteUrl)); ?>"><?php echo $safe($bbLink['label']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
                <?php if ($bbFooterNavHtml !== '') : ?>
                    <nav aria-labelledby="bb-footer-nav-title">
                        <h2 class="bb-footer-heading" id="bb-footer-nav-title">Navigation</h2>
                        <?php echo $bbFooterNavHtml; // theme_nav_menu(): alle Werte escaped ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
        <div class="bb-footer-bottom">
            <div class="bb-container">
                <?php echo $safe($copyright); ?>
                <?php if ($bbFooterLegalHtml !== '') : ?>
                    <nav class="bb-footer-legal" aria-label="<?php echo $safe(theme_v4_t('Rechtliche Links', 'Legal links', $bbLocale)); ?>"><?php echo $bbFooterLegalHtml; // escaped ?></nav>
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
