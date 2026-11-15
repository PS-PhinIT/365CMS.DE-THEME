<?php
/**
 * Academy365 – Kontaktseite (365CMS 4.0.0).
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Überschrift, Formular und Kontaktdaten an.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$acLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$acHeading = $isContactPage
    ? theme_v4_t('Kontakt aufnehmen', 'Get in touch', $acLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $acLocale))));
$acIntro = $isContactPage
    ? theme_v4_t('Fragen zu Kursen, Zertifikaten oder Firmenlizenzen? Schreib uns – wir melden uns zeitnah.', 'Questions about courses, certificates or company licences? Write to us and we will get back to you shortly.', $acLocale)
    : '';

// Kontaktdaten aus den Theme-Einstellungen (Customizer-Bereich „contact“), E-Mail-Fallback: ADMIN_EMAIL.
$acContact = [];
try {
    $acCustomizer = \CMS\Services\ThemeCustomizer::instance();
    foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $acKey) {
        $acValue = trim((string) $acCustomizer->get('contact', $acKey, ''));
        if ($acValue !== '') {
            $acContact[$acKey] = $acValue;
        }
    }
} catch (\Throwable) {
}
$acEmail = (string) (filter_var($acContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($acEmail === '' && defined('ADMIN_EMAIL')) {
    $acEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
?>
<main id="main" class="ac-main-content ac-page-content">
    <div class="ac-container">
        <div class="theme-v4-contact">
            <div class="theme-v4-contact__main">
                <h1 class="ac-page-title"><?php echo theme_v4_e($acHeading); ?></h1>
                <?php if ($acIntro !== '') : ?>
                <p class="theme-v4-contact__intro"><?php echo theme_v4_e($acIntro); ?></p>
                <?php endif; ?>

                <?php if ($contactFormHtml !== '') : ?>
                    <?php echo $contactFormHtml; // FormRenderer: vollständig escaped, CSRF + Honeypot ?>
                <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $acLocale)); ?></p>
                <?php endif; ?>
            </div>

            <aside class="theme-v4-contact__aside" aria-labelledby="ac-contact-aside-title">
                <h2 id="ac-contact-aside-title"><?php echo theme_v4_e(theme_v4_t('So erreichst du uns', 'How to reach us', $acLocale)); ?></h2>
                <dl>
                    <?php if ($acEmail !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('E-Mail', 'Email', $acLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo theme_v4_e($acEmail); ?>"><?php echo theme_v4_e($acEmail); ?></a></dd>
                    <?php endif; ?>
                    <?php if (!empty($acContact['contact_phone'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Telefon', 'Phone', $acLocale)); ?></dt>
                    <dd><?php echo theme_v4_e($acContact['contact_phone']); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($acContact['contact_address'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Adresse', 'Address', $acLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($acContact['contact_address'])); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($acContact['contact_hours'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Erreichbarkeit', 'Availability', $acLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($acContact['contact_hours'])); ?></dd>
                    <?php endif; ?>
                    <?php if ($acEmail === '' && count($acContact) === 0) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer hinterlegt.', 'Contact details are maintained in the theme customizer.', $acLocale)); ?></dd>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</main>
