<?php
/**
 * MedCare Pro – Kontaktseite (365CMS 4.0.0)
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Überschrift, Formular und Praxiskontakt an.
 * header.php öffnet kein <main> – die Vorlage setzt <main id="main"> (Skip-Link-Ziel) selbst.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 *
 * @package MedCarePro_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$mcLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;
$safe            = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

$mcHeading = $isContactPage
    ? theme_v4_t('Kontakt zur Praxis', 'Contact the practice', $mcLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $mcLocale))));
$mcIntro = $isContactPage
    ? theme_v4_t('Fragen zu Terminen, Leistungen oder Ihrem Konto? Schreiben Sie uns – bitte senden Sie keine Befunde oder Gesundheitsdaten über dieses Formular.', 'Questions about appointments, services or your account? Write to us – please do not send medical records or health data through this form.', $mcLocale)
    : '';

// Kontaktdaten aus dem Customizer-Bereich „contact“; Telefon-Fallback: Termin-Telefon aus „Header“,
// E-Mail-Fallback: ADMIN_EMAIL.
$mcContact = [];
foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $mcKey) {
    $mcValue = trim((string) mc_get_setting('contact', $mcKey, ''));
    if ($mcValue !== '') {
        $mcContact[$mcKey] = $mcValue;
    }
}
if (empty($mcContact['contact_phone'])) {
    $mcAppointmentPhone = trim((string) mc_get_setting('header', 'appointment_phone', ''));
    if ($mcAppointmentPhone !== '') {
        $mcContact['contact_phone'] = $mcAppointmentPhone;
    }
}
$mcEmail = (string) (filter_var($mcContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($mcEmail === '' && defined('ADMIN_EMAIL')) {
    $mcEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
$mcPhoneHref = isset($mcContact['contact_phone']) ? mc_tel_sanitize($mcContact['contact_phone']) : '';
$mcEmergency = trim((string) mc_get_setting('header', 'emergency_phone', '112'));
?>
<main id="main" class="mc-main mc-page">
    <div class="mc-container">
        <h1 class="mc-page__title"><?php echo $safe($mcHeading); ?></h1>
        <?php if ($mcIntro !== '') : ?>
            <p class="theme-v4-contact__intro"><?php echo $safe($mcIntro); ?></p>
        <?php endif; ?>

        <div class="theme-v4-contact">
            <div class="theme-v4-contact__main">
                <?php if ($contactFormHtml !== '') : ?>
                    <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
                <?php else : ?>
                    <p class="theme-v4-contact__empty"><?php echo $safe(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $mcLocale)); ?></p>
                <?php endif; ?>
            </div>

            <aside class="theme-v4-contact__aside" aria-labelledby="mc-contact-aside-title">
                <h2 id="mc-contact-aside-title"><?php echo $safe(theme_v4_t('So erreichen Sie uns', 'How to reach us', $mcLocale)); ?></h2>
                <dl>
                    <?php if ($mcEmail !== '') : ?>
                        <dt><?php echo $safe(theme_v4_t('E-Mail', 'Email', $mcLocale)); ?></dt>
                        <dd><a href="mailto:<?php echo $safe($mcEmail); ?>"><?php echo $safe($mcEmail); ?></a></dd>
                    <?php endif; ?>
                    <?php if (!empty($mcContact['contact_phone'])) : ?>
                        <dt><?php echo $safe(theme_v4_t('Telefon', 'Phone', $mcLocale)); ?></dt>
                        <dd><?php if ($mcPhoneHref !== '') : ?><a href="tel:<?php echo $safe($mcPhoneHref); ?>"><?php echo $safe($mcContact['contact_phone']); ?></a><?php else : ?><?php echo $safe($mcContact['contact_phone']); ?><?php endif; ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($mcContact['contact_address'])) : ?>
                        <dt><?php echo $safe(theme_v4_t('Adresse', 'Address', $mcLocale)); ?></dt>
                        <dd><?php echo nl2br($safe($mcContact['contact_address'])); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($mcContact['contact_hours'])) : ?>
                        <dt><?php echo $safe(theme_v4_t('Sprechzeiten', 'Office hours', $mcLocale)); ?></dt>
                        <dd><?php echo nl2br($safe($mcContact['contact_hours'])); ?></dd>
                    <?php endif; ?>
                    <?php if ($mcEmergency !== '' && mc_tel_sanitize($mcEmergency) !== '') : ?>
                        <dt><?php echo $safe(theme_v4_t('Im Notfall', 'In an emergency', $mcLocale)); ?></dt>
                        <dd><a href="tel:<?php echo $safe(mc_tel_sanitize($mcEmergency)); ?>"><?php echo $safe($mcEmergency); ?></a></dd>
                    <?php endif; ?>
                    <?php if ($mcEmail === '' && $mcContact === []) : ?>
                        <dd class="theme-v4-contact__empty"><?php echo $safe(theme_v4_t('Kontaktdaten werden im Theme-Customizer (Bereich „Kontakt“) hinterlegt.', 'Contact details are maintained in the theme customizer (section “Contact”).', $mcLocale)); ?></dd>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</main>
