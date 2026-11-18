<?php
declare(strict_types=1);
namespace CMS { class Database { public static function instance(): self { return new self; }
  public function getPrefix(): string { return 'cms_'; }
  public function get_results(string $q, array $p = []): array {
    $GLOBALS['Q'][] = [$q, $p];
    if (!empty($GLOBALS['DB_FAIL'])) { throw new \RuntimeException('db'); }
    $rows = $GLOBALS['PAGES'] ?? [];
    if (str_contains($q, 'LIMIT 501')) { return array_slice($rows, 0, 501); }
    // gezielte Abfrage: nur Zeilen, deren slug/slug_en in den Parametern steht
    $out = [];
    foreach ($rows as $r) { if (in_array($r->slug, $p, true) || in_array($r->slug_en, $p, true)) { $out[] = (object) ['slug' => $r->slug, 'slug_en' => $r->slug_en, 'has_locale' => str_contains($q, 'slug AS slug_en') ? 1 : $r->has_en]; } }
    return $out;
  } } }
namespace {
define('ABSPATH', rtrim(getenv('CMS_CORE_DIR') ?: dirname(__DIR__, 6) . '/365cms.de/CMS', '/') . '/'); define('SITE_URL', 'https://example.test/cms');
require ABSPATH . 'core/Hooks.php';
require $argv[1];
$f = 0; $ok = function (bool $c, string $m) use (&$f) { if (!$c) { $f++; echo "FAIL $m\n"; } };
$GLOBALS['PAGES'] = [
  (object) ['slug' => 'ueber-uns', 'slug_en' => 'about-us', 'has_en' => 1],
  (object) ['slug' => 'impressum', 'slug_en' => '', 'has_en' => 0],
  (object) ['slug' => 'grösse', 'slug_en' => '', 'has_en' => 0],
];
$ok(theme_v4_link_available('https://example.test/cms/ueber-uns') === true, 'de page');
$ok(theme_v4_link_available('https://example.test/cms/UEBER-UNS') === true, 'case-insensitive');
$ok(theme_v4_link_available('https://example.test/cms/gr%C3%B6sse') === true, 'percent-encoded');
$ok(theme_v4_link_available('https://example.test/cms/nicht-da') === false, 'unknown page');
$ok(theme_v4_link_available('https://example.test/cms/en/about-us') === true, 'en slug_en with english content');
$ok(theme_v4_link_available('https://example.test/cms/en/ueber-uns') === true, 'en via slug fallback');
$ok(theme_v4_link_available('https://example.test/cms/en/impressum') === false, 'en without english content');
$ok(theme_v4_link_available('https://example.test/cms/ueber-uns/../impressum') === false, 'dotdot rejected');
$ok(theme_v4_link_available('https://example.test/cms/blog') === true, 'route');
$ok(theme_v4_link_available('https://fremd.example/x') === true, 'external');
$ok(theme_v4_link_available('#') === false && theme_v4_link_available('') === false, 'empty');
$ok(theme_v4_link_available('mailto:a@b.de') === true, 'mailto');
$ok(count($GLOBALS['Q']) === 1, 'one query for everything: ' . count($GLOBALS['Q']));
$links = [['label' => 'a', 'url' => 'https://example.test/cms/ueber-uns'], ['label' => 'b', 'url' => 'https://example.test/cms/gibt-es-nicht'], ['label' => 'c', 'url' => '/blog']];
$ok(array_column(theme_v4_filter_links($links), 'label') === ['a', 'c'], 'filter_links');
echo $f === 0 ? "kit-links ok\n" : "$f failures\n";
}
