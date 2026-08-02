# SEO and Feeds

Wren handles search engine plumbing automatically; the only part you write is
descriptions.

## Descriptions

Every article and page has a **Search description** field — the sentence
search engines show under your title, and the text used when links are shared
socially. Aim for roughly 155 characters written for humans. Leave it blank
and Wren generates one from the opening of the text. The homepage uses the
site search description from Settings, falling back to the tagline.

## What every page carries

Wren outputs a canonical URL, the meta description, Open Graph tags
(site name, type, title, description, URL, and your share image if set), and a
Twitter card tag on every public page. These are injected into any theme
automatically; a theme can control their position by including `{{seo_head}}`.

## Sitemap and robots

`/sitemap.xml` is generated live from your published articles and pages, with
last-modified dates. `/robots.txt` welcomes crawlers and points them at the
sitemap. Submit the sitemap once to Google Search Console and Bing Webmaster
Tools and you are done — it updates itself thereafter.

## IndexNow

When you publish, update, or delete an article or page — or approve a
comment — Wren sends one small ping to IndexNow, the protocol shared by Bing,
DuckDuckGo, Yandex, Seznam, and Naver, and those engines recrawl the page
within minutes. (Google does not participate; it uses the sitemap.) There is
nothing to configure: the verification key generates itself and Wren serves it
automatically. Pings are fire-and-forget with a short timeout, so publishing
never blocks or breaks on hosts without outbound requests. A Settings tick
turns the whole thing off.

## RSS

`/rss` is a standard RSS 2.0 feed of your ten newest articles, linked from the
default theme footer and advertised in the page head for feed readers.
