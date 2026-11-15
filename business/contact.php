<?php
declare(strict_types=1);

/**
 * Business Theme – Kontaktseite (365CMS 4.0.0)
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
 * @package IT_Business_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$bizLocale       = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$bizHeading = $isContactPage
    ? theme_v4_t('Kontakt aufnehmen', 'Get in touch', $bizLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $bizLocale))));
$bizIntro = $isContactPage
    ? (string) biz_config('cta_text', theme_v4_t(
        'Kontaktieren Sie uns für ein unverbindliches Erstgespräch. Wir freuen uns auf Ihre Anfrage.',
        'Contact us for a free initial consultation. We look forward to hearing from you.',
        $bizLocale
    ))
    : '';

// Kontaktdaten aus dem Customizer-Bereich „contact“, E-Mail-Fallback: ADMIN_EMAIL.
$bizContact = [];
try {
    $bizCustomizer = \CMS\Services\ThemeCustomizer::instance();
    foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $bizKey) {
        $bizValue = trim((string) $bizCustomizer->get('contact', $bizKey, ''));
        if ($bizValue !== '') {
            $bizContact[$bizKey] = $bizValue;
        }
    }
} catch (\Throwable) {
}
$bizEmail = (string) (filter_var($bizContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($bizEmail === '' && defined('ADMIN_EMAIL')) {
    $bizEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
$bizPhoneHref = preg_replace('/[^0-9+]/', '', (string) ($bizContact['contact_phone'] ?? '')) ?? '';
?>

<section class="biz-page-hero">
    <div class="biz-container">
        <p class="biz-page-eyebrow"><?php echo theme_v4_e(theme_v4_t('Kontakt', 'Contact', $bizLocale)); ?></p>
        <h1><?php echo theme_v4_e($bizHeading); ?></h1>
        <?php if ($bizIntro !== '') : ?>
        <p><?php echo theme_v4_e($bizIntro); ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="biz-page-content">
    <div class="biz-container">
        <div class="theme-v4-contact">
            <div class="theme-v4-contact__main">
                <?php if ($contactFormHtml !== '') : ?>
                    <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
                <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $bizLocale)); ?></p>
                <?php endif; ?>
            </div>

            <aside class="theme-v4-contact__aside" aria-labelledby="biz-contact-aside-title">
                <h2 id="biz-contact-aside-title"><?php echo theme_v4_e(theme_v4_t('So erreichen Sie uns', 'How to reach us', $bizLocale)); ?></h2>
                <dl>
                    <?php if ($bizEmail !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('E-Mail', 'Email', $bizLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo theme_v4_e($bizEmail); ?>"><?php echo theme_v4_e($bizEmail); ?></a></dd>
                    <?php endif; ?>
                    <?php if (!empty($bizContact['contact_phone'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Telefon', 'Phone', $bizLocale)); ?></dt>
                    <dd><?php if ($bizPhoneHref !== '') : ?><a href="tel:<?php echo theme_v4_e($bizPhoneHref); ?>"><?php echo theme_v4_e($bizContact['contact_phone']); ?></a><?php else : ?><?php echo theme_v4_e($bizContact['contact_phone']); ?><?php endif; ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($bizContact['contact_address'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Adresse', 'Address', $bizLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($bizContact['contact_address'])); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($bizContact['contact_hours'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Erreichbarkeit', 'Availability', $bizLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($bizContact['contact_hours'])); ?></dd>
                    <?php endif; ?>
                    <?php if ($bizEmail === '' && count($bizContact) === 0) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer hinterlegt.', 'Contact details are maintained in the theme customizer.', $bizLocale)); ?></dd>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</div>
