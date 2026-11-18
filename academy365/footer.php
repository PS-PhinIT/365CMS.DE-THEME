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

$safe = fn(string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
try { $c = \CMS\Services\ThemeCustomizer::instance(); } catch (\Throwable $e) { $c = null; }
$copyrightText = $c ? $c->get('footer', 'copyright_text', '') : '';
$footerText = $c ? $c->get('footer', 'footer_text', 'Lernen ohne Grenzen. Starte noch heute.') : 'Lernen ohne Grenzen. Starte noch heute.';
$siteTitle     = \CMS\ThemeManager::instance()->getSiteTitle();
$siteUrl       = rtrim(academy365_safe_url((string) SITE_URL, '/'), '/');
$siteUrl       = $siteUrl !== '' ? $siteUrl : '/';
$year          = date('Y');
$copyrightLine = str_replace(
    ['{year}', '{site_title}'],
    [$year, $siteTitle],
    (string) $copyrightText
);
?>
</div><!-- #content .ac-site-content -->
<footer id="colophon" class="ac-site-footer">
    <div class="ac-footer-inner ac-container">
        <div class="ac-footer-brand">
            <a href="<?php echo $safe($siteUrl); ?>" class="ac-footer-logo">
                <span aria-hidden="true">🎓</span><?php echo $safe($siteTitle); ?>
            </a>
            <p class="ac-footer-tagline"><?php echo $safe((string) $footerText); ?></p>
        </div>
        <?php
        // Standardspalten: nur Ziele verlinken, die als Route oder veröffentlichte Seite existieren (365CMS 4.0.0).
        $acFooterColumns = [
            'Für Lernende' => [
                ['label' => 'Alle Kurse', 'url' => $siteUrl . '/courses'],
                ['label' => 'Kategorien', 'url' => $siteUrl . '/categories'],
                ['label' => 'Zertifikate', 'url' => $siteUrl . '/certificates'],
                ['label' => 'Wissensdatenbank', 'url' => $siteUrl . '/kb'],
                ['label' => 'Downloads', 'url' => $siteUrl . '/downloads'],
            ],
            'Für Tutoren' => [
                ['label' => 'Tutor werden', 'url' => $siteUrl . '/tutor-register'],
                ['label' => 'Tutor-Leitfaden', 'url' => $siteUrl . '/tutor-guide'],
                ['label' => 'FAQ Tutoren', 'url' => $siteUrl . '/tutor-faq'],
            ],
            'Unternehmen' => [
                ['label' => 'Business-Plan', 'url' => $siteUrl . '/business'],
                ['label' => 'Enterprise', 'url' => $siteUrl . '/enterprise'],
                ['label' => 'Partner werden', 'url' => $siteUrl . '/partner'],
            ],
            'Plattform' => [
                ['label' => 'Über uns', 'url' => $siteUrl . '/about'],
                ['label' => 'Blog', 'url' => $siteUrl . '/blog'],
                ['label' => 'Newsletter', 'url' => $siteUrl . '/newsletter'],
                // 365CMS 4.2.0: Community nur bei aktivem Modul (sonst leer und herausgefiltert).
                theme_v4_community_link() ?? ['label' => 'Community', 'url' => ''],
                ['label' => 'Kontakt', 'url' => $siteUrl . '/contact'],
                ['label' => 'Barrierefreiheit', 'url' => $siteUrl . '/barrierefreiheit'],
            ],
        ];
        ?>
        <nav class="ac-footer-nav" aria-label="Fußzeilen-Navigation">
            <?php foreach ($acFooterColumns as $acColumnTitle => $acColumnLinks) :
                $acColumnLinks = theme_v4_filter_links($acColumnLinks);
                if ($acColumnLinks === []) {
                    continue;
                } ?>
            <div class="ac-footer-col">
                <h2 class="ac-footer-heading"><?php echo $safe($acColumnTitle); ?></h2>
                <ul>
                    <?php foreach ($acColumnLinks as $acLink) : ?>
                    <li><a href="<?php echo $safe(academy365_safe_url($acLink['url'], $siteUrl)); ?>"><?php echo $safe($acLink['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </nav>
    </div>
    <div class="ac-footer-bottom">
        <div class="ac-container">
            <p>
                <?php if ($copyrightText && trim($copyrightText) !== '') : ?>
                    <?php echo $safe($copyrightLine); ?>
                <?php else : ?>
                    &copy; <?php echo $year; ?> <?php echo $safe($siteTitle); ?>. Alle Rechte vorbehalten.
                <?php endif; ?>
            </p>
            <?php theme_nav_menu('footer-legal'); ?>
        </div>
    </div>
</footer><!-- #colophon -->
</div><!-- #page .ac-page-wrapper -->
<?php
// before_footer (Theme-Scripts) und body_end (Core-Cookie-Consent, Web-Vitals, PhotoSwipe)
// laufen pro Request nur einmal – der Core löst sie ebenfalls aus.
\CMS\Hooks::doAction('before_footer');
\CMS\Hooks::doAction('body_end');
?>
</body>
</html>
