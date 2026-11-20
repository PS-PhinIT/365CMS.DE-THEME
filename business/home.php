<?php
declare(strict_types=1);

/**
 * Business Theme – Homepage / Company Presentation
 *
 * Zeigt alle Sektionen der Unternehmens-Landingpage:
 * Hero → Leistungen → Über uns → Zahlen & Fakten → CTA
 *
 * @package IT_Business_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$siteUrl = biz_site_url();

try {
    $aboutImg = (string) \CMS\Services\ThemeCustomizer::instance()->get('biz_content', 'about_image', '');
} catch (\Throwable) {
    $aboutImg = '';
}
$aboutImg = biz_safe_url($aboutImg);
$aboutImg = str_starts_with($aboutImg, '#') ? '' : $aboutImg;

// Kontakt-CTA: Kontaktdaten aus dem Customizer-Bereich „contact“ statt fest verdrahteter Platzhalter
// (bisher mailto:info@example.com / tel:+491234567890); Formular über die Core-Route /contact.
try {
    $bizCustomizer = \CMS\Services\ThemeCustomizer::instance();
    $ctaEmail = (string) (filter_var(trim((string) $bizCustomizer->get('contact', 'contact_email', '')), FILTER_VALIDATE_EMAIL) ?: '');
    $ctaPhone = trim((string) $bizCustomizer->get('contact', 'contact_phone', ''));
} catch (\Throwable) {
    $ctaEmail = '';
    $ctaPhone = '';
}
$ctaPhoneHref = preg_replace('/[^0-9+]/', '', $ctaPhone) ?? '';
$ctaContactUrl = biz_href('/contact');

$bizLocale = theme_v4_locale($contentLocale ?? null);
$t = static fn(string $de, string $en): string => theme_v4_t($de, $en, $bizLocale);
$esc = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

// Leistungen, Über-uns-Text, Aufzählung und Kennzahlen kommen aus dem Customizer (Bereich „Seiteninhalte“).
// Bis 4.1.0 standen sie fest in dieser Vorlage, darunter nicht belegte Angaben („Über 200 Projekte“, „ISO 27001-konforme
// Sicherheitsstandards“, „98 % Kundenzufriedenheit“): Aufzählung und Kennzahlen sind jetzt standardmäßig leer und
// werden nur ausgegeben, wenn sie gepflegt sind.
$bizServices  = biz_parse_lines((string) biz_config('services_list', ''), 3, 12);
$bizFacts     = biz_parse_lines((string) biz_config('facts_list', ''), 2, 6);
$bizChecklist = array_column(biz_parse_lines((string) biz_config('about_checklist', ''), 1, 12), 0);
$bizAboutText = array_values(array_filter(
    array_map('trim', preg_split('/\R{2,}/u', trim((string) biz_config('about_text', ''))) ?: []),
    static fn(string $p): bool => $p !== ''
));
// Der Hero-Button „Leistungen“ zeigt nur auf einen vorhandenen Abschnitt.
$bizHeroPrimaryUrl = (string) biz_config('hero_cta_primary_url', '#leistungen');
$bizShowHeroPrimary = !($bizServices === [] && $bizHeroPrimaryUrl === '#leistungen');
?>

<!-- ██ HERO ██████████████████████████████████████████████████████████████ -->
<section class="biz-hero" id="start">
    <div class="biz-container">
        <div class="biz-hero-inner">

            <div class="biz-hero-badge">
                <?php echo htmlspecialchars((string) biz_config('hero_badge', 'Ihr verlässlicher IT-Partner'), ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <h1>
                <?php
                $headline = (string) biz_config(
                    'hero_headline',
                    'Wir bringen <span class="highlight">Ihr Unternehmen</span> erfolgreich in die Zukunft.'
                );
                echo biz_safe_headline($headline);
                ?>
            </h1>

            <p class="biz-hero-lead">
                <?php echo htmlspecialchars(
                    (string) biz_config('hero_text', 'Mit fundierter Expertise, modernsten Technologien und einem engagierten Team entwickeln wir maßgeschneiderte Lösungen für Ihre digitalen Herausforderungen.'),
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </p>

            <div class="biz-hero-actions">
                <?php if ($bizShowHeroPrimary) : ?>
                <a href="<?php echo htmlspecialchars(biz_href($bizHeroPrimaryUrl), ENT_QUOTES, 'UTF-8'); ?>"
                   class="btn-biz btn-biz-primary btn-biz-lg">
                    <?php echo htmlspecialchars((string) biz_config('hero_cta_primary_label', 'Leistungen entdecken'), ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php endif; ?>
                <a href="<?php echo htmlspecialchars(biz_href((string) biz_config('hero_cta_secondary_url', '#kontakt')), ENT_QUOTES, 'UTF-8'); ?>"
                   class="btn-biz btn-biz-outline btn-biz-lg">
                    <?php echo htmlspecialchars((string) biz_config('hero_cta_secondary_label', 'Kontakt aufnehmen'), ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ██ LEISTUNGEN ████████████████████████████████████████████████████████ -->
<?php if ($bizServices !== []) : ?>
<section class="biz-section" id="leistungen">
    <div class="biz-container">

        <div class="biz-section-head">
            <span class="biz-eyebrow"><?php echo $esc($t('Was wir bieten', 'What we offer')); ?></span>
            <h2><?php echo $esc($t('Unsere Leistungen', 'Our services')); ?></h2>
            <p><?php echo $esc($t('Von der Strategie bis zur Umsetzung – wir begleiten Sie auf dem gesamten Weg Ihrer digitalen Transformation.', 'From strategy to delivery – we support you every step of your digital transformation.')); ?></p>
        </div>

        <div class="biz-services-grid">
            <?php foreach ($bizServices as $bizService) : ?>
            <div class="biz-service-card">
                <div class="biz-service-icon" aria-hidden="true"><?php echo $esc($bizService[0]); ?></div>
                <h3><?php echo $esc($bizService[1]); ?></h3>
                <?php if (($bizService[2] ?? '') !== '') : ?>
                <p><?php echo $esc($bizService[2]); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ██ ÜBER UNS ██████████████████████████████████████████████████████████ -->
<section class="biz-section biz-section-alt" id="ueber-uns">
    <div class="biz-container">
        <div class="biz-about-grid">

            <!-- Visual -->
            <div class="biz-about-visual">
                <?php if ($aboutImg !== '') : ?>
                    <?php echo theme_v4_img($aboutImg, $t('Über uns', 'About us'), ['sizes' => '(max-width: 768px) 100vw, 600px']); ?>
                <?php else : ?>
                    <span aria-hidden="true">🏢</span>
                <?php endif; ?>
            </div>

            <!-- Text -->
            <div class="biz-about-text">
                <span class="biz-eyebrow"><?php echo $esc($t('Über uns', 'About us')); ?></span>
                <h2><?php echo htmlspecialchars((string) biz_config('about_heading', 'Der Experte an Ihrer Seite'), ENT_QUOTES, 'UTF-8'); ?></h2>

                <?php foreach ($bizAboutText as $bizParagraph) : ?>
                <p><?php echo $esc($bizParagraph); ?></p>
                <?php endforeach; ?>

                <?php if ($bizChecklist !== []) : ?>
                <ul class="biz-check-list">
                    <?php foreach ($bizChecklist as $bizPoint) : ?>
                    <li><?php echo $esc($bizPoint); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <a href="<?php echo htmlspecialchars(biz_href('#kontakt'), ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-primary">
                    <?php echo $esc($t('Jetzt kennenlernen', 'Get to know us')); ?> <span aria-hidden="true">→</span>
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ██ ZAHLEN & FAKTEN ████████████████████████████████████████████████████ -->
<?php if ($bizFacts !== []) : ?>
<section class="biz-section biz-section-dark" id="zahlen">
    <div class="biz-container">

        <div class="biz-section-head">
            <span class="biz-eyebrow"><?php echo $esc($t('Zahlen & Fakten', 'Facts & figures')); ?></span>
            <h2><?php echo $esc($t('Das spricht für uns', 'Why choose us')); ?></h2>
        </div>

        <div class="biz-stats-grid">
            <?php foreach ($bizFacts as $bizFact) : ?>
            <div>
                <div class="biz-stat-value"><?php echo $esc($bizFact[0]); ?></div>
                <div class="biz-stat-label"><?php echo $esc($bizFact[1]); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<?php endif; ?>


<!-- ██ CTA ████████████████████████████████████████████████████████████████ -->
<section class="biz-cta-section" id="kontakt">
    <div class="biz-container">

        <span class="biz-eyebrow"><?php echo $esc($t('Kontakt', 'Contact')); ?></span>
        <h2><?php echo htmlspecialchars((string) biz_config('cta_heading', 'Bereit für den nächsten Schritt?'), ENT_QUOTES, 'UTF-8'); ?></h2>
        <p><?php echo htmlspecialchars((string) biz_config('cta_text', 'Kontaktieren Sie uns noch heute für ein unverbindliches Erstgespräch. Wir freuen uns auf Ihre Anfrage.'), ENT_QUOTES, 'UTF-8'); ?></p>

        <div class="biz-cta-actions">
            <a href="<?php echo htmlspecialchars($ctaContactUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-white btn-biz-lg">
                <?php echo $esc($t('Kontaktformular öffnen', 'Open contact form')); ?>
            </a>
            <?php if ($ctaEmail !== '') : ?>
            <a href="<?php echo htmlspecialchars('mailto:' . $ctaEmail, ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-outline-white btn-biz-lg">
                <span aria-hidden="true">✉</span> <?php echo $esc($t('E-Mail schreiben', 'Send an email')); ?>
            </a>
            <?php endif; ?>
            <?php if ($ctaPhoneHref !== '') : ?>
            <a href="<?php echo htmlspecialchars('tel:' . $ctaPhoneHref, ENT_QUOTES, 'UTF-8'); ?>" class="btn-biz btn-biz-outline-white btn-biz-lg">
                <span aria-hidden="true">📞</span> <?php echo $esc($t('Jetzt anrufen', 'Call now')); ?>
            </a>
            <?php endif; ?>
        </div>

    </div>
</section>
