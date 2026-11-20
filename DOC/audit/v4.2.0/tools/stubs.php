<?php
declare(strict_types=1);
namespace CMS {
    class ThemeManager {
        public static ?self $i = null; public string $slug = '';
        public static function instance(): self { return self::$i ??= new self; }
        public function getThemeUrl(): string { return 'https://example.test/themes/' . $this->slug; }
        public function getThemePath(string $s = ''): string { return $GLOBALS['THEME_DIR']; }
        public function getSiteTitle(): string { return 'Test <Site> & "Co"'; }
        public function getSiteDescription(): string { return 'Beschreibung'; }
        public function getMenu(string $l): array { return [
            ['title' => 'Start', 'url' => '/'], ['title' => 'Blog', 'url' => '/blog'],
            ['title' => 'XSS', 'url' => "javascript://x%0aalert(1)"], ['title' => 'BS', 'url' => '/\\evil.example'],
            ['title' => 'Ext', 'url' => 'https://ext.example/', 'target' => '_blank'], ['title' => 'Mail', 'url' => 'mailto:a@b.de'],
        ]; }
        public function getActiveThemeSlug(): string { return $this->slug; }
    }
    class Security { public static function instance(): self { return new self; }
        public function nonceAttr(): string { return 'nonce="N0NCE"'; }
        public function generateToken(string $a = ''): string { return 'TOKEN'; }
        public function verifyToken(string $t, string $a = ''): bool { return $t === 'TOKEN'; } }
    class Auth { public static function instance(): self { return new self; }
        public static function isLoggedIn(): bool { return !empty($GLOBALS['LOGGED_IN']); }
        public static function isAdmin(): bool { return false; }
        public function hasCapability(string $c): bool { return false; }
        public function getCurrentUser(): ?object { return !empty($GLOBALS['LOGGED_IN']) ? (object) ['id' => 2, 'username' => 'mod', 'display_name' => 'Mo <d>', 'role' => 'moderator', 'email' => 'm@x.de'] : null; } }
    class PageManager { public static function instance(): self { return new self; }
        public function getPageBySlug(string $s, string $l = 'de'): ?array { $GLOBALS['PM_CALLS'] = ($GLOBALS['PM_CALLS'] ?? 0) + 1; return in_array($s, ['ueber-uns', 'about', 'datenschutz', 'impressum'], true) ? ['status' => 'published'] : null; } }
    class Database { public static function instance(): self { return new self; }
        public function getPrefix(): string { return 'cms_'; } public function prefix(): string { return 'cms_'; }
        public function get_results(string $q, array $p = []): array {
            $GLOBALS["QUERIES"][] = $q;
            if (str_contains($q, 'cms_pages')) {
                $GLOBALS['PAGE_QUERIES'] = ($GLOBALS['PAGE_QUERIES'] ?? 0) + 1;
                $rows = [];
                foreach (['ueber-uns', 'about', 'datenschutz', 'impressum'] as $slug) {
                    if (in_array($slug, $p, true)) { $rows[] = (object) ['slug' => $slug, 'slug_en' => $slug, 'has_locale' => 1]; }
                }
                return $rows;
            }
            return $GLOBALS["ROWS"] ?? [];
        }
        public function get_row(string $q, array $p = []): ?object { $GLOBALS['QUERIES'][] = $q; return null; }
        public function get_var(string $q, array $p = []): mixed { $GLOBALS['QUERIES'][] = $q; return 0; }
        public function execute(string $q, array $p = []): object { $GLOBALS['QUERIES'][] = $q; return new class { public function fetchAll(int $m = 0): array { return []; } public function fetch(int $m = 0): mixed { return false; } public function fetchColumn(): mixed { return 0; } }; }
        public function query(string $q): mixed { $GLOBALS['QUERIES'][] = $q; return false; } }
}
namespace CMS\Services {
    class ThemeCustomizer { public static function instance(): self { return new self; }
        // Wie ThemeCustomizer::get() des Cores: gespeicherter Wert, sonst Standard aus theme.json, sonst Standard des Aufrufers
        public function get(string $s, string $k, mixed $d = null): mixed {
            if (isset($GLOBALS['CUSTOM'][$s][$k])) { return $GLOBALS['CUSTOM'][$s][$k]; }
            static $defaults = null;
            if ($defaults === null) {
                $file = ($GLOBALS['THEME_DIR'] ?? '') . '/theme.json';
                $json = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
                $defaults = is_array($json['customization'] ?? null) ? $json['customization'] : [];
            }
            return isset($defaults[$s]['settings'][$k]['default']) ? $defaults[$s]['settings'][$k]['default'] : $d;
        }
        public function getAll(): array { return []; } }
    class OptionStore { public static function getInstance(): self { return new self; }
        public function get(string $n, mixed $d = null): mixed { return $d; }
        public function getMany(array $n, mixed $d = null): array { return array_fill_keys($n, $d); } }
    class SEOService { public static function getInstance(): self { return new self; }
        public function renderCurrentHeadTags(): string { return '<meta name="robots" content="index, follow">' . "\n"; }
        public function getCurrentSeoPayload(): array { return ['title' => '']; } }
}
namespace {
    function cms_csp_runtime_tags(bool $d = false): string { return '<script src="/csp.js" nonce="N0NCE"></script>'; }
    function cms_post_publication_where(string $a = ''): string { return "{$a}.status = 'published'"; }
    function get_option(string $n, mixed $d = ''): mixed { return $d; }
}
