<?php
declare(strict_types=1);

/**
 * Rundschau – Kontaktseite (365CMS 4.0.0)
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Kopf, Formular und Redaktionskontakt an.
 * header.php öffnet bereits <main id="main-content"> – hier deshalb kein eigenes <main>.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 *
 * @package Rundschau_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$rsLocale        = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;

$rsHeading = $isContactPage
    ? theme_v4_t('Kontakt zur Redaktion', 'Contact the newsroom', $rsLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $rsLocale))));
$rsIntro = $isContactPage
    ? theme_v4_t('Hinweise, Leserbriefe, Korrekturen oder Termine: Schreiben Sie uns – wir melden uns so schnell wie möglich.', 'Tips, letters, corrections or events: write to us and we will get back to you as soon as possible.', $rsLocale)
    : '';

// Kontaktdaten aus dem Customizer-Bereich „contact“, E-Mail-Fallback: ADMIN_EMAIL.
$rsContact = [];
foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $rsKey) {
    $rsValue = rundschau_text('contact', $rsKey);
    if ($rsValue !== '') {
        $rsContact[$rsKey] = $rsValue;
    }
}
$rsEmail = (string) (filter_var($rsContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($rsEmail === '' && defined('ADMIN_EMAIL')) {
    $rsEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
$rsPhoneHref = isset($rsContact['contact_phone']) ? rundschau_safe_url('tel:' . preg_replace('/[^0-9+]/', '', $rsContact['contact_phone'])) : '';
?>

<header class="rs-pagehead">
    <div class="rs-container">
        <nav class="rs-breadcrumb" aria-label="Brotkrumen">
            <ol>
                <li><a href="<?php echo rundschau_e(rundschau_url('/')); ?>"><?php echo rundschau_e(theme_v4_t('Startseite', 'Home', $rsLocale)); ?></a></li>
                <li><span aria-current="page"><?php echo rundschau_e($rsHeading); ?></span></li>
            </ol>
        </nav>
        <p class="rs-pagehead__kicker"><?php echo rundschau_e(theme_v4_t('Kontakt', 'Contact', $rsLocale)); ?></p>
        <h1 class="rs-pagehead__title"><?php echo rundschau_e($rsHeading); ?></h1>
        <?php if ($rsIntro !== '') : ?>
            <p class="rs-pagehead__text"><?php echo rundschau_e($rsIntro); ?></p>
        <?php endif; ?>
    </div>
</header>

<div class="rs-container rs-layout rs-layout--sidebar theme-v4-contact">
    <div class="rs-layout__main theme-v4-contact__main">
        <?php if ($contactFormHtml !== '') : ?>
            <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
        <?php else : ?>
            <p class="rs-empty theme-v4-contact__empty"><?php echo rundschau_e(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $rsLocale)); ?></p>
        <?php endif; ?>
    </div>

    <aside class="rs-layout__aside theme-v4-contact__aside" aria-labelledby="rs-contact-aside-title">
        <h2 class="rs-box__title" id="rs-contact-aside-title"><?php echo rundschau_e(theme_v4_t('So erreichen Sie uns', 'How to reach us', $rsLocale)); ?></h2>
        <dl>
            <?php if ($rsEmail !== '') : ?>
                <dt><?php echo rundschau_e(theme_v4_t('E-Mail', 'Email', $rsLocale)); ?></dt>
                <dd><a href="mailto:<?php echo rundschau_e($rsEmail); ?>"><?php echo rundschau_e($rsEmail); ?></a></dd>
            <?php endif; ?>
            <?php if (!empty($rsContact['contact_phone'])) : ?>
                <dt><?php echo rundschau_e(theme_v4_t('Telefon', 'Phone', $rsLocale)); ?></dt>
                <dd><?php if ($rsPhoneHref !== '') : ?><a href="<?php echo rundschau_e($rsPhoneHref); ?>"><?php echo rundschau_e($rsContact['contact_phone']); ?></a><?php else : ?><?php echo rundschau_e($rsContact['contact_phone']); ?><?php endif; ?></dd>
            <?php endif; ?>
            <?php if (!empty($rsContact['contact_address'])) : ?>
                <dt><?php echo rundschau_e(theme_v4_t('Adresse', 'Address', $rsLocale)); ?></dt>
                <dd><?php echo nl2br(rundschau_e($rsContact['contact_address'])); ?></dd>
            <?php endif; ?>
            <?php if (!empty($rsContact['contact_hours'])) : ?>
                <dt><?php echo rundschau_e(theme_v4_t('Erreichbarkeit', 'Availability', $rsLocale)); ?></dt>
                <dd><?php echo nl2br(rundschau_e($rsContact['contact_hours'])); ?></dd>
            <?php endif; ?>
            <?php if ($rsEmail === '' && $rsContact === []) : ?>
                <dd class="theme-v4-contact__empty"><?php echo rundschau_e(theme_v4_t('Kontaktdaten werden im Theme-Customizer (Bereich „Kontakt“) hinterlegt.', 'Contact details are maintained in the theme customizer (section “Contact”).', $rsLocale)); ?></dd>
            <?php endif; ?>
        </dl>
    </aside>
</div>
