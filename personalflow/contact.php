<?php
/**
 * PersonalFlow Theme – Kontaktseite (365CMS 4.0.0).
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
 * @package PersonalFlow_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$pfLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$pfHeading = $isContactPage
    ? theme_v4_t('Kontakt zum Recruiting-Team', 'Contact our recruiting team', $pfLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $pfLocale))));
$pfIntro = $isContactPage
    ? theme_v4_t('Fragen zu offenen Stellen, deinem Profil oder unseren Paketen für Arbeitgeber? Schreib uns – wir melden uns zeitnah.', 'Questions about open positions, your profile or our employer packages? Write to us and we will get back to you shortly.', $pfLocale)
    : '';

// Kontaktdaten aus den Theme-Einstellungen (Customizer-Bereich „contact“), E-Mail-Fallback: ADMIN_EMAIL.
$pfContact = [];
foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $pfKey) {
    $pfValue = trim((string) pf_get_setting('contact', $pfKey, ''));
    if ($pfValue !== '') {
        $pfContact[$pfKey] = $pfValue;
    }
}
$pfEmail = (string) (filter_var($pfContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($pfEmail === '' && defined('ADMIN_EMAIL')) {
    $pfEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
?>
<main id="main" class="pf-main pf-page-shell pf-contact-shell">
    <div class="pf-container">
        <div class="theme-v4-contact">
            <div class="theme-v4-contact__main pf-card">
                <span class="pf-section-eyebrow"><?php echo theme_v4_e(theme_v4_t('Kontakt', 'Contact', $pfLocale)); ?></span>
                <h1><?php echo theme_v4_e($pfHeading); ?></h1>
                <?php if ($pfIntro !== '') : ?>
                <p class="theme-v4-contact__intro"><?php echo theme_v4_e($pfIntro); ?></p>
                <?php endif; ?>

                <?php if ($contactFormHtml !== '') : ?>
                    <?php echo $contactFormHtml; // FormRenderer: vollständig escaped, CSRF + Honeypot ?>
                <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $pfLocale)); ?></p>
                <?php endif; ?>
            </div>

            <aside class="theme-v4-contact__aside" aria-labelledby="pf-contact-aside-title">
                <h2 id="pf-contact-aside-title"><?php echo theme_v4_e(theme_v4_t('So erreichst du uns', 'How to reach us', $pfLocale)); ?></h2>
                <dl>
                    <?php if ($pfEmail !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('E-Mail', 'Email', $pfLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo theme_v4_e($pfEmail); ?>"><?php echo theme_v4_e($pfEmail); ?></a></dd>
                    <?php endif; ?>
                    <?php if (!empty($pfContact['contact_phone'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Telefon', 'Phone', $pfLocale)); ?></dt>
                    <dd><?php echo theme_v4_e($pfContact['contact_phone']); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($pfContact['contact_address'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Adresse', 'Address', $pfLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($pfContact['contact_address'])); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($pfContact['contact_hours'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Erreichbarkeit', 'Availability', $pfLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($pfContact['contact_hours'])); ?></dd>
                    <?php endif; ?>
                    <?php if ($pfEmail === '' && count($pfContact) === 0) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer hinterlegt.', 'Contact details are maintained in the theme customizer.', $pfLocale)); ?></dd>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</main>
