<?php
/**
 * PTC Theme – Kontaktseite (365CMS 4.0.0)
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Überschrift, Formular und Kontaktdaten an.
 * header.php öffnet bereits <main id="main-content"> – hier kein eigenes <main>.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 *
 * @package PTC_Theme
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$ptcLocale       = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$ptcHeading = $isContactPage
    ? theme_v4_t('Kontakt aufnehmen', 'Get in touch', $ptcLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $ptcLocale))));
$ptcIntro = $isContactPage
    ? theme_v4_t(
        'Fragen zu Stellen, Personalbedarf oder Weiterbildung? Schreiben Sie uns – wir melden uns zeitnah.',
        'Questions about jobs, staffing or training? Write to us and we will get back to you shortly.',
        $ptcLocale
    )
    : '';

// Kontaktdaten: die vorhandenen Footer-Felder des Themes (Customizer → Footer), E-Mail-Fallback ADMIN_EMAIL.
$ptcAddress = trim((string) ptc_customizer_get('footer', 'footer_address', ''));
$ptcPhone   = trim((string) ptc_customizer_get('footer', 'footer_phone', ''));
$ptcEmail   = (string) (filter_var(trim((string) ptc_customizer_get('footer', 'footer_email', '')), FILTER_VALIDATE_EMAIL) ?: '');
if ($ptcEmail === '' && defined('ADMIN_EMAIL')) {
    $ptcEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
$ptcPhoneHref = preg_replace('/[^+0-9]/', '', $ptcPhone) ?? '';
?>

<section class="ptc-page-hero">
    <div class="ptc-container">
        <p class="ptc-hero-overline"><?php echo theme_v4_e(theme_v4_t('Kontakt', 'Contact', $ptcLocale)); ?></p>
        <h1><?php echo theme_v4_e($ptcHeading); ?></h1>
        <?php if ($ptcIntro !== '') : ?>
        <p><?php echo theme_v4_e($ptcIntro); ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="ptc-page-content">
    <div class="ptc-container">
        <div class="theme-v4-contact">
            <div class="theme-v4-contact__main">
                <?php if ($contactFormHtml !== '') : ?>
                    <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
                <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $ptcLocale)); ?></p>
                <?php endif; ?>
            </div>

            <aside class="theme-v4-contact__aside" aria-labelledby="ptc-contact-aside-title">
                <h2 id="ptc-contact-aside-title"><?php echo theme_v4_e(theme_v4_t('So erreichen Sie uns', 'How to reach us', $ptcLocale)); ?></h2>
                <dl>
                    <?php if ($ptcEmail !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('E-Mail', 'Email', $ptcLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo theme_v4_e($ptcEmail); ?>"><?php echo theme_v4_e($ptcEmail); ?></a></dd>
                    <?php endif; ?>
                    <?php if ($ptcPhone !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Telefon', 'Phone', $ptcLocale)); ?></dt>
                    <dd><?php if ($ptcPhoneHref !== '') : ?><a href="tel:<?php echo theme_v4_e($ptcPhoneHref); ?>"><?php echo theme_v4_e($ptcPhone); ?></a><?php else : ?><?php echo theme_v4_e($ptcPhone); ?><?php endif; ?></dd>
                    <?php endif; ?>
                    <?php if ($ptcAddress !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Adresse', 'Address', $ptcLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($ptcAddress)); ?></dd>
                    <?php endif; ?>
                    <?php if ($ptcEmail === '' && $ptcPhone === '' && $ptcAddress === '') : ?>
                    <dd class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer unter „Footer“ hinterlegt.', 'Contact details are maintained in the theme customizer under “Footer”.', $ptcLocale)); ?></dd>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</div>
