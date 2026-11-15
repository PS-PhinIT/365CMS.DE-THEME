<?php
/**
 * PTC Theme – Registrierung (Legacy-Modus `auth_slug_mode = legacy`, Route GET /register)
 *
 * ThemeManager::render() bindet header.php / footer.php automatisch ein. Das Formular sendet an den Core
 * (POST /register → PublicRouter::handleRegister() → \CMS\Auth::register()); Passwort-Richtlinie, Rate-Limit,
 * CSRF und neutrale Meldungen (keine Hinweise auf bereits vergebene Adressen) liegen dort.
 *
 * Variablen des Routers: $showcase (Login-Showcase, 4.1.0).
 *
 * @package PTC_Theme
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$siteUrl   = ptc_site_url();
$siteTitle = ptc_site_title();
$logoUrl   = theme_v4_site_logo(ptc_safe_url((string) ptc_customizer_get('header', 'logo_url', '')));
$error     = theme_get_flash('error');
$success   = theme_get_flash('success');

// Altwerte aus der Sitzung (der Core leitet nach einem Fehler per GET zurück; $_POST ist dann leer).
$oldValues = is_array($_SESSION['auth_form_old']['register'] ?? null) ? $_SESSION['auth_form_old']['register'] : [];
unset($_SESSION['auth_form_old']['register']);
$savedUsername = (string) ($oldValues['username'] ?? '');
$savedEmail    = (string) ($oldValues['email'] ?? '');
$savedTerms    = ($oldValues['terms'] ?? '0') === '1';

// Registrierung aktiv? Pflicht-Checkbox? (Einstellungen der Core-Anmeldeseiten)
$registrationEnabled = true;
$disabledMessage     = 'Die Registrierung ist aktuell deaktiviert.';
$requireTerms        = true;
try {
    $authPages           = \CMS\Services\CmsAuthPageService::getInstance();
    $authSettings        = $authPages->getSettings();
    $registrationEnabled = $authPages->isRegistrationEnabled($authSettings);
    $disabledMessage     = trim((string) ($authSettings['register_disabled_message'] ?? '')) ?: $disabledMessage;
    $requireTerms        = (string) ($authSettings['register_require_terms'] ?? '1') === '1';
} catch (\Throwable) {
}

// Passwort-Richtlinie (4.1.0, Einstellungen → Sicherheit) statt fest verdrahteter 12 Zeichen.
$minLength   = 12;
$policyParts = [];
try {
    if (class_exists('\\CMS\\Services\\Security\\PasswordPolicyService')) {
        $policy    = \CMS\Services\Security\PasswordPolicyService::getInstance()->definition();
        $minLength = (int) $policy['min_length'];
        foreach (['require_uppercase' => 'Großbuchstabe', 'require_lowercase' => 'Kleinbuchstabe', 'require_digit' => 'Ziffer', 'require_special' => 'Sonderzeichen'] as $flag => $label) {
            if (!empty($policy[$flag])) {
                $policyParts[] = $label;
            }
        }
    }
} catch (\Throwable) {
    $policyParts = ['Groß-/Kleinbuchstaben', 'Ziffer', 'Sonderzeichen'];
}
$passwordHint = 'Mindestens ' . $minLength . ' Zeichen' . ($policyParts !== [] ? ', mit ' . implode(', ', $policyParts) : '') . '.';

// Rechtstexte nur verlinken, wenn die Seiten veröffentlicht sind.
$privacyUrl = $siteUrl . '/datenschutz';
$termsUrl   = $siteUrl . '/agb';
$hasPrivacy = theme_v4_link_available($privacyUrl);
$hasTerms   = theme_v4_link_available($termsUrl);
?>

<section class="ptc-auth-section">
    <div class="ptc-auth-wrap">

        <!-- Auth Card -->
        <div class="ptc-auth-card">

            <!-- Logo -->
            <div class="ptc-auth-logo">
                <?php if ($logoUrl !== ''): ?>
                    <?php echo theme_v4_img($logoUrl, html_entity_decode($siteTitle, ENT_QUOTES, 'UTF-8'), ['class' => 'ptc-auth-logo-img', 'lcp' => true]); ?>
                <?php else: ?>
                    <svg width="52" height="52" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <circle cx="18" cy="18" r="18" fill="var(--color-primary)"/>
                        <path d="M10 12h5v3h-5zM10 17h5v3h-5zM10 22h5v3h-5zM17 12h9v3h-9zM17 17h7v3h-7zM17 22h5v3h-5z" fill="var(--color-accent)"/>
                    </svg>
                <?php endif; ?>
                <h1 class="ptc-auth-title">Registrieren</h1>
                <p>Erstelle dein kostenloses Konto</p>
            </div>

            <!-- Flash Messages (Texte des Cores, neutral formuliert) -->
            <?php if (trim($error) !== ''): ?>
                <div class="ptc-alert ptc-alert-error" role="alert">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (trim($success) !== ''): ?>
                <div class="ptc-alert ptc-alert-success" role="status">
                    <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!$registrationEnabled): ?>
                <div class="ptc-alert ptc-alert-info" role="status">
                    <?php echo htmlspecialchars($disabledMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php else: ?>
            <!-- Register Form -->
            <form method="POST" action="<?php echo htmlspecialchars($siteUrl . '/register', ENT_QUOTES, 'UTF-8'); ?>">
                <?php theme_csrf_field('register'); ?>

                <div class="ptc-form-group">
                    <label class="ptc-form-label" for="username">Benutzername</label>
                    <input class="ptc-form-control"
                           type="text"
                           id="username"
                           name="username"
                           autocomplete="username"
                           required
                           minlength="3"
                           maxlength="60"
                           pattern="[a-zA-Z0-9_\-]+"
                           aria-describedby="username-hint"
                           value="<?php echo htmlspecialchars($savedUsername, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="ptc-form-hint" id="username-hint">Nur Buchstaben, Zahlen, - und _ erlaubt.</span>
                </div>

                <div class="ptc-form-group">
                    <label class="ptc-form-label" for="email">E-Mail-Adresse</label>
                    <input class="ptc-form-control"
                           type="email"
                           id="email"
                           name="email"
                           autocomplete="email"
                           required
                           value="<?php echo htmlspecialchars($savedEmail, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="ptc-form-group">
                    <label class="ptc-form-label" for="password">Passwort</label>
                    <input class="ptc-form-control"
                           type="password"
                           id="password"
                           name="password"
                           autocomplete="new-password"
                           required
                           minlength="<?php echo (int) $minLength; ?>"
                           aria-describedby="password-hint">
                    <span class="ptc-form-hint" id="password-hint"><?php echo htmlspecialchars($passwordHint, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div class="ptc-form-group">
                    <label class="ptc-form-label" for="password_confirm">Passwort bestätigen</label>
                    <input class="ptc-form-control"
                           type="password"
                           id="password_confirm"
                           name="password2"
                           autocomplete="new-password"
                           required>
                </div>

                <!-- Datenschutz-Checkbox -->
                <div class="ptc-form-group ptc-form-checkbox">
                    <input type="checkbox" id="privacy" name="terms" value="1"<?php echo $requireTerms ? ' required' : ''; ?><?php echo $savedTerms ? ' checked' : ''; ?>>
                    <label for="privacy">
                        Ich stimme der
                        <?php if ($hasPrivacy): ?>
                            <a href="<?php echo htmlspecialchars($privacyUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Datenschutzerklärung<span class="theme-v4-sr-only"> (öffnet in neuem Fenster)</span></a>
                        <?php else: ?>
                            Datenschutzerklärung
                        <?php endif; ?>
                        zu und akzeptiere die
                        <?php if ($hasTerms): ?>
                            <a href="<?php echo htmlspecialchars($termsUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Nutzungsbedingungen<span class="theme-v4-sr-only"> (öffnet in neuem Fenster)</span></a>.
                        <?php else: ?>
                            Nutzungsbedingungen.
                        <?php endif; ?>
                    </label>
                </div>

                <button type="submit" class="btn-ptc btn-ptc-accent ptc-auth-submit">
                    Konto erstellen
                </button>
            </form>
            <?php endif; ?>

            <!-- Footer Links -->
            <div class="ptc-auth-footer">
                <p>Bereits registriert?
                    <a href="<?php echo htmlspecialchars($siteUrl . '/login', ENT_QUOTES, 'UTF-8'); ?>">Jetzt anmelden</a>
                </p>
                <p>
                    <a href="<?php echo htmlspecialchars($siteUrl . '/', ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true">←</span> Zurück zur Startseite</a>
                </p>
            </div>

        </div><!-- /.ptc-auth-card -->

        <?php
        // Login-Showcase (365CMS 4.1.0) für die Registrierung, Stile aus css/cms-v4.css.
        if (isset($showcase) && class_exists('\\CMS\\Services\\CmsAuthPageService')) {
            \CMS\Services\CmsAuthPageService::getInstance()->renderShowcase(is_array($showcase) ? $showcase : null, 'register', 'legacy');
        }
        ?>

    </div>
</section>
