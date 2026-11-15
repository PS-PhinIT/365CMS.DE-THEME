<?php
declare(strict_types=1);

/**
 * BuildBase – Kontaktseite (365CMS 4.0.0)
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Überschrift, Formular und Kontaktdaten an.
 * header.php öffnet kein <main> – die Vorlage setzt <main id="main"> selbst.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 */

if (!defined('ABSPATH')) {
    exit;
}

$bbLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$bbHeading = $isContactPage
    ? theme_v4_t('Kontakt aufnehmen', 'Get in touch', $bbLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $bbLocale))));
$bbIntro = $isContactPage
    ? theme_v4_t(
        'Sie planen ein Bauvorhaben oder brauchen einen Fachbetrieb? Schreiben Sie uns – wir melden uns zeitnah.',
        'Planning a building project or looking for a contractor? Write to us and we will get back to you shortly.',
        $bbLocale
    )
    : '';

// Kontaktdaten aus dem Customizer-Bereich „contact“; Telefon-Fallback: Notfall-Nummer aus dem Header,
// E-Mail-Fallback: ADMIN_EMAIL.
$bbContact = [];
try {
    $bbCustomizer = \CMS\Services\ThemeCustomizer::instance();
    foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $bbKey) {
        $bbValue = trim((string) $bbCustomizer->get('contact', $bbKey, ''));
        if ($bbValue !== '') {
            $bbContact[$bbKey] = $bbValue;
        }
    }
    if (!isset($bbContact['contact_phone'])) {
        $bbEmergency = trim((string) $bbCustomizer->get('header', 'emergency_phone', ''));
        if ($bbEmergency !== '') {
            $bbContact['contact_phone'] = $bbEmergency;
        }
    }
} catch (\Throwable) {
}
$bbEmail = (string) (filter_var($bbContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($bbEmail === '' && defined('ADMIN_EMAIL')) {
    $bbEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
$bbPhoneHref = preg_replace('/[^0-9+]/', '', (string) ($bbContact['contact_phone'] ?? '')) ?? '';
?>
<main id="main" class="bb-main bb-page-section">
    <div class="bb-container">
        <div class="theme-v4-contact">
            <div class="theme-v4-contact__main">
                <h1 class="bb-page-title"><?php echo theme_v4_e($bbHeading); ?></h1>
                <?php if ($bbIntro !== '') : ?>
                <p class="theme-v4-contact__intro"><?php echo theme_v4_e($bbIntro); ?></p>
                <?php endif; ?>

                <?php if ($contactFormHtml !== '') : ?>
                    <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
                <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $bbLocale)); ?></p>
                <?php endif; ?>
            </div>

            <aside class="theme-v4-contact__aside" aria-labelledby="bb-contact-aside-title">
                <h2 id="bb-contact-aside-title"><?php echo theme_v4_e(theme_v4_t('So erreichen Sie uns', 'How to reach us', $bbLocale)); ?></h2>
                <dl>
                    <?php if ($bbEmail !== '') : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('E-Mail', 'Email', $bbLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo theme_v4_e($bbEmail); ?>"><?php echo theme_v4_e($bbEmail); ?></a></dd>
                    <?php endif; ?>
                    <?php if (!empty($bbContact['contact_phone'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Telefon', 'Phone', $bbLocale)); ?></dt>
                    <dd><?php if ($bbPhoneHref !== '') : ?><a href="tel:<?php echo theme_v4_e($bbPhoneHref); ?>"><?php echo theme_v4_e($bbContact['contact_phone']); ?></a><?php else : ?><?php echo theme_v4_e($bbContact['contact_phone']); ?><?php endif; ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($bbContact['contact_address'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Adresse', 'Address', $bbLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($bbContact['contact_address'])); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($bbContact['contact_hours'])) : ?>
                    <dt><?php echo theme_v4_e(theme_v4_t('Erreichbarkeit', 'Availability', $bbLocale)); ?></dt>
                    <dd><?php echo nl2br(theme_v4_e($bbContact['contact_hours'])); ?></dd>
                    <?php endif; ?>
                    <?php if ($bbEmail === '' && count($bbContact) === 0) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo theme_v4_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer hinterlegt.', 'Contact details are maintained in the theme customizer.', $bbLocale)); ?></dd>
                    <?php endif; ?>
                </dl>
            </aside>
        </div>
    </div>
</main>
