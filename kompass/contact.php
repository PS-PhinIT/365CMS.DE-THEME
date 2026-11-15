<?php
declare(strict_types=1);

/**
 * Kompass – Kontaktseite (365CMS 4.0.0)
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Seitenkopf, Formular und Kontaktdaten an.
 * header.php öffnet <main id="main-content"> bereits – deshalb hier kein eigenes <main>.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 *
 * Kontaktdaten: vorhandener Customizer-Bereich „Service & Kontakt“ (kp_service), E-Mail-Fallback ADMIN_EMAIL.
 *
 * @package Kompass_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$kpLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$kpHeading = $isContactPage
    ? theme_v4_t('Kontakt aufnehmen', 'Get in touch', $kpLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $kpLocale))));
$kpIntro = $isContactPage
    ? kompass_text('kp_service', 'service_text', theme_v4_t('Wir helfen Ihnen gern persönlich weiter.', 'We are happy to help you personally.', $kpLocale))
    : '';

$kpService = kompass_service();
$kpEmail = $kpService['email'];
if ($kpEmail === '' && defined('ADMIN_EMAIL')) {
    $kpEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
?>

<header class="kp-pagehead">
    <div class="kp-container">
        <nav class="kp-breadcrumb" aria-label="<?php echo kompass_e(theme_v4_t('Brotkrumen', 'Breadcrumb', $kpLocale)); ?>">
            <ol>
                <li><a href="<?php echo kompass_e(kompass_url('/')); ?>"><?php echo kompass_e(theme_v4_t('Start', 'Home', $kpLocale)); ?></a></li>
                <li><span aria-current="page"><?php echo kompass_e($kpHeading); ?></span></li>
            </ol>
        </nav>
        <h1 class="kp-pagehead__title"><?php echo kompass_e($kpHeading); ?></h1>
        <?php if ($kpIntro !== '') : ?>
            <p class="kp-pagehead__lead"><?php echo kompass_e($kpIntro); ?></p>
        <?php endif; ?>
    </div>
</header>

<div class="kp-container kp-contactpage">
    <div class="theme-v4-contact">
        <div class="theme-v4-contact__main">
            <?php if ($contactFormHtml !== '') : ?>
                <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
            <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo kompass_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $kpLocale)); ?></p>
            <?php endif; ?>
        </div>

        <aside class="theme-v4-contact__aside" aria-labelledby="kp-contact-aside-title">
            <h2 id="kp-contact-aside-title"><?php echo kompass_e(theme_v4_t('So erreichen Sie uns', 'How to reach us', $kpLocale)); ?></h2>
            <dl>
                <?php if ($kpService['phone_href'] !== '') : ?>
                    <dt><?php echo kompass_e(theme_v4_t('Telefon', 'Phone', $kpLocale)); ?></dt>
                    <dd><a href="<?php echo kompass_e($kpService['phone_href']); ?>"><?php echo kompass_e($kpService['phone']); ?></a></dd>
                <?php endif; ?>
                <?php if ($kpEmail !== '') : ?>
                    <dt><?php echo kompass_e(theme_v4_t('E-Mail', 'Email', $kpLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo kompass_e($kpEmail); ?>"><?php echo kompass_e($kpEmail); ?></a></dd>
                <?php endif; ?>
                <?php if ($kpService['hours'] !== []) : ?>
                    <dt><?php echo kompass_e(theme_v4_t('Sprechzeiten', 'Opening hours', $kpLocale)); ?></dt>
                    <dd><?php echo implode('<br>', array_map('kompass_e', $kpService['hours'])); ?></dd>
                <?php endif; ?>
                <?php if ($kpService['address'] !== []) : ?>
                    <dt><?php echo kompass_e(theme_v4_t('Anschrift', 'Address', $kpLocale)); ?></dt>
                    <dd><?php echo kompass_e(kompass_organisation()); ?><br><?php echo implode('<br>', array_map('kompass_e', $kpService['address'])); ?></dd>
                <?php endif; ?>
                <?php if ($kpEmail === '' && $kpService['phone_href'] === '' && $kpService['hours'] === [] && $kpService['address'] === []) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo kompass_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer unter „Service & Kontakt“ hinterlegt.', 'Contact details are maintained in the theme customizer under “Service & Kontakt”.', $kpLocale)); ?></dd>
                <?php endif; ?>
            </dl>
        </aside>
    </div>
</div>
