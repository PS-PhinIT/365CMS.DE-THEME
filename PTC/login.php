<?php
/**
 * PTC Theme – Login (Legacy-Modus `auth_slug_mode = legacy`, Route GET /login)
 *
 * ThemeManager::render() bindet header.php / footer.php automatisch ein. Das Formular sendet an den Core
 * (POST /login → PublicRouter::handleLogin() → \CMS\Auth::login()); Rate-Limit, MFA, CSRF und neutrale
 * Fehlermeldungen liegen dort. Die Vorlage zeigt nur Flash-Meldungen und Altwerte aus der Sitzung.
 *
 * Variablen des Routers: $login_redirect (geprüftes Weiterleitungsziel), $showcase (Login-Showcase, 4.1.0).
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

// Altwerte legt der Core nach einem Fehlversuch in der Sitzung ab (nach dem Redirect ist $_POST leer).
$oldValues = is_array($_SESSION['auth_form_old']['login'] ?? null) ? $_SESSION['auth_form_old']['login'] : [];
unset($_SESSION['auth_form_old']['login']);
$savedUsername = (string) ($oldValues['username'] ?? $oldValues['email'] ?? '');
$redirectTo    = isset($login_redirect) && is_string($login_redirect) ? trim($login_redirect) : '';
// Zusätzlich zur Core-Prüfung: nur interne Pfade (kein `//host`, kein `/\host`, keine Steuerzeichen).
$redirectTo    = str_starts_with($redirectTo, '/') && !str_starts_with($redirectTo, '//') && !str_contains($redirectTo, '\\')
    && preg_match('/[\x00-\x1F\x7F]/', $redirectTo) !== 1 ? $redirectTo : '';
$forgotUrl     = $siteUrl . '/forgot-password';
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
                <h1 class="ptc-auth-title">Anmelden</h1>
                <p>Melde dich mit deinem Konto an</p>
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

            <!-- Login Form -->
            <form method="POST" action="<?php echo htmlspecialchars($siteUrl . '/login', ENT_QUOTES, 'UTF-8'); ?>">
                <?php theme_csrf_field('login'); ?>
                <?php if ($redirectTo !== ''): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectTo, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>

                <div class="ptc-form-group">
                    <label class="ptc-form-label" for="username">Benutzername oder E-Mail</label>
                    <input class="ptc-form-control"
                           type="text"
                           id="username"
                           name="username"
                           autocomplete="username"
                           required
                           value="<?php echo htmlspecialchars($savedUsername, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="ptc-form-group">
                    <label class="ptc-form-label" for="password">Passwort</label>
                    <input class="ptc-form-control"
                           type="password"
                           id="password"
                           name="password"
                           autocomplete="current-password"
                           required>
                </div>

                <div class="ptc-form-group ptc-form-checkbox">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Angemeldet bleiben</label>
                </div>

                <button type="submit" class="btn-ptc btn-ptc-accent ptc-auth-submit">
                    Anmelden
                </button>
            </form>

            <!-- Footer Links -->
            <div class="ptc-auth-footer">
                <p><a href="<?php echo htmlspecialchars($forgotUrl, ENT_QUOTES, 'UTF-8'); ?>">Passwort vergessen?</a></p>
                <p>Noch kein Konto?
                    <a href="<?php echo htmlspecialchars($siteUrl . '/register', ENT_QUOTES, 'UTF-8'); ?>">Jetzt registrieren</a>
                </p>
                <p>
                    <a href="<?php echo htmlspecialchars($siteUrl . '/', ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true">←</span> Zurück zur Startseite</a>
                </p>
            </div>

        </div><!-- /.ptc-auth-card -->

        <?php
        // Login-Showcase (365CMS 4.1.0): dieselben Inhalte wie auf der Core-Loginseite, Stile aus css/cms-v4.css.
        if (isset($showcase) && class_exists('\\CMS\\Services\\CmsAuthPageService')) {
            \CMS\Services\CmsAuthPageService::getInstance()->renderShowcase(is_array($showcase) ? $showcase : null, 'login', 'legacy');
        }
        ?>

    </div>
</section>
