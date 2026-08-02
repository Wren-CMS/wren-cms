# Wren CMS

**A whole website in one small file.**

Wren is a content management system in the spirit of sNews (2004–2016), rebuilt
from scratch for the modern web. One PHP file, one SQLite database it creates
itself, no framework, no Composer, no build step. Upload it, open it in a
browser, and start writing.

Version 1.0.0 · MIT licence

## Requirements

- PHP 8.1 or newer with PDO SQLite — standard on virtually every shared host
- That's it. No MySQL setup, no command line, no dependencies.

## Install

1. Upload `index.php` to your web space (a folder of its own is tidiest).
2. Visit it in a browser.
3. Fill in the one-minute setup form: site title, username, password.

Wren creates `wren.db` beside itself and signs you in. Done.

**Optional:** upload `.htaccess` too (Apache with mod_rewrite), then switch on
*Pretty URLs* in Settings to get `/my-article` instead of `/?q=my-article`.
The `.htaccess` also blocks direct downloads of `wren.db` — recommended.
On hosts without `.htaccess` support, keep Pretty URLs off and consider moving
`wren.db` out of the web root once you're comfortable editing the `WREN_DB`
constant at the top of the file.

## Writing

- **Articles** appear on the home page, newest first, and in the RSS feed (`/rss`).
- **Pages** appear in the site menu (order them with *menu position*, and
  nest them one level deep with *menu parent* for dropdown submenus). A menu
  link with a blank URL becomes a heading-only item for grouping.
- Both are written in markdown: `**bold**`, `*italic*`, `# headings`,
  `[links](https://…)`, `![images](https://…)`, lists, quotes, and fenced
  ``` code blocks. Raw HTML passes through untouched — you're the admin,
  Wren trusts you.
- Untick *Published* to keep something as a draft only you can see.
- **Images:** upload JPG, PNG, GIF or WebP in the admin's Media tab, then click
  an image's address to copy ready-made markdown into any post.
- **Comments:** readers can comment on articles (and pages, if you opt one
  in); every comment waits in the admin's Comments tab until you approve it.
  Honeypot and rate-limiting keep casual spam out; each post has its own
  "Allow comments" switch, plus a site-wide toggle in Settings.
- **Page-first site:** in Settings, set *Homepage shows* to any page and your
  front page becomes that page; the article list moves to its own menu entry
  (name and address configurable, "Blog" by default).

## Theming

Wren ships with a built-in default theme. To use your own, place a file named
`theme.html` beside `index.php` — plain HTML with these tags where you want
things to appear:

    {{site_title}}  {{tagline}}   {{page_title}}  {{menu}}
    {{content}}     {{home_url}}  {{rss_url}}     {{year}}  {{generator}}

That's the entire theming API. A theme is one HTML file a designer can read.

## Security notes

- Passwords are stored with PHP's `password_hash` (bcrypt/argon2, never plain).
- Every form is CSRF-protected; every query uses prepared statements.
- Repeated failed logins are slowed automatically.
- All reader-facing output of your titles and settings is HTML-escaped.

## Roadmap

Comments with moderation, image uploads, search, multiple authors, export.
Deliberately *not* on the roadmap: plugins, page builders, anything that would
mean Wren no longer fits in your head.

---

*Named for the wren: a tiny bird with a very loud song. Built as a tribute to
sNews by Luka Cvrk / Solucija, which proved twenty years ago that a CMS could
be small enough to read.*
