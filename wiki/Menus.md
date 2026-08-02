# Menus

The site menu is built automatically from your published pages and links, in
order of their **menu position** (lower numbers first; ties order
alphabetically). Home always comes first. The current location is marked with
an `active` class your theme can style.

## Menu links

The **Links** tab creates menu entries that point anywhere — another site, a
document, a mailto address. A link has a label, a URL, a position, and a
"Shown in menu" tick. Bare domains are normalised to `https://` automatically.
Every link's slug also works as a short URL on your own site: a link named
Download pointing at your latest release means `yoursite.com/download`
redirects there.

## Dropdown submenus

Pages and links can nest **one level** under any top-level page or link: set
the **Menu parent** field in the editor. The parent renders as a dropdown that
opens on hover and on keyboard focus, with sensible default styling injected
automatically (override `.menu-sub` and `.menu-drop` in your theme if you want
a different look). Visiting a child marks the parent branch active. If a
parent is deleted, demoted to a child itself, or unpublished, its children are
promoted to the top level rather than lost.

## Heading-only labels

To group items under a heading that is not itself a destination — say, a
**Company** item holding About, Privacy and Contact — create a **link with a
blank URL**. It renders themed like the rest of the menu, navigates nowhere,
and can parent a dropdown. Its own address harmlessly redirects to the
homepage.

## The articles entry

When you use a static homepage (see [[Settings Reference]]), the article
listing gets its own menu entry with a configurable name, address, and
position — by default "Blog" at position 0, which places it right after Home.
Give it a higher position number to move it later in the menu.
