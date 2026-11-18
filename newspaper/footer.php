<?php
/**
 * CMS Newspaper Theme – Footer
 *
 * @package CmsNewspaper_Theme
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

$safe       = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$siteTitle  = news_site_title();
$brandRaw   = (string) CmsNewspaper_Theme::instance()->getConfig('masthead_brand', 'PHIN<span>IT</span>.DE');

$tagline    = (string) news_get_setting(
    'footer',
    'footer_tagline',
    (string) CmsNewspaper_Theme::instance()->getConfig('footer_tagline', '')
);

$copyTpl    = (string) news_get_setting(
    'footer',
    'footer_copyright',
    '© {year} {site_title} // designed & developed with 365CMS.DE'
);

$showPartner = filter_var(
    news_get_setting('footer', 'show_partner_strip', true),
    FILTER_VALIDATE_BOOLEAN
);

$copyText = strtr($copyTpl, [
    '{year}'       => gmdate('Y'),
    '{site_title}' => $siteTitle,
]);

// Partner-Leiste: nur gepflegte Links (Customizer „Footer → Partner-Links“, „Bezeichnung | URL“ je Zeile).
// Bis 2.0.0 waren sieben fremde Domains fest eingetragen und erschienen in jeder Installation.
$partners = [];
foreach (preg_split('/\R/', (string) news_get_setting('footer', 'partner_links', '')) ?: [] as $partnerLine) {
    $partnerParts = array_map('trim', explode('|', $partnerLine, 2));
    if (count($partnerParts) === 2 && $partnerParts[0] !== '' && preg_match('#^https?://#i', $partnerParts[1]) === 1) {
        $partners[] = ['label' => mb_substr($partnerParts[0], 0, 60), 'url' => $partnerParts[1]];
    }
    if (count($partners) >= 12) {
        break;
    }
}

// Standardspalten: nur Ziele, die als Route oder veröffentlichte Seite existieren (365CMS 4.0.0); leere Spalten entfallen.
$newsFooterColumns = [
    'Ressourcen' => [
        ['label' => 'M365 Script Hub',   'url' => news_href('/script-hub')],
        ['label' => 'Admin Checklisten', 'url' => news_href('/checklisten')],
        ['label' => 'Knowledge Base',    'url' => news_href('/kb')],
        ['label' => 'Downloads',         'url' => news_href('/downloads')],
        ['label' => 'Event Kalender',    'url' => news_href('/events')],
    ],
    'Portal' => [
        ['label' => 'Über uns (Autoren)', 'url' => news_href('/autoren')],
        ['label' => 'Newsletter',         'url' => news_href('/newsletter')],
        // 365CMS 4.2.0: Leser-Community nur bei aktivem Modul (sonst leer und herausgefiltert).
        theme_v4_community_link(null, 'Leser-Community', 'Reader community') ?? ['label' => 'Community', 'url' => ''],
        ['label' => 'Kontakt',            'url' => news_href('/kontakt')],
        ['label' => 'Impressum',          'url' => news_href('/impressum')],
        ['label' => 'Datenschutz',        'url' => news_href('/datenschutz')],
    ],
];

ob_start();
$newsHasTopics = theme_nav_menu('footer-nav');
$newsTopicsMenu = (string) ob_get_clean();
ob_start();
$newsHasLegal = theme_nav_menu('footer-legal');
$newsLegalMenu = (string) ob_get_clean();
?>
    </main><!-- /#main-content -->

    <footer class="news-footer" id="news-footer">

        <div class="news-footer-grid">

            <div class="news-footer-brand">
                <a href="<?php echo $safe(news_href('/')); ?>" class="news-footer-wordmark news-focus-shadow" rel="home" aria-label="<?php echo $safe($siteTitle); ?>">
                    <?php echo news_brand_html($brandRaw); ?>
                </a>
                <?php if (trim($tagline) !== '') : ?>
                    <p><?php echo $safe($tagline); ?></p>
                <?php endif; ?>
            </div>

            <?php if ($newsHasTopics) : ?>
                <nav class="news-footer-col" aria-labelledby="news-footer-topics">
                    <h2 class="news-footer-heading" id="news-footer-topics">Themen</h2>
                    <?php echo $newsTopicsMenu; // theme_nav_menu(): escapte Menüliste ?>
                </nav>
            <?php endif; ?>

            <?php foreach ($newsFooterColumns as $newsColumnTitle => $newsColumnLinks) :
                $newsColumnLinks = theme_v4_filter_links($newsColumnLinks);
                if ($newsColumnLinks === []) {
                    continue;
                }
                $newsColumnId = 'news-footer-' . strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $newsColumnTitle));
                ?>
                <nav class="news-footer-col" aria-labelledby="<?php echo $safe($newsColumnId); ?>">
                    <h2 class="news-footer-heading" id="<?php echo $safe($newsColumnId); ?>"><?php echo $safe($newsColumnTitle); ?></h2>
                    <ul>
                        <?php foreach ($newsColumnLinks as $newsLink) :
                            $newsLinkUrl = news_safe_url($newsLink['url']);
                            $newsLinkActive = theme_v4_is_current_path($newsLinkUrl);
                            ?>
                            <li<?php echo $newsLinkActive ? ' class="current-menu-item"' : ''; ?>><a href="<?php echo $safe($newsLinkUrl); ?>"<?php echo $newsLinkActive ? ' aria-current="page"' : ''; ?>><?php echo $safe($newsLink['label']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endforeach; ?>

        </div>

        <?php if ($showPartner && $partners !== []) : ?>
            <nav class="news-partner-strip" aria-label="Partnerseiten">
                <?php foreach ($partners as $p) :
                    $pUrl   = news_safe_url((string) ($p['url'] ?? ''), '');
                    $pLabel = (string) ($p['label'] ?? '');
                    if ($pLabel === '' || $pUrl === '') { continue; }
                    ?>
                    <a href="<?php echo $safe($pUrl); ?>" target="_blank" rel="noopener noreferrer"><?php echo $safe($pLabel); ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <div class="news-footer-copy">
            <span><?php echo $safe($copyText); ?></span>
            <?php if ($newsHasLegal) : ?>
                <nav class="news-footer-legal" aria-label="Rechtliche Links">
                    <?php echo $newsLegalMenu; // theme_nav_menu(): escapte Menüliste ?>
                </nav>
            <?php endif; ?>
        </div>

    </footer>

<?php
// before_footer (Theme-Scripts) und body_end (Core-Cookie-Consent, Web-Vitals, PhotoSwipe)
// laufen pro Request nur einmal – der Core löst sie ebenfalls aus.
\CMS\Hooks::doAction('before_footer');
\CMS\Hooks::doAction('body_end');
?>

</body>
</html>
