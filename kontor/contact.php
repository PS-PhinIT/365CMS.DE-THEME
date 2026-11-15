<?php
declare(strict_types=1);

/**
 * Kontor – Kontaktseite (365CMS 4.0.0)
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
 * Kontaktdaten: vorhandener Customizer-Bereich „Kontaktdaten“ (kt_contact), E-Mail-Fallback ADMIN_EMAIL.
 *
 * @package Kontor_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$ktLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$ktHeading = $isContactPage
    ? theme_v4_t('Sprechen Sie mit uns', 'Talk to us', $ktLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $ktLocale))));
$ktIntro = $isContactPage
    ? theme_v4_t('Schildern Sie uns kurz Ihr Anliegen – wir melden uns in der Regel innerhalb eines Werktags.', 'Tell us briefly what you need – we usually reply within one business day.', $ktLocale)
    : '';

$ktContact = kontor_contact();
$ktEmail = $ktContact['email'];
if ($ktEmail === '' && defined('ADMIN_EMAIL')) {
    $ktEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
?>

<header class="kt-pagehero">
    <div class="kt-container">
        <nav class="kt-breadcrumb" aria-label="<?php echo kontor_e(theme_v4_t('Brotkrumen', 'Breadcrumb', $ktLocale)); ?>">
            <ol>
                <li><a href="<?php echo kontor_e(kontor_url('/')); ?>"><?php echo kontor_e(theme_v4_t('Start', 'Home', $ktLocale)); ?></a></li>
                <li><span aria-current="page"><?php echo kontor_e($ktHeading); ?></span></li>
            </ol>
        </nav>
        <h1 class="kt-pagehero__title"><?php echo kontor_e($ktHeading); ?></h1>
        <?php if ($ktIntro !== '') : ?>
            <p class="kt-pagehero__text"><?php echo kontor_e($ktIntro); ?></p>
        <?php endif; ?>
    </div>
</header>

<div class="kt-container kt-contactpage">
    <div class="theme-v4-contact">
        <div class="theme-v4-contact__main">
            <?php if ($contactFormHtml !== '') : ?>
                <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
            <?php else : ?>
                <p class="theme-v4-contact__empty"><?php echo kontor_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $ktLocale)); ?></p>
            <?php endif; ?>
        </div>

        <aside class="theme-v4-contact__aside" aria-labelledby="kt-contact-aside-title">
            <h2 id="kt-contact-aside-title"><?php echo kontor_e(theme_v4_t('So erreichen Sie uns', 'How to reach us', $ktLocale)); ?></h2>
            <dl>
                <?php if ($ktContact['phone_href'] !== '') : ?>
                    <dt><?php echo kontor_e(theme_v4_t('Telefon', 'Phone', $ktLocale)); ?></dt>
                    <dd><a href="<?php echo kontor_e($ktContact['phone_href']); ?>"><?php echo kontor_e($ktContact['phone']); ?></a></dd>
                <?php endif; ?>
                <?php if ($ktEmail !== '') : ?>
                    <dt><?php echo kontor_e(theme_v4_t('E-Mail', 'Email', $ktLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo kontor_e($ktEmail); ?>"><?php echo kontor_e($ktEmail); ?></a></dd>
                <?php endif; ?>
                <?php if ($ktContact['address'] !== []) : ?>
                    <dt><?php echo kontor_e(theme_v4_t('Adresse', 'Address', $ktLocale)); ?></dt>
                    <dd><?php echo kontor_e(kontor_company_name()); ?><br><?php echo implode('<br>', array_map('kontor_e', $ktContact['address'])); ?></dd>
                <?php endif; ?>
                <?php if ($ktContact['hours'] !== []) : ?>
                    <dt><?php echo kontor_e(theme_v4_t('Erreichbarkeit', 'Availability', $ktLocale)); ?></dt>
                    <dd><?php echo implode('<br>', array_map('kontor_e', $ktContact['hours'])); ?></dd>
                <?php endif; ?>
                <?php if ($ktEmail === '' && $ktContact['phone_href'] === '' && $ktContact['address'] === [] && $ktContact['hours'] === []) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo kontor_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer hinterlegt.', 'Contact details are maintained in the theme customizer.', $ktLocale)); ?></dd>
                <?php endif; ?>
            </dl>
        </aside>
    </div>
</div>
