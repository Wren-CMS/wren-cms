# Settings Reference

Everything on the admin's Settings screen, in order.

**Site title** and **Tagline** appear in your theme wherever it places
`{{site_title}}` and `{{tagline}}`, and in feeds.

**Site search description** is what search engines show for your homepage;
leave it blank and the tagline is used. **Social share image URL** is an
optional image (ideally 1200×630) used by Open Graph when links are shared.

**Homepage shows** turns Wren from blog-first to page-first: pick any
published page and it becomes your front page at `/`, while the article
listing moves to its own menu entry. The chosen page leaves the menu (it *is*
Home) and its old address redirects to `/`. Choose "Latest articles" to revert.

**Articles page name**, **slug**, and **menu position** control that listing's
menu entry when a static homepage is set — by default "Blog" at `/blog`,
position 0 (right after Home).

**Articles per page** sets listing length before pagination appears.

**Allow comments on articles** is the site-wide comments switch; each post
also has its own (see [[Comments]]).

**Ping search engines on publish** is the IndexNow toggle (see
[[SEO and Feeds]]); on by default.

**Pretty URLs** switches all generated links to the clean `/article-name`
form. It requires the `.htaccess` file from the download on an Apache host
with mod_rewrite — turn it off again if links start returning 404s.

Below the settings, **Change password** does what it says, requiring your
current password first. There is no self-service reset (Wren sends no email),
so keep the password somewhere safe; recovery is covered in
[[Troubleshooting]].
