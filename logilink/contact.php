<?php
/**
 * LogiLink Theme – Kontaktseite (365CMS 4.0.0).
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
 * @package LogiLink_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$llLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$llHeading = $isContactPage
    ? theme_v4_t('Kontakt zur Disposition', 'Contact our dispatch team', $llLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $llLocale))));
$llIntro = $isContactPage
    ? theme_v4_t('Fragen zu Sendungen, Kapazitäten oder Partnerschaften? Schreib uns – wir melden uns zeitnah.', 'Questions about shipments, capacity or partnerships? Write to us and we will get back to you shortly.', $llLocale)
    : '';

// Kontaktdaten aus den Theme-Einstellungen (Customizer-Bereich „contact“), E-Mail-Fallback: ADMIN_EMAIL.
$llContact = [];
foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $llKey) {
    $llValue = trim((string) ll_get_setting('contact', $llKey, ''));
    if ($llValue !== '') {
        $llContact[$llKey] = $llValue;
    }
}
$llEmail = (string) (filter_var($llContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($llEmail === '' && defined('ADMIN_EMAIL')) {
    $llEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
?>
<main id="main" class="ll-main ll-prose-wrap ll-contact-wrap">
    <div class="ll-container">
        <div class="theme-v4-contact">
            <div class="theme-v4-contact__main ll-card ll-page-card">
                <span class="ll-eyebrow"><?php echo theme_v4_e(theme_v4_t('Kontakt', 'Contact', $llLocale)); ?></span>
                <h1><?php echo theme_v4_e($llHeading); ?></h1>
                <?php if ($llIntro !== '') : ?>
                <p class="theme-v4-contact__intro"><?php echo theme_v4_e($llIntro); ?></p>
                <?php endif; ?>

                <?php if ($contactFormHtml !== '') : ?>
                    <?php echo $contactFormHtml; // FormRenderer: vollständig escaped, CSRF + Honeypot ?>
                <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $llLocale)); ?></p>
                <?php endif; ?>
            </div>

            <aside class="theme-v4-contact__aside" aria-labelledby="ll-contact-aside-title">
                <h2 id="ll-contact-aside-title"><?php echo theme_v4_e(theme_v4_t('So erreichst du uns', 'How to reach us', $llLocale)); ?></h2>
                <dl>
                    <?php if ($llEmail !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('E-Mail', 'Email', $llLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo theme_v4_e($llEmail); ?>"><?php echo theme_v4_e($llEmail); ?></a></dd>
                    <?php endif; ?>
                    <?php if (!empty($llContact['contact_phone'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Telefon', 'Phone', $llLocale)); ?></dt>
                    <dd><?php echo theme_v4_e($llContact['contact_phone']); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($llContact['contact_address'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Adresse', 'Address', $llLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($llContact['contact_address'])); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($llContact['contact_hours'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Erreichbarkeit der Disposition', 'Dispatch availability', $llLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($llContact['contact_hours'])); ?></dd>
                    <?php endif; ?>
                    <?php if ($llEmail === '' && count($llContact) === 0) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer hinterlegt.', 'Contact details are maintained in the theme customizer.', $llLocale)); ?></dd>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</main>
