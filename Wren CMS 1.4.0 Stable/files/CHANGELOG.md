# Changelog

## 1.4.0 "Rufociliatus" — 2 August 2026

- New: menu links gain an "Open in a new tab" option (rendered with
  rel="noopener" for safety); existing links keep opening in the same tab


## 1.3.1 — 2 August 2026

- Fix: top-level menu items with submenus now align vertically with their
  siblings (the dropdown wrapper is inline-flex and centred, so it behaves
  in flexbox navs)


## 1.3.0 "Tanneri" — 1 August 2026

- New: heading-only menu items — leave a menu link's URL blank and it becomes
  a label that navigates nowhere but can hold a dropdown (e.g. a "Company"
  heading with About, Privacy and Contact beneath it)
- Menu parents can now be top-level links as well as pages; labels stay
  keyboard-focusable so dropdowns open by keyboard too
- A label's own address harmlessly redirects to the homepage


## 1.2.0 "Sissonii" — 1 August 2026

- New: layered menus — pages and links can nest one level under a top-level
  page via a "Menu parent" selector; parents render as hover/focus dropdowns
  with sensible default styling injected automatically (override with your
  own .menu-sub / .menu-drop rules if you like)
- Visiting a child marks the parent branch active; deleting or demoting a
  parent promotes its children back to the top level, and unpublished parents
  never hide their children
- New: "Articles menu position" setting — when using a static homepage, the
  articles entry (Blog/News) now orders among your pages instead of always
  sitting first


## 1.1.0 "Ochraceus" — 1 August 2026

- New: per-post comment control — every article and page editor gains an
  "Allow comments" checkbox (alongside the site-wide Settings toggle)
- Pages can now opt in to comments (for a guestbook or feedback page);
  new pages default to comments off, new articles to on
- Upgrading: existing articles keep comments on; existing pages stay
  comment-free — nothing changes until you tick the box


## 1.0.0 "Troglodytes troglodytes" — 1 August 2026

Wren is stable. The upgrade procedure — replace index.php, the database
updates itself — is now a promise.

- New: comments on articles, with moderation-first design — every comment
  waits in the admin's new Comments tab (with a pending count badge) until
  approved; nothing appears on the site without the author's say-so
- Comment spam defences: honeypot field, per-visitor rate limiting, CSRF
  protection, and full output escaping; deleting a post removes its comments
- New: settings toggle to disable comments site-wide
- Approving a comment pings IndexNow so search engines see the updated page

(1.0.0 also includes the media library introduced as 0.7.0 in the same
release cycle: validated image uploads, thumbnail grid, click-to-copy
markdown, and a PHP-execution guard on the media folder.)


## 0.7.0 "Musculus" — 1 August 2026

- New: media library — a Media tab in the admin for uploading images (JPG,
  PNG, GIF, WebP), with a thumbnail grid, click-to-copy markdown snippets,
  and delete
- Uploads are strictly validated: extension whitelist, MIME sniffing, and a
  real image-decode check; filenames are sanitised, collisions auto-suffixed,
  and a guard file blocks anything in /media from ever executing as PHP
- Images are plain files in a /media folder beside index.php — served
  statically, backed up by copying the folder


## 0.6.0 "Bewickii" — 30 July 2026

- New: IndexNow built in — publishing, updating, or deleting an article or
  page quietly pings api.indexnow.org so Bing, DuckDuckGo, Yandex, Seznam
  and Naver recrawl within minutes (Google reads the sitemap instead).
  Zero configuration: the key generates itself and is served automatically;
  a settings toggle turns it off. Fire-and-forget with a 2-second timeout,
  so publishing never blocks or breaks on hosts without outbound requests
- Fix: settings written and re-read within one request no longer return a
  stale value


## 0.5.0 "Ludovicianus" — 30 July 2026

- New: menu links — a third content type alongside articles and pages, managed
  from a Links tab in the admin. A link has a label, a URL, a menu position,
  and a published toggle; bare domains are normalised to https automatically
- New: each link's slug doubles as a short URL on your own site
  (e.g. /download can redirect to your latest GitHub release)
- Sitemap correctly lists only articles and pages


## 0.4.1 — 30 July 2026

- Fix: article excerpts and automatic search descriptions no longer include
  the contents of style or script blocks embedded in a post's HTML


## 0.4.0 "Pacificus" — 30 July 2026

- New: SEO throughout — every article and page gets a "Search description"
  field (automatic excerpt when blank), plus canonical URLs, Open Graph and
  Twitter card tags on every public page
- New: sitemap.xml, generated live from your published content
- New: robots.txt route pointing crawlers at the sitemap (pretty-URL sites)
- New settings: site search description and an optional social share image
- Themes need no changes: tags are injected into any theme automatically,
  or place {{seo_head}} in yours to control the position


## 0.3.0 "Hiemalis" — 30 July 2026

- New: pages have a "Show title on the page" option — untick it to render the
  page body without the big heading, made for static homepages
- New: automatic database migrations — upgrading is still just "replace
  index.php"; the database updates itself on the next page load
- The browser tab title and menu label still use the page's title either way


## 0.2.0 "Aedon" — 30 July 2026

- New: static homepage — Settings → "Homepage shows" can point the front page
  at any published page instead of the article list
- New: articles page — when a static homepage is set, the article list moves to
  its own menu entry with a configurable name and slug (default "Blog"),
  with pagination intact
- The chosen homepage is dropped from the page menu (it is "Home") and its old
  address redirects to / so each page keeps one canonical URL


## 0.1.0 "Troglodytes" — 30 July 2026

First release.

- Single-file engine (index.php), SQLite database created on first run
- One-minute browser setup: site title, admin account, seeded first article
- Articles (home page + RSS) and pages (site menu, orderable), markdown editing
- Drafts, pagination, slugs with collision handling, 404 page
- Theming via a single optional theme.html with {{tag}} placeholders
- Security: password_hash, CSRF tokens on every form, prepared statements,
  login throttling, escaped output, optional .htaccess that also shields wren.db
