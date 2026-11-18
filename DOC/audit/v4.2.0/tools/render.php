<?php
// php render.php <themedir> <template> [json-vars] [json-get]
declare(strict_types=1);
$dir = rtrim($argv[1], '/'); $tpl = $argv[2];
$vars = isset($argv[3]) && $argv[3] !== '' ? json_decode($argv[3], true) : [];
$_GET = isset($argv[4]) && $argv[4] !== '' ? json_decode($argv[4], true) : [];
$_SERVER['REQUEST_URI'] = $vars['__uri'] ?? '/'; unset($vars['__uri']);
$_SERVER['REQUEST_METHOD'] = 'GET'; $_SERVER['HTTP_HOST'] = 'example.test';
$GLOBALS['LOGGED_IN'] = !empty($vars['__logged']); unset($vars['__logged']);
$GLOBALS['CUSTOM'] = $vars['__custom'] ?? []; unset($vars['__custom']);
$GLOBALS["ROWS"] = array_map(static fn($r) => (object) $r, $vars["__rows"] ?? []); unset($vars["__rows"]);
$_SESSION = [];
// Core-Checkout (Verzeichnis `CMS` von PS-PhinIT/365CMS.DE): CMS_CORE_DIR oder ../365cms.de/CMS neben dem Theme-Repository
define('ABSPATH', rtrim(getenv('CMS_CORE_DIR') ?: dirname(__DIR__, 5) . '/365cms.de/CMS', '/') . '/');
define('SITE_URL', 'https://example.test');
define('THEME_PATH', dirname($dir) . '/');
$GLOBALS['THEME_DIR'] = $dir;
$warnings = [];
set_error_handler(function (int $no, string $msg, string $file, int $line) use (&$warnings) { $warnings[] = "$msg @ " . basename($file) . ":$line"; return true; });
require ABSPATH . 'core/Hooks.php';
require ABSPATH . 'core/Slug.php';
require __DIR__ . '/stubs.php';
\CMS\ThemeManager::instance()->slug = basename($dir);
ob_start();
try {
    require $dir . '/functions.php';
    $render = function (string $__f, array $__v) { extract($__v); require $__f; };
    $framed = ($tpl === 'error-framed');
    if ($framed) { $tpl = 'error'; }
    $bare = in_array($tpl, ['error', 'error-standalone'], true) && !$framed;
    if (!$bare) { $render($dir . '/header.php', $vars); }
    $file = $tpl === 'error-standalone' ? 'error' : $tpl;
    if ($tpl === 'error-standalone') { $vars['error_standalone'] = true; }
    $render($dir . '/' . $file . '.php', $vars);
    if (!$bare) { $render($dir . '/footer.php', $vars); }
} catch (\Throwable $e) { $warnings[] = 'EXCEPTION ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine(); }
$html = (string) ob_get_clean();
$mains = preg_match_all('/<main\b/i', $html);
$issues = $warnings;
if (($tpl !== 'error' || $framed) && $mains !== 1) { $issues[] = "main count $mains"; }
if ($tpl === 'error' && !$framed && $mains > 1) { $issues[] = "main count $mains"; }
if (preg_match_all('/<(script|style)\b(?![^>]*\bsrc=)(?![^>]*nonce=)(?![^>]*application\/(?:ld\+)?json)[^>]*>/i', $html, $m)) { $issues[] = 'inline without nonce: ' . implode(' | ', $m[0]); }
if (preg_match('/href="\s*javascript:/i', $html)) { $issues[] = 'javascript: href'; }
if (str_contains($html, 'href="/\\') || str_contains($html, "evil.example")) { $issues[] = 'backslash/evil href'; }
if (preg_match('/<script>alert|<x-xss>/i', $html)) { $issues[] = 'XSS payload reflected raw'; }
if (preg_match('/id="([^"]+)"[\s\S]*id="\1"/', '')) {}
preg_match_all('/\bid="([^"]+)"/', $html, $ids); $dups = array_unique(array_diff_assoc($ids[1], array_unique($ids[1])));
if ($dups) { $issues[] = 'duplicate ids: ' . implode(',', $dups); }
if (getenv('DUMP')) { file_put_contents(getenv('DUMP'), $html); }
if (getenv('STATS')) { echo basename($dir) . " $tpl pagemanager_calls=" . ($GLOBALS['PM_CALLS'] ?? 0) . " page_queries=" . ($GLOBALS['PAGE_QUERIES'] ?? 0) . "
"; }
echo basename($dir) . " $tpl " . ($issues ? "ISSUES:\n  - " . implode("\n  - ", $issues) : 'ok') . "\n";
