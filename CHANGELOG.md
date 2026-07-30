# Changelog

## 0.1.0 "Troglodytes" — 30 July 2026

First release.

- Single-file engine (index.php), SQLite database created on first run
- One-minute browser setup: site title, admin account, seeded first article
- Articles (home page + RSS) and pages (site menu, orderable), markdown editing
- Drafts, pagination, slugs with collision handling, 404 page
- Theming via a single optional theme.html with {{tag}} placeholders
- Security: password_hash, CSRF tokens on every form, prepared statements,
  login throttling, escaped output, optional .htaccess that also shields wren.db
