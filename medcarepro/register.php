<?php
/**
 * Registrierung – MedCare Pro Theme
 *
 * Unterstützt Patienten- und Arzt-Registrierung via GET-Parameter ?type=doctor (nur Darstellung).
 *
 * Legacy-Modus (`auth_slug_mode = legacy`, PublicRouter::redirectLegacyRegister()): Der Core übergibt
 * `$showcase`. Das Formular postet an den Core (PublicRouter::handleRegister(), CSRF-Aktion `register`,
 * Rate-Limit, Passwort-Richtlinie), der ausschließlich über `\CMS\Auth::instance()->register()` anlegt und
 * neutrale Meldungen als Session-Flash zurückgibt – ohne Hinweis, ob eine E-Mail-Adresse bereits existiert.
 *
 * @package MedCarePro_Theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (theme_is_logged_in()) {
    header('Location: ' . theme_route_url('member'));
    exit;
}

$isDoctor       = ($_GET['type'] ?? '') === 'doctor';
$safe           = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$registrationOn = mc_registration_enabled();

// POST /login bzw. /register verarbeitet der Core (PublicRouter::handleLogin/handleRegister,
// CSRF-Aktion 'login'/'register'); Meldungen kommen als Session-Flash zurück.
$error   = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : null;
$success = isset($_SESSION['success']) && is_string($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['error'], $_SESSION['success']);

// Eingaben des letzten Versuchs (der Core legt sie in auth_form_old ab) – nie Passwörter.
$oldInput = is_array($_SESSION['auth_form_old']['register'] ?? null) ? $_SESSION['auth_form_old']['register'] : [];
unset($_SESSION['auth_form_old']['register']);
$postedEmail    = mb_substr(trim((string) ($oldInput['email'] ?? '')), 0, 254);
$postedUsername = mb_substr(trim((string) ($oldInput['username'] ?? '')), 0, 80);
$postedTerms    = ($oldInput['terms'] ?? '0') === '1';

try {
    $csrfToken = \CMS\Security::instance()->generateToken('register');
} catch (\Throwable) {
    $csrfToken = '';
}

$privText   = (string) mc_get_setting('dsgvo_medical', 'privacy_form_text', 'Ihre Daten werden gemäß DSGVO und § 203 StGB vertraulich behandelt.');
$loginUrl   = $safe(theme_route_url('login'));
$registerAction = $safe(theme_route_url('register'));
$privacyUrl = theme_v4_link_available(theme_route_url('privacy')) ? $safe(theme_route_url('privacy')) : ''; // nur veröffentlichte Seite

?>
<main id="main" class="mc-main mc-auth-page">
    <div class="mc-container">
        <div class="mc-form-card mc-auth-card mc-auth-card--wide">

            <div class="mc-auth-card__head">
                <span class="mc-auth-card__icon" aria-hidden="true">
                    <?php if ($isDoctor) : ?>
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                             focusable="false" aria-hidden="true">
                            <circle cx="12" cy="7" r="3"/>
                            <path d="M5 21c0-3.866 3.134-7 7-7s7 3.134 7 7"/>
                            <path d="M16 14h2v3h-2zM16 17h2v3a1 1 0 0 1-1 1 1 1 0 0 1-1-1z"/>
                        </svg>
                    <?php else : ?>
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                             focusable="false" aria-hidden="true">
                            <path d="M10 4h4v4h4v4h-4v4h-4v-4H6V8h4z"/>
                            <path d="M4 21V12a8 8 0 0 1 16 0v9"/>
                        </svg>
                    <?php endif; ?>
                </span>
                <h1 class="mc-auth-card__title">
                    <?php echo $isDoctor ? 'Als Arzt registrieren' : 'Konto erstellen'; ?>
                </h1>
                <p class="mc-auth-card__lead">
                    <?php echo $isDoctor
                        ? 'Erstellen Sie Ihr Arztprofil und erreichen Sie neue Patienten.'
                        : 'Kostenlos anmelden und Ärzte finden.'; ?>
                </p>
            </div>

            <?php // Umschalter sind Links (Seitenwechsel), daher aria-current statt Tab-Rollen. ?>
            <nav class="mc-auth-toggle" aria-label="Registrierungs-Typ">
                <a href="?type=patient"
                   class="mc-auth-toggle__btn<?php echo !$isDoctor ? ' is-active' : ''; ?>"
                   <?php echo !$isDoctor ? 'aria-current="page"' : ''; ?>>
                    Patient
                </a>
                <a href="?type=doctor"
                   class="mc-auth-toggle__btn<?php echo $isDoctor ? ' is-active' : ''; ?>"
                   <?php echo $isDoctor ? 'aria-current="page"' : ''; ?>>
                    Arzt / Therapeut
                </a>
            </nav>

            <?php if (!empty($error)) : ?>
            <div class="mc-alert mc-alert-error" role="alert"><?php echo $safe($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)) : ?>
            <div class="mc-alert mc-alert-success" role="status">
                <?php echo $safe($success); ?><br>
                <a href="<?php echo $loginUrl; ?>" class="mc-alert-link">Jetzt anmelden →</a>
            </div>
            <?php endif; ?>

            <?php if (empty($success) && !$registrationOn) : ?>
            <div class="mc-alert mc-alert-error" role="status">Die Registrierung ist derzeit deaktiviert.</div>
            <?php endif; ?>

            <?php if (empty($success) && $registrationOn) : ?>
            <form method="POST" action="<?php echo $registerAction; ?>" novalidate class="mc-auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $safe($csrfToken); ?>">

                <div class="mc-honeypot" aria-hidden="true">
                    <label for="honeypot_field">Dieses Feld leer lassen</label>
                    <input id="honeypot_field" type="text" name="honeypot_field" tabindex="-1" autocomplete="off">
                </div>

                <div class="mc-form-group">
                    <label for="reg-email" class="mc-label">
                        E-Mail-Adresse <span class="mc-required" aria-hidden="true">*</span>
                    </label>
                    <input id="reg-email"
                           type="email"
                           name="email"
                           class="mc-input"
                           value="<?php echo $safe($postedEmail); ?>"
                           autocomplete="email"
                           required
                           aria-required="true"
                           placeholder="ihre@email.de">
                </div>

                <div class="mc-form-group">
                    <label for="reg-username" class="mc-label">
                        <?php echo $isDoctor ? 'Name / Praxisname' : 'Benutzername'; ?>
                        <span class="mc-required" aria-hidden="true">*</span>
                    </label>
                    <input id="reg-username"
                           type="text"
                           name="username"
                           class="mc-input"
                           value="<?php echo $safe($postedUsername); ?>"
                           autocomplete="username"
                           autocapitalize="none"
                           spellcheck="false"
                           required
                           aria-required="true"
                           aria-describedby="reg-username-hint"
                           placeholder="<?php echo $isDoctor ? 'praxis_mustermann' : 'max_mustermann'; ?>"
                           minlength="3">
                    <p class="mc-form-hint" id="reg-username-hint">3–50 Zeichen: Buchstaben, Zahlen und Unterstrich. Den Praxisnamen ergänzen Sie später im Profil.</p>
                </div>

                <div class="mc-form-group">
                    <label for="reg-password" class="mc-label">
                        Passwort <span class="mc-required" aria-hidden="true">*</span>
                    </label>
                    <input id="reg-password"
                           type="password"
                           name="password"
                           class="mc-input"
                           autocomplete="new-password"
                           required
                           aria-required="true"
                           placeholder="Mindestens 8 Zeichen"
                           aria-describedby="reg-password-hint"
                           minlength="8">
                    <p class="mc-form-hint" id="reg-password-hint">Mindestens 8 Zeichen; es gilt die Passwort-Richtlinie dieser Website.</p>
                </div>

                <div class="mc-form-group">
                    <label for="reg-password-confirm" class="mc-label">
                        Passwort bestätigen <span class="mc-required" aria-hidden="true">*</span>
                    </label>
                    <input id="reg-password-confirm"
                           type="password"
                           name="password2"
                           class="mc-input"
                           autocomplete="new-password"
                           required
                           aria-required="true"
                           placeholder="Passwort wiederholen">
                </div>

                <div class="mc-form-group mc-form-check">
                    <input id="reg-privacy"
                           type="checkbox"
                           name="terms"
                           value="1"
                           required
                           aria-required="true"
                           class="mc-checkbox"<?php echo $postedTerms ? ' checked' : ''; ?>>
                    <label for="reg-privacy" class="mc-checkbox-label">
                        Ich habe die
                        <?php if ($privacyUrl !== '') : ?>
                        <a href="<?php echo $privacyUrl; ?>" target="_blank" rel="noopener noreferrer">Datenschutzerklärung</a>
                        <?php else : ?>
                        Datenschutzerklärung
                        <?php endif; ?>
                        gelesen und stimme der Verarbeitung meiner Daten zu.
                        <span class="mc-required" aria-hidden="true">*</span>
                    </label>
                </div>

                <button type="submit" class="mc-btn mc-btn-primary mc-btn-block">
                    <?php echo $isDoctor ? 'Arztprofil erstellen' : 'Konto erstellen'; ?>
                </button>
            </form>
            <?php endif; ?>

            <hr class="mc-form-divider">
            <div class="mc-form-links mc-form-links--single">
                <a href="<?php echo $loginUrl; ?>">Bereits registriert? Anmelden</a>
            </div>
            <p class="mc-dsgvo-note">
                <span aria-hidden="true">🔒</span> <?php echo $safe($privText); ?>
            </p>
        </div>

        <?php
        // Login-Showcase (365CMS 4.1.0) für die Registrierung, Stile aus css/cms-v4.css.
        if (isset($showcase) && class_exists('\\CMS\\Services\\CmsAuthPageService')) {
            \CMS\Services\CmsAuthPageService::getInstance()->renderShowcase(is_array($showcase) ? $showcase : null, 'register', 'legacy');
        }
        ?>
    </div>
</main>
