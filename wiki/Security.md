# Security

Wren was built in 2026, not patched up to it. Passwords are stored with PHP's
modern `password_hash` (and quietly re-hashed to stronger settings when you
log in as defaults improve). Every form — admin and public — carries CSRF
protection. Every database query is a prepared statement. Failed logins are
throttled. All reader-facing output of titles, settings, and comments is
HTML-escaped. Uploads pass three independent checks and the media folder
refuses to execute code. Comments never publish without approval. The admin
area asks search engines not to index it.

The small codebase is its own audit advantage: at around 1,600 lines, you can
read every query Wren runs in an evening.

## What you should do

Serve the site over HTTPS and only sign in over it. Use a strong admin
password — there is no reset email to save you. Keep the `.htaccess` from the
download in place: it blocks direct downloads of `wren.db` (verify by
visiting `/wren.db` — you want Forbidden, not a download). Never leave
diagnostic or test PHP files on the server. And apply updates when they are
released — with a one-file upgrade there is no excuse not to.

Found a vulnerability? Please report it privately to
**webmaster@wrencms.com** rather than opening a public issue.
