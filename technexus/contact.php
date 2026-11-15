<?php
/**
 * TechNexus Theme – Kontaktseite (365CMS 4.0.0).
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Überschrift, Formular und Kontaktdaten an.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 *
 * @package TechNexus_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$tnLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$tnHeading = $isContactPage
    ? theme_v4_t('Kontakt zum TechNexus-Team', 'Contact the TechNexus team', $tnLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $tnLocale))));
$tnIntro = $isContactPage
    ? theme_v4_t('Fragen zu Projekten, Experten-Profilen oder Partnerschaften? Schreib uns – wir melden uns zeitnah.', 'Questions about projects, expert profiles or partnerships? Write to us and we will get back to you shortly.', $tnLocale)
    : '';

// Kontaktdaten aus den Theme-Einstellungen (Customizer-Bereich „contact“), E-Mail-Fallback: ADMIN_EMAIL.
$tnContact = [];
foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $tnKey) {
    $tnValue = trim((string) tn_get_setting('contact', $tnKey, ''));
    if ($tnValue !== '') {
        $tnContact[$tnKey] = $tnValue;
    }
}
$tnEmail = (string) (filter_var($tnContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($tnEmail === '' && defined('ADMIN_EMAIL')) {
    $tnEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
?>
<main id="main" class="site-main tn-section tn-contact-section">
    <div class="container">
        <div class="theme-v4-contact">
            <div class="theme-v4-contact__main tech-card tn-prose-card">
                <p class="hero-badge tn-contact-badge"><?php echo theme_v4_e(theme_v4_t('Kontakt', 'Contact', $tnLocale)); ?></p>
                <h1><?php echo theme_v4_e($tnHeading); ?></h1>
                <?php if ($tnIntro !== '') : ?>
                <p class="theme-v4-contact__intro"><?php echo theme_v4_e($tnIntro); ?></p>
                <?php endif; ?>

                <?php if ($contactFormHtml !== '') : ?>
                    <?php echo $contactFormHtml; // FormRenderer: vollständig escaped, CSRF + Honeypot ?>
                <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $tnLocale)); ?></p>
                <?php endif; ?>
            </div>

            <aside class="theme-v4-contact__aside" aria-labelledby="tn-contact-aside-title">
                <h2 id="tn-contact-aside-title"><?php echo theme_v4_e(theme_v4_t('So erreichst du uns', 'How to reach us', $tnLocale)); ?></h2>
                <dl>
                    <?php if ($tnEmail !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('E-Mail', 'Email', $tnLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo theme_v4_e($tnEmail); ?>"><?php echo theme_v4_e($tnEmail); ?></a></dd>
                    <?php endif; ?>
                    <?php if (!empty($tnContact['contact_phone'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Telefon', 'Phone', $tnLocale)); ?></dt>
                    <dd><?php echo theme_v4_e($tnContact['contact_phone']); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($tnContact['contact_address'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Adresse', 'Address', $tnLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($tnContact['contact_address'])); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($tnContact['contact_hours'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Erreichbarkeit', 'Availability', $tnLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($tnContact['contact_hours'])); ?></dd>
                    <?php endif; ?>
                    <?php if ($tnEmail === '' && count($tnContact) === 0) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer hinterlegt.', 'Contact details are maintained in the theme customizer.', $tnLocale)); ?></dd>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</main>
