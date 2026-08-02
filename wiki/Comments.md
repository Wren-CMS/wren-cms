# Comments

Readers can comment on articles — and on any page you opt in — with just a
name and their comment. No accounts, no email addresses, no passwords.

## Moderation first

Every comment, from everyone, every time, is held for review. Nothing appears
on your site until you approve it. The admin's **Comments** tab lists what is
waiting (a badge in the admin navigation counts pending items) alongside
everything already approved, with Approve and Delete buttons. Approving a
comment also pings IndexNow so search engines see the updated page.

## Spam defences

The comment form carries an invisible honeypot field that humans never see but
crawlers fill in — those submissions are silently discarded. A per-visitor
rate limit stops flooding (one comment per half-minute), and the form has the
same CSRF protection as every other form in Wren. Whatever slips through
costs you one glance and one Delete click, because nothing publishes itself.

## Switches

Comments are controlled at two levels. Site-wide: the **Allow comments on
articles** tick in Settings. Per-post: every article and page has its own
**Allow comments** tick — new articles default to on, new pages to off, so a
guestbook page can opt in while your homepage and privacy policy stay silent.
Both switches must be on for a form to appear, and a comment posted directly
at a disabled address is ignored.

## What is stored

Each comment stores the given name, the text, the time, and the poster's IP
address (kept for spam prevention and moderation). If your site has a privacy
policy, mention this. Deleting a post deletes its comments with it.
