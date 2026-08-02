# Writing Content

Wren has three content types, all managed from the admin. **Articles** are
dated posts: they appear on the article listing newest-first and in the RSS
feed, and readers can comment on them. **Pages** are timeless: they appear in
the site menu and suit things like About or Contact. **Links** are menu
entries that point anywhere (covered in [[Menus]]).

## The editor

Every article and page has a title, a slug (the address — leave it blank and
Wren makes one from the title, resolving collisions with a `-2` suffix), a
search description (see [[SEO and Feeds]]), a markdown body, and a Published
tick. Untick Published and the item becomes a draft only you can see. Articles
and pages each have an **Allow comments** tick (see [[Comments]]); pages
additionally have a menu position, a menu parent, and a **Show title on the
page** tick — untick that last one for a homepage that shouldn't display its
own name.

One convention to know: **do not start the body with a `#` heading repeating
the title** — Wren renders the title as the page heading for you, so you would
get it twice.

## Markdown

The body is markdown. Wren supports `# headings` through `###### `, `**bold**`,
`*italic*`, `[links](https://example.com)`, `![images](/media/photo.jpg)`,
bulleted and numbered lists, `> blockquotes`, horizontal rules (`---`),
`` `inline code` ``, and fenced code blocks:

    ```
    code here is displayed verbatim and never executed or templated
    ```

Raw HTML passes straight through — you are the site's trusted author, so if
you want a `<figure>`, an inline SVG, or a scoped `<style>` block, paste it in.
One rule when embedding larger HTML: keep blank lines *between* blocks, never
*inside* an HTML block, because blank lines are how Wren separates blocks.

## The article listing and excerpts

The listing shows each article's title, date, and an automatic excerpt taken
from the beginning of its text (styles and scripts are excluded). Listing
length is controlled by **Articles per page** in Settings, with older/newer
pagination appearing automatically.
