<?php
declare(strict_types=1);

/**
 * Feder – Kontaktseite (365CMS 4.0.0)
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Überschrift, Formular und Kontaktdaten an.
 * header.php öffnet <main id="main-content"> bereits – deshalb hier kein eigenes <main>.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 *
 * @package Feder_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$fdrLocale       = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$fdrHeading = $isContactPage
    ? theme_v4_t('Schreiben Sie mir', 'Get in touch', $fdrLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $fdrLocale))));
$fdrIntro = $isContactPage
    ? theme_v4_t('Fragen, Hinweise zu einem Text oder ein Themenvorschlag? Ich freue mich über jede Nachricht und antworte so bald wie möglich.', 'Questions, feedback on a post or a topic suggestion? I am happy to hear from you and will reply as soon as I can.', $fdrLocale)
    : '';

// Kontaktdaten: Customizer-Bereich „Kontakt“; E-Mail-Fallback: Autor:innen-E-Mail des Themes, dann ADMIN_EMAIL.
$fdrContact = [];
foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $fdrKey) {
    $fdrValue = feder_text('contact', $fdrKey);
    if ($fdrValue !== '') {
        $fdrContact[$fdrKey] = $fdrValue;
    }
}
$fdrEmail = '';
foreach ([$fdrContact['contact_email'] ?? '', feder_text('feder_author', 'social_email'), defined('ADMIN_EMAIL') ? (string) ADMIN_EMAIL : ''] as $fdrCandidate) {
    $fdrEmail = (string) (filter_var(trim($fdrCandidate), FILTER_VALIDATE_EMAIL) ?: '');
    if ($fdrEmail !== '') {
        break;
    }
}
?>

<section class="fdr-wide fdr-contact" aria-labelledby="fdr-contact-title">
    <div class="theme-v4-contact">
        <div class="theme-v4-contact__main">
            <p class="fdr-kicker"><?php echo feder_e(theme_v4_t('Kontakt', 'Contact', $fdrLocale)); ?></p>
            <h1 class="fdr-archive-head__title" id="fdr-contact-title"><?php echo feder_e($fdrHeading); ?></h1>
            <?php if ($fdrIntro !== '') : ?>
                <p class="theme-v4-contact__intro"><?php echo feder_e($fdrIntro); ?></p>
            <?php endif; ?>

            <?php if ($contactFormHtml !== '') : ?>
                <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
            <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo feder_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $fdrLocale)); ?></p>
            <?php endif; ?>
        </div>

        <aside class="theme-v4-contact__aside" aria-labelledby="fdr-contact-aside-title">
            <h2 id="fdr-contact-aside-title"><?php echo feder_e(theme_v4_t('So erreichen Sie mich', 'How to reach me', $fdrLocale)); ?></h2>
            <dl>
                <?php if ($fdrEmail !== '') : ?>
                    <dt><?php echo feder_e(theme_v4_t('E-Mail', 'Email', $fdrLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo feder_e($fdrEmail); ?>"><?php echo feder_e($fdrEmail); ?></a></dd>
                <?php endif; ?>
                <?php if (!empty($fdrContact['contact_phone'])) : ?>
                    <dt><?php echo feder_e(theme_v4_t('Telefon', 'Phone', $fdrLocale)); ?></dt>
                    <dd><?php echo feder_e($fdrContact['contact_phone']); ?></dd>
                <?php endif; ?>
                <?php if (!empty($fdrContact['contact_address'])) : ?>
                    <dt><?php echo feder_e(theme_v4_t('Adresse', 'Address', $fdrLocale)); ?></dt>
                    <dd><?php echo nl2br(feder_e($fdrContact['contact_address']), false); ?></dd>
                <?php endif; ?>
                <?php if (!empty($fdrContact['contact_hours'])) : ?>
                    <dt><?php echo feder_e(theme_v4_t('Erreichbarkeit', 'Availability', $fdrLocale)); ?></dt>
                    <dd><?php echo nl2br(feder_e($fdrContact['contact_hours']), false); ?></dd>
                <?php endif; ?>
                <?php if ($fdrEmail === '' && count($fdrContact) === 0) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo feder_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer unter „Kontakt“ hinterlegt.', 'Contact details are maintained in the theme customizer under “Contact”.', $fdrLocale)); ?></dd>
                <?php endif; ?>
            </dl>
        </aside>
    </div>
</section>
