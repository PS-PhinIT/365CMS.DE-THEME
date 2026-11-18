<?php
/**
 * Login-Seite – MedCare Pro Theme (Legacy-Modus, /login)
 *
 * Rendert der Core nur bei `auth_slug_mode = legacy` (PublicRouter::redirectLegacyLogin()) und übergibt
 * `$login_redirect` und `$showcase`. Das Formular postet an den Core (PublicRouter::handleLogin(),
 * CSRF-Aktion `login`), der ausschließlich über `\CMS\Auth::instance()->login()` anmeldet, Fehlversuche
 * drosselt und neutrale Meldungen („Ungültige Anmeldedaten.“) als Session-Flash zurückgibt. Das Theme
 * prüft keine Zugangsdaten selbst und gibt keine Exception-Texte aus.
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

$safe    = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// POST /login bzw. /register verarbeitet der Core (PublicRouter::handleLogin/handleRegister,
// CSRF-Aktion 'login'/'register'); Meldungen kommen als Session-Flash zurück.
$error   = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : null;
$success = isset($_SESSION['success']) && is_string($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['error'], $_SESSION['success']);

// Eingaben des letzten Versuchs (der Core legt sie in auth_form_old ab) – nie das Passwort.
$oldInput = is_array($_SESSION['auth_form_old']['login'] ?? null) ? $_SESSION['auth_form_old']['login'] : [];
unset($_SESSION['auth_form_old']['login']);

try {
    $csrfToken = \CMS\Security::instance()->generateToken('login');
} catch (\Throwable) {
    $csrfToken = '';
}

$postedLogin   = mb_substr(trim((string) ($oldInput['username'] ?? $oldInput['email'] ?? '')), 0, 254);
$loginRedirect = isset($login_redirect) && is_string($login_redirect) ? trim($login_redirect) : '';
// Nur interne Ziele (der Core prüft das Ziel beim Login zusätzlich).
$loginRedirect = str_starts_with($loginRedirect, '/') && !str_starts_with($loginRedirect, '//') && !str_contains($loginRedirect, '\\')
    && preg_match('/[\x00-\x1F\x7F]/', $loginRedirect) !== 1 ? $loginRedirect : '';
$loginAction   = $safe(theme_route_url('login'));
$forgotUrl     = $safe(mc_href('/forgot-password'));
$registerUrl   = mc_registration_enabled() ? $safe(theme_route_url('register')) : '';

?>
<main id="main" class="mc-main mc-auth-page">
    <div class="mc-container">
        <div class="mc-form-card mc-auth-card">

            <div class="mc-auth-card__head">
                <span class="mc-auth-card__icon" aria-hidden="true">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                         focusable="false" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="9" rx="2"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                </span>
                <h1 class="mc-auth-card__title">Anmelden</h1>
                <p class="mc-auth-card__lead">Willkommen zurück bei <?php echo $safe(mc_site_title()); ?></p>
            </div>

            <?php if (!empty($error)) : ?>
            <div class="mc-alert mc-alert-error" role="alert"><?php echo $safe($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)) : ?>
            <div class="mc-alert mc-alert-success" role="status"><?php echo $safe($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $loginAction; ?>" novalidate class="mc-auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $safe($csrfToken); ?>">
                <?php if ($loginRedirect !== '') : ?>
                <input type="hidden" name="redirect" value="<?php echo $safe($loginRedirect); ?>">
                <?php endif; ?>

                <div class="mc-form-group">
                    <label for="login-email" class="mc-label">
                        E-Mail-Adresse oder Benutzername <span class="mc-required" aria-hidden="true">*</span>
                    </label>
                    <input id="login-email"
                           type="text"
                           name="username"
                           class="mc-input"
                           value="<?php echo $safe($postedLogin); ?>"
                           autocomplete="username"
                           autocapitalize="none"
                           spellcheck="false"
                           required
                           aria-required="true"
                           placeholder="ihre@email.de">
                </div>

                <div class="mc-form-group">
                    <label for="login-password" class="mc-label">
                        Passwort <span class="mc-required" aria-hidden="true">*</span>
                    </label>
                    <input id="login-password"
                           type="password"
                           name="password"
                           class="mc-input"
                           autocomplete="current-password"
                           required
                           aria-required="true"
                           placeholder="Ihr Passwort">
                </div>

                <button type="submit" class="mc-btn mc-btn-primary mc-btn-block">
                    Anmelden
                </button>
            </form>

            <hr class="mc-form-divider">

            <div class="mc-form-links">
                <a href="<?php echo $forgotUrl; ?>">Passwort vergessen?</a>
                <?php if ($registerUrl !== '') : ?>
                <a href="<?php echo $registerUrl; ?>">Jetzt registrieren</a>
                <?php endif; ?>
            </div>

            <p class="mc-dsgvo-note">
                <span aria-hidden="true">🔒</span>
                Ihre Anmeldedaten werden verschlüsselt übertragen und gemäß DSGVO &amp; § 203 StGB geschützt.
            </p>
        </div>

        <?php
        // Login-Showcase (365CMS 4.1.0): dieselben Inhalte wie auf der Core-Loginseite, Stile aus css/cms-v4.css.
        if (isset($showcase) && class_exists('\\CMS\\Services\\CmsAuthPageService')) {
            \CMS\Services\CmsAuthPageService::getInstance()->renderShowcase(is_array($showcase) ? $showcase : null, 'login', 'legacy');
        }
        ?>
    </div>
</main>
