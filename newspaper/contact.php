<?php
/**
 * CMS Newspaper Theme – Kontaktseite (365CMS 4.0.0)
 *
 * Das Formular liefert der Core (Inhalte → Formulare): Prüfung, CSRF, Honeypot, Einwilligung, Speicherung
 * und Mails laufen dort. Diese Vorlage ordnet nur Seitenkopf, Formular und Redaktionskontakt an.
 * header.php öffnet bereits <main id="main-content"> – hier deshalb kein eigenes <main>.
 *
 * Variablen des Routers:
 *   $contactForm     – array|null  Formular-Definition
 *   $contactFormHtml – string      fertig gerendertes, escaptes Formular inkl. Meldungen
 *   $isContactPage   – bool        allgemeine Kontaktseite (/contact, /kontakt) statt /contact/<schlüssel>
 *   $title           – string      Seitentitel
 *
 * @package CmsNewspaper_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$newsLocale      = theme_v4_locale($contentLocale ?? null);
$contactForm     = isset($contactForm) && is_array($contactForm) ? $contactForm : null;
$contactFormHtml = isset($contactFormHtml) ? (string) $contactFormHtml : '';
$isContactPage   = !isset($isContactPage) || (bool) $isContactPage;
$safe            = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

$newsHeading = $isContactPage
    ? theme_v4_t('Kontakt zur Redaktion', 'Contact the newsroom', $newsLocale)
    : trim((string) ($title ?? ($contactForm['title'] ?? theme_v4_t('Kontakt', 'Contact', $newsLocale))));
$newsIntro = $isContactPage
    ? theme_v4_t('Themenvorschläge, Korrekturen oder Fragen zu Skripten und Artikeln: Schreiben Sie uns – wir melden uns zeitnah.', 'Story tips, corrections or questions about scripts and articles: write to us and we will get back to you shortly.', $newsLocale)
    : '';

// Kontaktdaten aus dem Customizer-Bereich „contact“, E-Mail-Fallback: ADMIN_EMAIL.
$newsContact = [];
foreach (['contact_email', 'contact_phone', 'contact_address', 'contact_hours'] as $newsKey) {
    $newsValue = trim((string) news_get_setting('contact', $newsKey, ''));
    if ($newsValue !== '') {
        $newsContact[$newsKey] = $newsValue;
    }
}
$newsEmail = (string) (filter_var($newsContact['contact_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '');
if ($newsEmail === '' && defined('ADMIN_EMAIL')) {
    $newsEmail = (string) (filter_var(trim((string) ADMIN_EMAIL), FILTER_VALIDATE_EMAIL) ?: '');
}
?>

<section class="news-page-hero" aria-labelledby="news-contact-title">
    <div class="news-container">
        <span class="news-kicker"><?php echo $safe(theme_v4_t('Kontakt', 'Contact', $newsLocale)); ?></span>
        <h1 id="news-contact-title"><?php echo $safe($newsHeading); ?></h1>
        <?php if ($newsIntro !== '') : ?>
            <p><?php echo $safe($newsIntro); ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="news-page-content">
    <div class="news-container theme-v4-contact">
        <div class="theme-v4-contact__main">
            <?php if ($contactFormHtml !== '') : ?>
                <?php echo $contactFormHtml; // FormRenderer des Cores: vollständig escaped, CSRF + Honeypot ?>
            <?php else : ?>
                <p class="news-prose-empty theme-v4-contact__empty"><?php echo $safe(theme_v4_t('Das Kontaktformular ist derzeit nicht verfügbar.', 'The contact form is currently unavailable.', $newsLocale)); ?></p>
            <?php endif; ?>
        </div>

        <aside class="theme-v4-contact__aside" aria-labelledby="news-contact-aside-title">
            <h2 id="news-contact-aside-title"><?php echo $safe(theme_v4_t('So erreichen Sie uns', 'How to reach us', $newsLocale)); ?></h2>
            <dl>
                <?php if ($newsEmail !== '') : ?>
                    <dt><?php echo $safe(theme_v4_t('E-Mail', 'Email', $newsLocale)); ?></dt>
                    <dd><a href="mailto:<?php echo $safe($newsEmail); ?>"><?php echo $safe($newsEmail); ?></a></dd>
                <?php endif; ?>
                <?php if (!empty($newsContact['contact_phone'])) : ?>
                    <dt><?php echo $safe(theme_v4_t('Telefon', 'Phone', $newsLocale)); ?></dt>
                    <dd><?php echo $safe($newsContact['contact_phone']); ?></dd>
                <?php endif; ?>
                <?php if (!empty($newsContact['contact_address'])) : ?>
                    <dt><?php echo $safe(theme_v4_t('Adresse', 'Address', $newsLocale)); ?></dt>
                    <dd><?php echo nl2br($safe($newsContact['contact_address'])); ?></dd>
                <?php endif; ?>
                <?php if (!empty($newsContact['contact_hours'])) : ?>
                    <dt><?php echo $safe(theme_v4_t('Erreichbarkeit', 'Availability', $newsLocale)); ?></dt>
                    <dd><?php echo nl2br($safe($newsContact['contact_hours'])); ?></dd>
                <?php endif; ?>
                <?php if ($newsEmail === '' && $newsContact === []) : ?>
                    <dd class="theme-v4-contact__empty"><?php echo $safe(theme_v4_t('Kontaktdaten werden im Theme-Customizer (Bereich „Kontakt“) hinterlegt.', 'Contact details are maintained in the theme customizer (section “Contact”).', $newsLocale)); ?></dd>
                <?php endif; ?>
            </dl>
        </aside>
    </div>
</div>
