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

$f = 0; $ok = function (bool $c, string $m) use (&$f) { if (!$c) { $f++; echo "FAIL $m
"; } };
if ($argv[2] === 'big') {
  $GLOBALS['PAGES'] = [];
  for ($i = 0; $i < 600; $i++) { $GLOBALS['PAGES'][] = (object) ['slug' => "seite-$i", 'slug_en' => '', 'has_en' => 0]; }
  $ok(theme_v4_link_available('/seite-599') === true, 'big: targeted hit');
  $ok(theme_v4_link_available('/seite-9999') === false, 'big: targeted miss');
  $links = [['label' => 'a', 'url' => '/seite-1'], ['label' => 'b', 'url' => '/seite-2'], ['label' => 'c', 'url' => '/nix']];
  $before = count($GLOBALS['Q']);
  $ok(array_column(theme_v4_filter_links($links), 'label') === ['a', 'b'], 'big: filter');
  $ok(count($GLOBALS['Q']) - $before === 1, 'big: filter_links batches into one query, got ' . (count($GLOBALS['Q']) - $before));
} else {
  $GLOBALS['DB_FAIL'] = true;
  $ok(theme_v4_link_available('/irgendwas') === false, 'db fail: false, no exception');
  $ok(theme_v4_link_available('/blog') === true, 'db fail: routes still work');
}
echo $f === 0 ? "ok $argv[2]
" : "$f failures
";
}
