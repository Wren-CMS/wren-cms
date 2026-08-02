# Installation

Installation is genuinely one step: **upload `index.php` to your web space and
open it in a browser.**

Upload it to the folder your domain serves (commonly `public_html/` or
`htdocs/`). Uploading the `.htaccess` file alongside it is recommended on
Apache hosts. Then visit your domain.

## The setup screen

On first load Wren creates its database (`wren.db`, beside `index.php`) and
shows a one-minute form asking for a site title, an optional tagline, an admin
username, and a password of at least eight characters. Submitting it signs you
in and seeds a first "Hello, world" article you can edit or delete.

That is the whole installation. Your site is live.

## After installing

Three things worth doing in the first sitting. First, turn on HTTPS if your
host has not already (most offer free certificates under an "SSL/TLS" menu),
and always sign in to the admin over https. Second, if you uploaded
`.htaccess`, go to **Settings** and tick **Pretty URLs** so every link uses the
clean `/article-name` form. Third, confirm your database is shielded: visiting
`yourdomain.com/wren.db` should give "Forbidden" or a 404, never a download —
the `.htaccess` handles this.

## The admin area

The admin lives at `/admin` (or `/?q=admin` without pretty URLs). Its tabs are
Articles, Pages, Links, Media, Comments, and Settings — each covered by its
own wiki page. Signing out is the last link in the admin navigation.
