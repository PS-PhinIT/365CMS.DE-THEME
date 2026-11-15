<?php
/**
 * 404 – MedCare Pro Theme
 *
 * @package MedCarePro_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}


$safe       = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$homeUrl    = $safe(theme_route_url('home'));
$searchUrl  = $safe(theme_route_url('search'));
// „Arzt suchen“ nur, wenn die Route /aerzte existiert (Plugin oder veröffentlichte Seite).
$doctorsUrl = theme_v4_link_available(theme_route_url('doctors')) ? $safe(theme_route_url('doctors')) : '';
?>
<main id="main" class="mc-main mc-status-page">
    <div class="mc-container mc-status-page__container">
        <div class="mc-card mc-status-card">
            <p class="mc-status-code mc-status-code--info" aria-hidden="true">404</p>
            <h1 class="mc-status-title">Seite nicht gefunden</h1>
            <p class="mc-status-text">Die gesuchte Seite existiert nicht oder wurde verschoben.</p>
            <div class="mc-status-actions">
                <a href="<?php echo $homeUrl; ?>" class="mc-btn mc-btn-primary">Zur Startseite</a>
                <?php if ($doctorsUrl !== '') : ?>
                <a href="<?php echo $doctorsUrl; ?>" class="mc-btn mc-btn-outline">Arzt suchen</a>
                <?php else : ?>
                <a href="<?php echo $searchUrl; ?>" class="mc-btn mc-btn-outline">Suche öffnen</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
