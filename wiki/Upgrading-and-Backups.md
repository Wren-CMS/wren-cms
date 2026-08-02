# Upgrading and Backups

## Upgrading

Replace `index.php` with the new version. That is the entire procedure — your
content, settings, comments, and images live in the database and the media
folder, and the database updates its own structure automatically on the next
page load. This has been the upgrade path since the first release and, as of
1.0, it is a promise.

Versions are announced at
<https://github.com/Wren-CMS/wren-cms/releases> and the changelog in the
repository records every change.

## Backups

Your entire site is the folder containing `index.php`: the engine, `wren.db`
(all content and settings), `media/` (your images), and `theme.html` if you
have one. **Backing up is copying that folder**; restoring is copying it back.
Download it over FTP or your host's file manager on whatever schedule suits
how often you write. Because SQLite is a single file, there are no database
exports to wrangle — though avoid copying `wren.db` at the exact moment you
are saving a post; a quiet moment is a consistent backup.
