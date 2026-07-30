# Changelog

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
