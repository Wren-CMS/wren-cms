# Theming

A Wren theme is **one HTML file** named `theme.html` placed beside
`index.php`. Delete the file and the built-in theme returns — that is the
entire risk model of experimenting.

## The nine tags

Write plain HTML and CSS, and place these where you want things to appear:

`{{site_title}}` `{{tagline}}` `{{page_title}}` (title · site, for `<title>`)
`{{menu}}` `{{content}}` `{{home_url}}` `{{rss_url}}` `{{year}}`
`{{generator}}`

That is the whole template language. No PHP in the theme, no build step.
Optionally add `{{seo_head}}` inside `<head>` to control where the SEO tags
land; omit it and they are injected automatically.

## A complete minimal theme

```html
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{page_title}}</title>
<style>
  body { max-width: 42rem; margin: 2rem auto; padding: 0 1rem;
         font: 17px/1.65 Georgia, serif; }
  nav a { margin-right: 1rem; }
  .post-meta { color: #888; font-size: .8rem; text-transform: uppercase; }
</style>
</head>
<body>
<h1><a href="{{home_url}}">{{site_title}}</a></h1>
<p>{{tagline}}</p>
<nav>{{menu}}</nav>
{{content}}
<footer><p>&copy; {{year}} &middot; <a href="{{rss_url}}">RSS</a></p></footer>
</body>
</html>
```

## Classes Wren gives you to style

Content arrives marked up with stable classes. Articles and pages use
`post-title`, `post-meta`, `post-body`, `more` (the read-on link), and
`pagination`. The menu marks the current item with `active`; submenu parents
are wrapped in `menu-sub` (plus `active-branch` when the current page is
inside), dropdowns are `menu-drop`, and heading-only labels are `menu-label` —
override those three to restyle dropdowns. Comments use `comments`, `comment`,
`comment-form`, and `comment-notice`.

## Tips

Define your palette as CSS variables at the top of the file so retuning
colours later is a five-second job. Style the content classes so your
typography reaches inside articles. And remember themes are per-site: one
`theme.html`, no options panel — if you want the site to look different, you
edit an HTML file.
