<?php
/*
 *  Wren CMS — a whole website in one small file.
 *  Version 0.1.0 "Troglodytes"
 *
 *  Requirements: PHP 8.1+ with PDO SQLite (standard on almost every host).
 *  Install: upload this file. Visit it in a browser. That's it.
 *
 *  In the spirit of sNews (Luka Cvrk / Solucija, 2004–2016), rebuilt from
 *  scratch for the modern web: SQLite by default, hashed passwords, CSRF
 *  protection, prepared statements throughout, markdown editing.
 *
 *  Licence: MIT
 */

declare(strict_types=1);

const WREN_VERSION = '0.4.1';
define('WREN_DIR', __DIR__);
define('WREN_DB', WREN_DIR . '/wren.db');

/* ---------------------------------------------------------------- sessions */

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

/* ---------------------------------------------------------------- database */

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $fresh = !file_exists(WREN_DB);
        $pdo = new PDO('sqlite:' . WREN_DB, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');
        if ($fresh) {
            wren_schema($pdo);
        } else {
            wren_migrate($pdo);
        }
    }
    return $pdo;
}

function wren_migrate(PDO $pdo): void
{
    // Tiny forward-only migrations so upgrades are just "replace index.php".
    $cols = array_column($pdo->query('PRAGMA table_info(posts)')->fetchAll(), 'name');
    if ($cols && !in_array('show_title', $cols, true)) {
        $pdo->exec('ALTER TABLE posts ADD COLUMN show_title INTEGER NOT NULL DEFAULT 1');
    }
    if ($cols && !in_array('description', $cols, true)) {
        $pdo->exec('ALTER TABLE posts ADD COLUMN description TEXT NOT NULL DEFAULT ""');
    }
}

function wren_schema(PDO $pdo): void
{
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS settings (
            name  TEXT PRIMARY KEY,
            value TEXT NOT NULL DEFAULT ""
        );
        CREATE TABLE IF NOT EXISTS users (
            id       INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created  TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS posts (
            id        INTEGER PRIMARY KEY AUTOINCREMENT,
            type      TEXT NOT NULL DEFAULT "article",  -- article | page
            slug      TEXT NOT NULL UNIQUE,
            title     TEXT NOT NULL,
            body      TEXT NOT NULL DEFAULT "",
            published INTEGER NOT NULL DEFAULT 1,
            show_title INTEGER NOT NULL DEFAULT 1,
            description TEXT NOT NULL DEFAULT "",
            position  INTEGER NOT NULL DEFAULT 0,
            created   TEXT NOT NULL,
            updated   TEXT NOT NULL
        );
        CREATE INDEX IF NOT EXISTS idx_posts_type ON posts (type, published, created);
    ');
}

/* ---------------------------------------------------------------- settings */

function setting(string $name, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT name, value FROM settings') as $row) {
            $cache[$row['name']] = $row['value'];
        }
    }
    return $cache[$name] ?? $default;
}

function set_setting(string $name, string $value): void
{
    $st = db()->prepare('INSERT INTO settings (name, value) VALUES (?, ?)
                         ON CONFLICT(name) DO UPDATE SET value = excluded.value');
    $st->execute([$name, $value]);
}

/* ----------------------------------------------------------------- helpers */

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function now(): string
{
    return gmdate('Y-m-d H:i:s');
}

function slugify(string $s): string
{
    $s = strtolower(trim($s));
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false) {
            $s = $t;
        }
    }
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    return $s !== '' ? $s : 'untitled';
}

function base_path(): string
{
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $dir;
}

function url(string $route = ''): string
{
    $base = base_path();
    if ($route === '') {
        return ($base ?: '') . '/';
    }
    if (setting('pretty_urls') === '1') {
        return $base . '/' . $route;
    }
    return ($base ?: '') . '/?q=' . rawurlencode($route);
}

function abs_url(string $route = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . url($route);
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function flash(?string $msg = null): ?string
{
    if ($msg !== null) {
        $_SESSION['wren_flash'] = $msg;
        return null;
    }
    $m = $_SESSION['wren_flash'] ?? null;
    unset($_SESSION['wren_flash']);
    return $m;
}

/* ---------------------------------------------------------------- security */

function csrf_token(): string
{
    if (empty($_SESSION['wren_csrf'])) {
        $_SESSION['wren_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['wren_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Wren: this form has expired. Go back, reload the page, and try again.');
    }
}

function is_admin(): bool
{
    return !empty($_SESSION['wren_user']);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect(url('admin/login'));
    }
}

/* ---------------------------------------------------- markdown (tiny, tidy) */
/* Headings, paragraphs, bold, italic, code spans and fenced blocks, links,
   images, quotes, lists, rules. Raw HTML passes through untouched: authors
   are trusted admins, exactly as in sNews.                                  */

function markdown(string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    // Protect fenced code blocks first.
    $stash = [];
    $text = preg_replace_callback('/^```[\w-]*\n(.*?)\n?```[ \t]*$/ms', function ($m) use (&$stash) {
        $stash[] = '<pre><code>' . e($m[1]) . '</code></pre>';
        return "\x02" . (count($stash) - 1) . "\x03";
    }, $text);

    $blocks = preg_split('/\n{2,}/', trim($text)) ?: [];
    $html = [];

    foreach ($blocks as $block) {
        $block = trim($block, "\n");
        if ($block === '') {
            continue;
        }
        // Stashed code block
        if (preg_match('/^\x02(\d+)\x03$/', $block, $m)) {
            $html[] = $stash[(int)$m[1]];
            continue;
        }
        // Horizontal rule
        if (preg_match('/^(\*{3,}|-{3,}|_{3,})$/', $block)) {
            $html[] = '<hr>';
            continue;
        }
        // Heading
        if (preg_match('/^(#{1,6})\s+(.+)$/', $block, $m)) {
            $level = strlen($m[1]);
            $html[] = "<h$level>" . md_inline($m[2]) . "</h$level>";
            continue;
        }
        // Blockquote
        if (preg_match('/^>\s?/m', $block) && preg_match('/^(\s*>)/', $block)) {
            $inner = preg_replace('/^>\s?/m', '', $block);
            $html[] = '<blockquote>' . markdown($inner) . '</blockquote>';
            continue;
        }
        // Lists
        $lines = explode("\n", $block);
        $isUl = !array_filter($lines, fn($l) => !preg_match('/^\s*[-*+]\s+/', $l));
        $isOl = !array_filter($lines, fn($l) => !preg_match('/^\s*\d+[.)]\s+/', $l));
        if ($isUl || $isOl) {
            $tag = $isUl ? 'ul' : 'ol';
            $items = array_map(
                fn($l) => '<li>' . md_inline(preg_replace('/^\s*(?:[-*+]|\d+[.)])\s+/', '', $l)) . '</li>',
                $lines
            );
            $html[] = "<$tag>\n" . implode("\n", $items) . "\n</$tag>";
            continue;
        }
        // Raw HTML block: starts with a tag, leave alone.
        if (preg_match('/^<(\/?)[a-zA-Z][^>]*>/', $block)) {
            $html[] = $block;
            continue;
        }
        // Paragraph
        $html[] = '<p>' . md_inline(str_replace("\n", "<br>\n", $block)) . '</p>';
    }

    return implode("\n\n", $html);
}

function md_inline(string $s): string
{
    // Code spans first, protected from further formatting.
    $stash = [];
    $s = preg_replace_callback('/`([^`\n]+)`/', function ($m) use (&$stash) {
        $stash[] = '<code>' . e($m[1]) . '</code>';
        return "\x02" . (count($stash) - 1) . "\x03";
    }, $s);

    $s = preg_replace('/!\[([^\]]*)\]\(([^)\s]+)\)/', '<img src="$2" alt="$1">', $s);
    $s = preg_replace('/\[([^\]]+)\]\(([^)\s]+)\)/', '<a href="$2">$1</a>', $s);
    $s = preg_replace('/\*\*([^*\n]+)\*\*/', '<strong>$1</strong>', $s);
    $s = preg_replace('/(?<![\w*])\*([^*\n]+)\*(?![\w*])/', '<em>$1</em>', $s);

    return preg_replace_callback('/\x02(\d+)\x03/', fn($m) => $stash[(int)$m[1]], $s);
}

/* ------------------------------------------------------------------ posts */

function post_by_slug(string $slug, bool $publishedOnly = true): ?array
{
    $sql = 'SELECT * FROM posts WHERE slug = ?' . ($publishedOnly ? ' AND published = 1' : '');
    $st = db()->prepare($sql);
    $st->execute([$slug]);
    $row = $st->fetch();
    return $row ?: null;
}

function excerpt(string $body, int $limit = 320): string
{
    $html = markdown($body);
    // strip_tags keeps the *contents* of style/script blocks; remove them first
    $html = preg_replace('/<(style|script)\b[^>]*>.*?<\/\1>/is', '', $html);
    $plain = trim(strip_tags($html));
    $plain = preg_replace('/\s+/', ' ', $plain);
    // UTF-8 safe without requiring the mbstring extension.
    if (preg_match('/^.{0,' . $limit . '}$/us', $plain)) {
        return $plain;
    }
    preg_match('/^.{0,' . $limit . '}(?=\s|$)/us', $plain, $m);
    $cut = $m[0] ?? substr($plain, 0, $limit);
    return rtrim($cut) . '…';
}

function unique_slug(string $slug, int $ignoreId = 0): string
{
    $base = $slug;
    $n = 2;
    while (true) {
        $st = db()->prepare('SELECT id FROM posts WHERE slug = ? AND id != ?');
        $st->execute([$slug, $ignoreId]);
        if (!$st->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $n++;
    }
}

/* ------------------------------------------------------------- theme layer */
/* If a file named theme.html sits beside this one, Wren uses it.
   Otherwise the built-in default below is used. Tags a theme may use:
   {{site_title}} {{tagline}} {{page_title}} {{menu}} {{content}}
   {{home_url}} {{rss_url}} {{year}} {{generator}}                          */

function menu_html(string $activeSlug = ''): string
{
    $homePage = setting('home_page');
    $items = [];
    $cls = ($activeSlug === '' ? ' class="active"' : '');
    $items[] = '<a href="' . e(url()) . '"' . $cls . '>Home</a>';
    if ($homePage !== '') {
        $blogSlug = setting('blog_slug', 'blog');
        $cls = ($activeSlug === $blogSlug ? ' class="active"' : '');
        $items[] = '<a href="' . e(url($blogSlug)) . '"' . $cls . '>'
                 . e(setting('blog_title', 'Blog')) . '</a>';
    }
    $st = db()->query('SELECT slug, title FROM posts WHERE type = "page" AND published = 1
                       ORDER BY position, title');
    foreach ($st as $p) {
        if ($homePage !== '' && $p['slug'] === $homePage) {
            continue; // shown as Home already
        }
        $cls = ($activeSlug === $p['slug'] ? ' class="active"' : '');
        $items[] = '<a href="' . e(url($p['slug'])) . '"' . $cls . '>' . e($p['title']) . '</a>';
    }
    return implode("\n", $items);
}

function render(string $pageTitle, string $content, string $activeSlug = '', array $seo = []): never
{
    $themeFile = WREN_DIR . '/theme.html';
    $theme = is_file($themeFile) ? (string)file_get_contents($themeFile) : default_theme();

    $siteTitle = setting('site_title', 'Wren');

    // ---- SEO head block: description, canonical, Open Graph / Twitter card
    $desc = trim($seo['description'] ?? '') ?: setting('meta_description', setting('tagline'));
    $fullTitle = $pageTitle !== '' ? $pageTitle . ' · ' . $siteTitle : $siteTitle;
    $canonical = abs_url($seo['route'] ?? $activeSlug);
    if (($seo['pg'] ?? 1) > 1) {
        $canonical .= (str_contains($canonical, '?') ? '&' : '?') . 'pg=' . (int)$seo['pg'];
    }
    $head  = '<meta name="description" content="' . e($desc) . '">' . "\n";
    $head .= '<link rel="canonical" href="' . e($canonical) . '">' . "\n";
    $head .= '<meta property="og:site_name" content="' . e($siteTitle) . '">' . "\n";
    $head .= '<meta property="og:type" content="' . e($seo['type'] ?? 'website') . '">' . "\n";
    $head .= '<meta property="og:title" content="' . e($fullTitle) . '">' . "\n";
    $head .= '<meta property="og:description" content="' . e($desc) . '">' . "\n";
    $head .= '<meta property="og:url" content="' . e($canonical) . '">' . "\n";
    if (($img = setting('og_image')) !== '') {
        $head .= '<meta property="og:image" content="' . e($img) . '">' . "\n";
    }
    $head .= '<meta name="twitter:card" content="summary">';

    $vars = [
        '{{seo_head}}'   => $head,
        '{{site_title}}' => e($siteTitle),
        '{{tagline}}'    => e(setting('tagline')),
        '{{page_title}}' => e($pageTitle !== '' ? $pageTitle . ' · ' . $siteTitle : $siteTitle),
        '{{menu}}'       => menu_html($activeSlug),
        '{{content}}'    => $content,
        '{{home_url}}'   => e(url()),
        '{{rss_url}}'    => e(url('rss')),
        '{{year}}'       => gmdate('Y'),
        '{{generator}}'  => 'Wren ' . WREN_VERSION,
    ];
    $html = strtr($theme, $vars);
    if (!str_contains($theme, '{{seo_head}}')) {
        $html = preg_replace('/<\/head>/i', $head . "\n</head>", $html, 1);
    }
    echo $html;
    exit;
}

function default_theme(): string
{
    return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{page_title}}</title>
<link rel="alternate" type="application/rss+xml" title="{{site_title}}" href="{{rss_url}}">
<meta name="generator" content="{{generator}}">
<style>
:root {
  --paper:  #FBFAF6;
  --ink:    #262219;
  --quiet:  #7C7669;
  --moss:   #55613B;
  --moss-d: #414B2C;
  --straw:  #EDE9DD;
  --line:   #E2DDCF;
}
* { box-sizing: border-box; }
html { font-size: 17px; }
body {
  margin: 0;
  background: var(--paper);
  color: var(--ink);
  font-family: Georgia, 'Times New Roman', serif;
  line-height: 1.65;
}
.wrap { max-width: 44rem; margin: 0 auto; padding: 0 1.25rem; }
header.site { padding: 3.5rem 0 0; }
.masthead {
  font-size: 2.6rem; font-weight: 400; letter-spacing: -0.02em;
  margin: 0; line-height: 1.1;
}
.masthead a { color: var(--ink); text-decoration: none; }
.masthead a::after { content: "."; color: var(--moss); }
.tagline { color: var(--quiet); font-style: italic; margin: 0.35rem 0 0; }
nav.menu {
  margin: 1.75rem 0 0; padding: 0.65rem 0;
  border-top: 1px solid var(--line); border-bottom: 1px solid var(--line);
  font-family: system-ui, -apple-system, sans-serif;
  font-size: 0.82rem; letter-spacing: 0.06em; text-transform: uppercase;
}
nav.menu a { color: var(--quiet); text-decoration: none; margin-right: 1.4rem; }
nav.menu a:hover, nav.menu a.active { color: var(--moss-d); }
main { padding: 2.5rem 0 3rem; }
article + article { margin-top: 3rem; padding-top: 3rem; border-top: 1px solid var(--line); }
h1.post-title { font-size: 1.75rem; font-weight: 400; margin: 0 0 0.3rem; line-height: 1.25; }
h1.post-title a { color: var(--ink); text-decoration: none; }
h1.post-title a:hover { color: var(--moss-d); }
.post-meta {
  font-family: system-ui, -apple-system, sans-serif;
  font-size: 0.78rem; color: var(--quiet);
  letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 1.1rem;
}
.post-body a { color: var(--moss-d); text-decoration-color: var(--moss); text-underline-offset: 2px; }
.post-body img { max-width: 100%; height: auto; }
.post-body blockquote {
  margin: 1.5rem 0; padding: 0.1rem 1.25rem;
  border-left: 3px solid var(--moss); background: var(--straw);
  font-style: italic;
}
.post-body pre {
  background: var(--ink); color: var(--straw);
  padding: 1rem 1.25rem; overflow-x: auto; border-radius: 4px;
  font-size: 0.85rem; line-height: 1.5;
}
.post-body code { font-family: ui-monospace, 'Cascadia Code', Menlo, monospace; }
.post-body p code, .post-body li code {
  background: var(--straw); padding: 0.1em 0.35em; border-radius: 3px; font-size: 0.88em;
}
.post-body h2, .post-body h3 { font-weight: 400; margin-top: 2rem; }
.post-body hr { border: 0; border-top: 1px solid var(--line); margin: 2.25rem 0; }
a.more {
  font-family: system-ui, -apple-system, sans-serif; font-size: 0.82rem;
  letter-spacing: 0.05em; text-transform: uppercase;
  color: var(--moss-d); text-decoration: none;
}
a.more:hover { text-decoration: underline; }
.pagination {
  display: flex; justify-content: space-between; margin-top: 3rem;
  font-family: system-ui, -apple-system, sans-serif; font-size: 0.85rem;
}
.pagination a { color: var(--moss-d); text-decoration: none; }
footer.site {
  border-top: 1px solid var(--line); padding: 1.4rem 0 3rem;
  font-family: system-ui, -apple-system, sans-serif;
  font-size: 0.78rem; color: var(--quiet);
}
footer.site a { color: var(--quiet); }
@media (max-width: 480px) { html { font-size: 16px; } .masthead { font-size: 2.1rem; } }
</style>
</head>
<body>
<div class="wrap">
  <header class="site">
    <h1 class="masthead"><a href="{{home_url}}">{{site_title}}</a></h1>
    <p class="tagline">{{tagline}}</p>
    <nav class="menu">{{menu}}</nav>
  </header>
  <main>
{{content}}
  </main>
  <footer class="site">
    &copy; {{year}} {{site_title}} &middot; <a href="{{rss_url}}">RSS</a> &middot; Powered by {{generator}}
  </footer>
</div>
</body>
</html>
HTML;
}

/* ----------------------------------------------------------- public views */

function view_home(string $routeBase = ''): never
{
    $perPage = max(1, (int)setting('posts_per_page', '5'));
    $pg = max(1, (int)($_GET['pg'] ?? 1));
    $offset = ($pg - 1) * $perPage;

    $total = (int)db()->query('SELECT COUNT(*) c FROM posts WHERE type = "article" AND published = 1')
                      ->fetch()['c'];
    $st = db()->prepare('SELECT * FROM posts WHERE type = "article" AND published = 1
                         ORDER BY created DESC LIMIT ? OFFSET ?');
    $st->bindValue(1, $perPage, PDO::PARAM_INT);
    $st->bindValue(2, $offset, PDO::PARAM_INT);
    $st->execute();
    $posts = $st->fetchAll();

    $listTitle = $routeBase === '' ? '' : setting('blog_title', 'Blog');

    if (!$posts && $pg === 1) {
        $content = '<article><h1 class="post-title">Nothing here yet</h1>'
                 . '<div class="post-body"><p>This site runs on Wren. '
                 . '<a href="' . e(url('admin')) . '">Sign in</a> to write your first article.</p></div></article>';
        render($listTitle, $content, $routeBase, ['route' => $routeBase]);
    }

    $out = [];
    foreach ($posts as $p) {
        $link = e(url($p['slug']));
        $out[] = '<article>'
               . '<h1 class="post-title"><a href="' . $link . '">' . e($p['title']) . '</a></h1>'
               . '<p class="post-meta">' . e(gmdate('j F Y', strtotime($p['created']))) . '</p>'
               . '<div class="post-body"><p>' . e(excerpt($p['body'])) . '</p></div>'
               . '<a class="more" href="' . $link . '">Read on &rarr;</a>'
               . '</article>';
    }

    $nav = '';
    $pages = (int)ceil($total / $perPage);
    if ($pages > 1) {
        $mk = function (int $n, string $label) use ($routeBase) {
            $base = $routeBase === '' ? url() : url($routeBase);
            $u = $n === 1 ? $base : $base . (str_contains($base, '?') ? '&' : '?') . 'pg=' . $n;
            return '<a href="' . e($u) . '">' . $label . '</a>';
        };
        $nav = '<div class="pagination">'
             . '<span>' . ($pg > 1 ? $mk($pg - 1, '&larr; Newer') : '') . '</span>'
             . '<span>' . ($pg < $pages ? $mk($pg + 1, 'Older &rarr;') : '') . '</span>'
             . '</div>';
    }

    render($listTitle, implode("\n", $out) . $nav, $routeBase, ['route' => $routeBase, 'pg' => $pg]);
}

function view_post(array $post, bool $asHome = false): never
{
    $meta = $post['type'] === 'article'
        ? '<p class="post-meta">' . e(gmdate('j F Y', strtotime($post['created']))) . '</p>'
        : '';
    $h1 = ($post['show_title'] ?? 1)
        ? '<h1 class="post-title">' . e($post['title']) . '</h1>'
        : '';
    $content = '<article>'
             . $h1
             . $meta
             . '<div class="post-body">' . markdown($post['body']) . '</div>'
             . '</article>';
    render($asHome ? '' : $post['title'], $content, $asHome ? '' : $post['slug'], [
        'description' => trim($post['description'] ?? '') ?: excerpt($post['body'], 158),
        'route'       => $asHome ? '' : $post['slug'],
        'type'        => $post['type'] === 'article' ? 'article' : 'website',
    ]);
}

function view_404(): never
{
    http_response_code(404);
    $content = '<article><h1 class="post-title">Not found</h1>'
             . '<div class="post-body"><p>No page lives at this address. '
             . 'Try the <a href="' . e(url()) . '">home page</a>.</p></div></article>';
    render('Not found', $content);
}

function view_rss(): never
{
    $st = db()->query('SELECT * FROM posts WHERE type = "article" AND published = 1
                       ORDER BY created DESC LIMIT 10');

    header('Content-Type: application/rss+xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<rss version="2.0"><channel>';
    echo '<title>' . e(setting('site_title', 'Wren')) . '</title>';
    echo '<link>' . e(abs_url()) . '</link>';
    echo '<description>' . e(setting('tagline')) . '</description>';
    echo '<generator>Wren ' . WREN_VERSION . '</generator>';
    foreach ($st as $p) {
        echo '<item>';
        echo '<title>' . e($p['title']) . '</title>';
        echo '<link>' . e(abs_url($p['slug'])) . '</link>';
        echo '<guid>' . e(abs_url($p['slug'])) . '</guid>';
        echo '<pubDate>' . gmdate(DATE_RSS, strtotime($p['created'])) . '</pubDate>';
        echo '<description>' . e(excerpt($p['body'], 500)) . '</description>';
        echo '</item>';
    }
    echo '</channel></rss>';
    exit;
}

function view_sitemap(): never
{
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $homePage = setting('home_page');
    echo '<url><loc>' . e(abs_url()) . '</loc></url>' . "\n";
    if ($homePage !== '') {
        echo '<url><loc>' . e(abs_url(setting('blog_slug', 'blog'))) . '</loc></url>' . "\n";
    }
    $st = db()->query('SELECT slug, updated FROM posts WHERE published = 1 ORDER BY type, created');
    foreach ($st as $p) {
        if ($p['slug'] === $homePage) {
            continue; // it lives at /
        }
        echo '<url><loc>' . e(abs_url($p['slug'])) . '</loc><lastmod>'
           . gmdate('Y-m-d', strtotime($p['updated'])) . '</lastmod></url>' . "\n";
    }
    echo '</urlset>';
    exit;
}

/* ------------------------------------------------------------- first run  */

function needs_setup(): bool
{
    return (int)db()->query('SELECT COUNT(*) c FROM users')->fetch()['c'] === 0;
}

function view_setup(): never
{
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $site = trim((string)($_POST['site_title'] ?? ''));
        $user = trim((string)($_POST['username'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');
        if ($site === '' || $user === '' || strlen($pass) < 8) {
            $err = 'Every field is required, and the password needs at least 8 characters.';
        } else {
            $st = db()->prepare('INSERT INTO users (username, password, created) VALUES (?, ?, ?)');
            $st->execute([$user, password_hash($pass, PASSWORD_DEFAULT), now()]);
            set_setting('site_title', $site);
            set_setting('tagline', trim((string)($_POST['tagline'] ?? '')));
            set_setting('posts_per_page', '5');
            set_setting('pretty_urls', '0');
            $st = db()->prepare('INSERT INTO posts (type, slug, title, body, published, created, updated)
                                 VALUES ("article", "hello-world", "Hello, world", ?, 1, ?, ?)');
            $st->execute(["Welcome to **" . $site . "** — a brand-new site running on Wren.\n\nThis is your first article. Sign in to the [admin area](" . url('admin') . ") to edit or delete it, write something of your own, and add pages to the menu.", now(), now()]);
            $_SESSION['wren_user'] = $user;
            session_regenerate_id(true);
            flash('Welcome to Wren. Your site is live.');
            redirect(url('admin'));
        }
    }
    admin_shell('Set up your site', '
      <h1>A new nest</h1>
      <p class="lede">Wren needs a minute of your time, then stays out of your way.</p>
      ' . ($err ? '<p class="err">' . e($err) . '</p>' : '') . '
      <form method="post">
        ' . csrf_field() . '
        <label>Site title <input name="site_title" required value="' . e((string)($_POST['site_title'] ?? '')) . '"></label>
        <label>Tagline <span class="hint">(optional)</span> <input name="tagline" value="' . e((string)($_POST['tagline'] ?? '')) . '"></label>
        <label>Admin username <input name="username" autocomplete="username" required value="' . e((string)($_POST['username'] ?? '')) . '"></label>
        <label>Admin password <span class="hint">(8+ characters)</span> <input type="password" autocomplete="new-password" name="password" required></label>
        <button>Create site</button>
      </form>', false);
}

/* ------------------------------------------------------------ admin views */

function admin_shell(string $title, string $body, bool $nav = true): never
{
    $menu = '';
    if ($nav) {
        $menu = '<nav>
            <a href="' . e(url('admin')) . '">Articles</a>
            <a href="' . e(url('admin/pages')) . '">Pages</a>
            <a href="' . e(url('admin/settings')) . '">Settings</a>
            <span class="grow"></span>
            <a href="' . e(url()) . '">View site</a>
            <a href="' . e(url('admin/logout')) . '">Sign out</a>
        </nav>';
    }
    $f = flash();
    $flashHtml = $f ? '<p class="flash">' . e($f) . '</p>' : '';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>' . e($title) . ' · Wren</title>
<style>
:root { --paper:#FBFAF6; --ink:#262219; --quiet:#7C7669; --moss:#55613B; --moss-d:#414B2C;
        --straw:#EDE9DD; --line:#E2DDCF; --red:#8C3B2E; }
* { box-sizing:border-box; }
body { margin:0; background:var(--paper); color:var(--ink);
       font-family: system-ui, -apple-system, "Segoe UI", sans-serif; font-size:15px; line-height:1.55; }
.shell { max-width: 46rem; margin: 0 auto; padding: 1.5rem 1.25rem 4rem; }
.brand { font-family: Georgia, serif; font-size: 1.3rem; margin: 0 0 1rem; }
.brand::after { content:"."; color: var(--moss); }
nav { display:flex; gap:1.1rem; align-items:center; border-top:1px solid var(--line);
      border-bottom:1px solid var(--line); padding:0.6rem 0; margin-bottom:1.75rem;
      font-size:0.82rem; letter-spacing:0.05em; text-transform:uppercase; }
nav a { color:var(--quiet); text-decoration:none; }
nav a:hover { color:var(--moss-d); }
nav .grow { flex:1; }
h1 { font-family: Georgia, serif; font-weight:400; font-size:1.6rem; margin:0 0 0.4rem; }
.lede { color: var(--quiet); margin-top:0; }
table { width:100%; border-collapse:collapse; margin-top:1rem; }
th { text-align:left; font-size:0.75rem; letter-spacing:0.06em; text-transform:uppercase;
     color:var(--quiet); font-weight:600; padding:0.4rem 0.5rem; border-bottom:1px solid var(--line); }
td { padding:0.55rem 0.5rem; border-bottom:1px solid var(--line); vertical-align:middle; }
td.actions { text-align:right; white-space:nowrap; }
a { color: var(--moss-d); }
.draft { color:var(--quiet); font-size:0.78rem; border:1px solid var(--line);
         padding:0.05rem 0.4rem; border-radius:3px; margin-left:0.4rem; }
form label { display:block; margin:0.9rem 0 0; font-weight:600; font-size:0.85rem; }
form .hint { font-weight:400; color:var(--quiet); }
input[type=text], input[type=password], input[type=number], input:not([type]), textarea, select {
  width:100%; margin-top:0.25rem; padding:0.55rem 0.65rem; font:inherit;
  border:1px solid var(--line); border-radius:4px; background:#fff; color:var(--ink); }
textarea { min-height: 22rem; font-family: ui-monospace, Menlo, monospace; font-size:0.88rem; line-height:1.6; }
input:focus, textarea:focus, select:focus { outline:2px solid var(--moss); outline-offset:1px; border-color:var(--moss); }
button { margin-top:1.2rem; background:var(--moss); color:#fff; border:0; font:inherit; font-weight:600;
         padding:0.6rem 1.3rem; border-radius:4px; cursor:pointer; }
button:hover { background:var(--moss-d); }
button.danger { background:transparent; color:var(--red); padding:0.2rem 0.4rem; font-weight:400; font-size:0.85rem; }
.row { display:flex; gap:1rem; } .row > * { flex:1; }
.check { display:flex; gap:0.5rem; align-items:center; font-weight:400; margin-top:0.9rem; }
.check input { width:auto; margin:0; }
.flash { background:var(--straw); border-left:3px solid var(--moss); padding:0.6rem 0.9rem; }
.err { background:#F6E7E4; border-left:3px solid var(--red); padding:0.6rem 0.9rem; }
.topline { display:flex; align-items:baseline; justify-content:space-between; }
.btn-link { display:inline-block; background:var(--moss); color:#fff; text-decoration:none;
            padding:0.45rem 1rem; border-radius:4px; font-size:0.85rem; font-weight:600; }
.btn-link:hover { background:var(--moss-d); }
.muted { color:var(--quiet); font-size:0.82rem; }
</style></head><body><div class="shell">
<p class="brand">Wren</p>' . $menu . $flashHtml . $body . '</div></body></html>';
    exit;
}

function view_login(): never
{
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $fails = (int)($_SESSION['wren_fails'] ?? 0);
        if ($fails > 2) {
            sleep(min($fails, 8)); // slow brute force to a crawl
        }
        $st = db()->prepare('SELECT * FROM users WHERE username = ?');
        $st->execute([trim((string)($_POST['username'] ?? ''))]);
        $u = $st->fetch();
        if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password'])) {
            unset($_SESSION['wren_fails']);
            $_SESSION['wren_user'] = $u['username'];
            session_regenerate_id(true);
            if (password_needs_rehash($u['password'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE users SET password = ? WHERE id = ?')
                    ->execute([password_hash((string)$_POST['password'], PASSWORD_DEFAULT), $u['id']]);
            }
            redirect(url('admin'));
        }
        $_SESSION['wren_fails'] = $fails + 1;
        $err = 'That username and password don\'t match.';
    }
    admin_shell('Sign in', '
      <h1>Sign in</h1>
      ' . ($err ? '<p class="err">' . e($err) . '</p>' : '') . '
      <form method="post">
        ' . csrf_field() . '
        <label>Username <input name="username" autocomplete="username" required></label>
        <label>Password <input type="password" name="password" autocomplete="current-password" required></label>
        <button>Sign in</button>
      </form>', false);
}

function view_admin_list(string $type): never
{
    $st = db()->prepare('SELECT * FROM posts WHERE type = ? ORDER BY
                         CASE WHEN type = "page" THEN position ELSE 0 END, created DESC');
    $st->execute([$type]);
    $rows = '';
    foreach ($st as $p) {
        $badge = $p['published'] ? '' : '<span class="draft">draft</span>';
        $rows .= '<tr>
            <td><a href="' . e(url('admin/edit/' . $p['id'])) . '">' . e($p['title']) . '</a>' . $badge . '</td>
            <td class="muted">' . e(gmdate('j M Y', strtotime($p['created']))) . '</td>
            <td class="actions">
              <a href="' . e(url($p['slug'])) . '">View</a> &nbsp;
              <form method="post" action="' . e(url('admin/delete')) . '" style="display:inline"
                    onsubmit="return confirm(\'Delete &quot;' . e($p['title']) . '&quot;? This cannot be undone.\')">
                ' . csrf_field() . '
                <input type="hidden" name="id" value="' . (int)$p['id'] . '">
                <button class="danger">Delete</button>
              </form>
            </td></tr>';
    }
    if ($rows === '') {
        $rows = '<tr><td colspan="3" class="muted">Nothing here yet — write the first one.</td></tr>';
    }
    $label = $type === 'page' ? 'Pages' : 'Articles';
    $one = $type === 'page' ? 'page' : 'article';
    admin_shell($label, '
      <div class="topline">
        <h1>' . $label . '</h1>
        <a class="btn-link" href="' . e(url('admin/new/' . $one)) . '">New ' . $one . '</a>
      </div>
      <table>
        <tr><th>Title</th><th>Created</th><th></th></tr>' . $rows . '
      </table>');
}

function view_admin_edit(?array $post, string $type): never
{
    $isNew = $post === null;
    $one = $type === 'page' ? 'page' : 'article';
    $posField = $type === 'page'
        ? '<label>Menu position <span class="hint">(lower shows first)</span>
             <input type="number" name="position" value="' . (int)($post['position'] ?? 0) . '"></label>
           <label class="check"><input type="checkbox" name="show_title" value="1" '
             . (($post['show_title'] ?? 1) ? 'checked' : '') . '>
             Show title on the page <span class="hint">(untick for a homepage)</span></label>'
        : '';
    admin_shell($isNew ? "New $one" : 'Edit', '
      <h1>' . ($isNew ? "New $one" : 'Edit ' . $one) . '</h1>
      <form method="post" action="' . e(url('admin/save')) . '">
        ' . csrf_field() . '
        <input type="hidden" name="id" value="' . (int)($post['id'] ?? 0) . '">
        <input type="hidden" name="type" value="' . e($type) . '">
        <label>Title <input name="title" required value="' . e($post['title'] ?? '') . '"></label>
        <label>Slug <span class="hint">(leave blank to make one from the title)</span>
          <input name="slug" value="' . e($post['slug'] ?? '') . '"></label>
        ' . $posField . '
        <label>Search description <span class="hint">(for Google and social shares, ~155 characters; blank = automatic from the text)</span>
          <input name="description" maxlength="300" value="' . e($post['description'] ?? '') . '"></label>
        <label>Body <span class="hint">(markdown — **bold**, *italic*, # headings, [links](url), ``` code)</span>
          <textarea name="body">' . e($post['body'] ?? '') . '</textarea></label>
        <label class="check"><input type="checkbox" name="published" value="1" '
          . (($post['published'] ?? 1) ? 'checked' : '') . '> Published</label>
        <button>' . ($isNew ? 'Create' : 'Save changes') . '</button>
      </form>');
}

function admin_save(): never
{
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $type = ($_POST['type'] ?? '') === 'page' ? 'page' : 'article';
    $title = trim((string)($_POST['title'] ?? ''));
    $slug = slugify((string)($_POST['slug'] ?? '') !== '' ? (string)$_POST['slug'] : $title);
    $slug = unique_slug($slug, $id);
    $body = (string)($_POST['body'] ?? '');
    $published = isset($_POST['published']) ? 1 : 0;
    $position = (int)($_POST['position'] ?? 0);
    $showTitle = ($type === 'page' && !isset($_POST['show_title'])) ? 0 : 1;
    $description = trim((string)($_POST['description'] ?? ''));

    if ($title === '') {
        flash('A title is required.');
        redirect(url($id ? 'admin/edit/' . $id : 'admin/new/' . $type));
    }
    if ($id) {
        $st = db()->prepare('UPDATE posts SET title=?, slug=?, body=?, published=?, position=?,
                             show_title=?, description=?, updated=? WHERE id=?');
        $st->execute([$title, $slug, $body, $published, $position, $showTitle, $description, now(), $id]);
        flash('Saved.');
    } else {
        $st = db()->prepare('INSERT INTO posts (type, slug, title, body, published, position,
                             show_title, description, created, updated) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $st->execute([$type, $slug, $title, $body, $published, $position, $showTitle, $description, now(), now()]);
        flash(ucfirst($type) . ' created.');
    }
    redirect(url($type === 'page' ? 'admin/pages' : 'admin'));
}

function admin_delete(): never
{
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $st = db()->prepare('SELECT type FROM posts WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch();
    if ($p) {
        db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
        flash('Deleted.');
    }
    redirect(url(($p['type'] ?? '') === 'page' ? 'admin/pages' : 'admin'));
}

function view_admin_settings(): never
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        if (($_POST['form'] ?? '') === 'password') {
            $st = db()->prepare('SELECT * FROM users WHERE username = ?');
            $st->execute([$_SESSION['wren_user']]);
            $u = $st->fetch();
            $new = (string)($_POST['new_password'] ?? '');
            if (!$u || !password_verify((string)($_POST['current_password'] ?? ''), $u['password'])) {
                flash('Current password was wrong — nothing changed.');
            } elseif (strlen($new) < 8) {
                flash('New password needs at least 8 characters — nothing changed.');
            } else {
                db()->prepare('UPDATE users SET password = ? WHERE id = ?')
                    ->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
                flash('Password changed.');
            }
        } else {
            set_setting('site_title', trim((string)($_POST['site_title'] ?? 'Wren')));
            set_setting('tagline', trim((string)($_POST['tagline'] ?? '')));
            set_setting('posts_per_page', (string)max(1, (int)($_POST['posts_per_page'] ?? 5)));
            set_setting('pretty_urls', isset($_POST['pretty_urls']) ? '1' : '0');
            set_setting('meta_description', trim((string)($_POST['meta_description'] ?? '')));
            set_setting('og_image', trim((string)($_POST['og_image'] ?? '')));
            $hp = (string)($_POST['home_page'] ?? '');
            if ($hp !== '' && !post_by_slug($hp)) {
                $hp = ''; // only published pages can be the homepage
            }
            set_setting('home_page', $hp);
            $bt = trim((string)($_POST['blog_title'] ?? '')) ?: 'Blog';
            set_setting('blog_title', $bt);
            $bs = slugify((string)($_POST['blog_slug'] ?? '') !== '' ? (string)$_POST['blog_slug'] : $bt);
            set_setting('blog_slug', $bs);
            flash('Settings saved.');
        }
        redirect(url('admin/settings'));
    }
    $opts = '<option value="">Latest articles (default)</option>';
    foreach (db()->query('SELECT slug, title FROM posts WHERE type = "page" AND published = 1
                          ORDER BY position, title') as $p) {
        $sel = setting('home_page') === $p['slug'] ? ' selected' : '';
        $opts .= '<option value="' . e($p['slug']) . '"' . $sel . '>' . e($p['title']) . '</option>';
    }
    admin_shell('Settings', '
      <h1>Settings</h1>
      <form method="post">
        ' . csrf_field() . '
        <input type="hidden" name="form" value="site">
        <label>Site title <input name="site_title" required value="' . e(setting('site_title', 'Wren')) . '"></label>
        <label>Tagline <input name="tagline" value="' . e(setting('tagline')) . '"></label>
        <label>Site search description <span class="hint">(shown for the homepage in Google; blank = tagline)</span>
          <input name="meta_description" maxlength="300" value="' . e(setting('meta_description')) . '"></label>
        <label>Social share image URL <span class="hint">(optional, used by Open Graph)</span>
          <input name="og_image" value="' . e(setting('og_image')) . '"></label>
        <label>Homepage shows <span class="hint">(pick a page to run a page-first site)</span>
          <select name="home_page">' . $opts . '</select></label>
        <div class="row">
          <label>Articles page name <span class="hint">(menu label when a page is the homepage)</span>
            <input name="blog_title" value="' . e(setting('blog_title', 'Blog')) . '"></label>
          <label>Articles page slug
            <input name="blog_slug" value="' . e(setting('blog_slug', 'blog')) . '"></label>
        </div>
        <div class="row">
          <label>Articles per page
            <input type="number" name="posts_per_page" min="1" value="' . e(setting('posts_per_page', '5')) . '"></label>
        </div>
        <label class="check"><input type="checkbox" name="pretty_urls" value="1" '
          . (setting('pretty_urls') === '1' ? 'checked' : '') . '>
          Pretty URLs <span class="hint">(needs the .htaccess file from the Wren download)</span></label>
        <button>Save settings</button>
      </form>
      <hr style="border:0;border-top:1px solid var(--line);margin:2.2rem 0">
      <h1 style="font-size:1.2rem">Change password</h1>
      <form method="post">
        ' . csrf_field() . '
        <input type="hidden" name="form" value="password">
        <label>Current password <input type="password" name="current_password" autocomplete="current-password" required></label>
        <label>New password <span class="hint">(8+ characters)</span>
          <input type="password" name="new_password" autocomplete="new-password" required></label>
        <button>Change password</button>
      </form>
      <p class="muted" style="margin-top:2.5rem">Wren ' . WREN_VERSION . ' &middot; database: wren.db (SQLite)</p>');
}

/* ----------------------------------------------------------------- router */

$q = trim((string)($_GET['q'] ?? ''), '/');

// Pretty URLs: if no ?q= given, look at the request path.
if ($q === '' && !empty($_SERVER['REQUEST_URI'])) {
    $path = (string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    $path = trim($path, '/');
    if ($path !== '' && !str_ends_with($path, basename(__FILE__))) {
        $q = $path;
    }
}

if (needs_setup()) {
    view_setup();
}

$seg = $q === '' ? [] : explode('/', $q);

if (($seg[0] ?? '') === 'admin') {
    $sub = $seg[1] ?? '';
    if ($sub === 'login') {
        is_admin() ? redirect(url('admin')) : view_login();
    }
    if ($sub === 'logout') {
        session_destroy();
        redirect(url());
    }
    require_admin();
    match (true) {
        $sub === ''         => view_admin_list('article'),
        $sub === 'pages'    => view_admin_list('page'),
        $sub === 'settings' => view_admin_settings(),
        $sub === 'save'     => admin_save(),
        $sub === 'delete'   => admin_delete(),
        $sub === 'new'      => view_admin_edit(null, ($seg[2] ?? 'article') === 'page' ? 'page' : 'article'),
        $sub === 'edit'     => (function () use ($seg) {
            $st = db()->prepare('SELECT * FROM posts WHERE id = ?');
            $st->execute([(int)($seg[2] ?? 0)]);
            $p = $st->fetch();
            $p ? view_admin_edit($p, $p['type']) : redirect(url('admin'));
        })(),
        default => redirect(url('admin')),
    };
}

$homePage = setting('home_page');
$blogSlug = setting('blog_slug', 'blog');

if ($q === '') {
    if ($homePage !== '' && ($hp = post_by_slug($homePage))) {
        view_post($hp, true);
    }
    view_home();
}
if ($homePage !== '' && $q === $blogSlug) {
    view_home($blogSlug);
}
if ($q === 'rss') {
    view_rss();
}
if ($q === 'sitemap.xml') {
    view_sitemap();
}
if ($q === 'robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nAllow: /\nSitemap: " . abs_url('sitemap.xml') . "\n";
    exit;
}
if ($homePage !== '' && $seg[0] === $homePage) {
    redirect(url()); // the homepage lives at /, keep one canonical address
}

$post = post_by_slug($seg[0]);
$post ? view_post($post) : view_404();
