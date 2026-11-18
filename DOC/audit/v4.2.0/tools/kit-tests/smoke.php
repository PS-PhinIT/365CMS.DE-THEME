<?php
declare(strict_types=1);
define('ABSPATH', rtrim(getenv('CMS_CORE_DIR') ?: dirname(__DIR__, 6) . '/365cms.de/CMS', '/') . '/');
define('SITE_URL', 'https://example.test/cms');
require ABSPATH . 'core/Hooks.php';
require ABSPATH . 'core/Slug.php';
require $argv[1];
$f = 0;
$ok = function (bool $c, string $m) use (&$f) { if (!$c) { $f++; echo "FAIL $m\n"; } };
$ok(theme_v4_local_asset_url('/\\evil.example/x.png') === '', 'backslash');
$ok(theme_v4_local_asset_url('/uploads/a.png') === '/uploads/a.png', 'local');
$ok(theme_v4_local_asset_url('https://example.test/a.png') !== '', 'same host');
$_SERVER['REQUEST_URI'] = '/cms/blog/x';
$ok(theme_v4_is_current_path('/cms/blog') === true, 'current');
$ok(theme_v4_is_current_path('/cmsblog') === false, 'boundary');
$_SERVER['HTTP_REFERER'] = 'https://example.test\\@evil.example/';
$ok(theme_v4_safe_back_url() === '', 'referer backslash');
$_SERVER['HTTP_REFERER'] = 'https://example.test/cms/page';
$ok(theme_v4_safe_back_url() !== '', 'referer ok');
foreach (['Übersicht: Größe der Postfächer prüfen', 'Café & Crème', 'ABC def', '  --x--  '] as $t) {
    $core = \CMS\Slug::fromText($t, 100);
    $ok(theme_v4_slug($t) === $core, "slug core $t");
}
$ok(theme_v4_module_enabled('community') === false, 'module without service → default');
$ok(theme_v4_community_available() === false, 'community unavailable');
$ok(theme_v4_community_url() === '' && theme_v4_community_link() === null, 'community url');
$ok(theme_v4_user_can('community.moderate') === false, 'cap');
echo $f === 0 ? "kit ok\n" : "$f failures\n";
