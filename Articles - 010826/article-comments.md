<style>
.we-fig { background: var(--paper); border: 1px solid var(--line); border-radius: 10px; padding: 22px 18px 10px; margin: 22px 0; text-align: center; }
.we-fig figcaption { font-size: 12px; color: var(--muted); padding: 8px 0 6px; }
.we-fig svg { max-width: 100%; height: auto; }
</style>

A website that only talks *at* people is a brochure. From version 1.0, Wren sites can talk *with* them: **comments have arrived** — and if you scroll to the bottom of this article, you'll find them switched on.

## No accounts. No exceptions to your judgement.

Wren's comments rest on two convictions. The first: **readers shouldn't need an account to say something.** A name and a comment is all the form asks — no registration, no email address, no password for someone to forget. The lower the wall, the more of the good stuff gets over it.

The second conviction balances the first: **nothing appears on your site without your say-so.** Every comment, from everyone, every time, waits in a moderation queue. There's no reputation system deciding for you, no "trusted commenter" shortcut that spammers eventually find — just a new **Comments** tab in the admin with a badge counting what's waiting, and two buttons: Approve and Delete.

<figure class="we-fig">
<svg viewBox="0 0 640 190" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Diagram: a reader's comment goes to a held-for-review state; the admin approves it and it appears on the site, or deletes it and it vanishes">
<rect x="22" y="62" width="120" height="66" rx="10" fill="#ffffff" stroke="#e3e6ea" stroke-width="2"/>
<text x="82" y="90" text-anchor="middle" font-family="Arial" font-size="13" fill="#33373b">reader writes</text>
<text x="82" y="108" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">name + comment</text>
<path d="M147 95 H180" stroke="#e67300" stroke-width="3" stroke-linecap="round"/>
<polygon points="180,95 170,89 170,101" fill="#e67300"/>
<rect x="186" y="55" width="150" height="80" rx="10" fill="#fff6ec" stroke="#e67300" stroke-width="2"/>
<text x="261" y="86" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="#33373b">held for review</text>
<text x="261" y="106" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">visible only to you</text>
<path d="M341 78 Q390 55 435 55" stroke="#55613B" stroke-width="3" fill="none" stroke-linecap="round"/>
<polygon points="437,55 426,50 427,62" fill="#55613B"/>
<text x="385" y="46" text-anchor="middle" font-family="Arial" font-size="11" fill="#55613B" font-weight="bold">Approve</text>
<rect x="442" y="30" width="176" height="50" rx="10" fill="#33373b"/>
<text x="530" y="52" text-anchor="middle" font-family="Arial" font-size="12" fill="#ffffff">published on the site</text>
<text x="530" y="68" text-anchor="middle" font-family="Arial" font-size="9" fill="#cfd3d8">search engines pinged too</text>
<path d="M341 112 Q390 135 435 135" stroke="#8C3B2E" stroke-width="3" fill="none" stroke-linecap="round"/>
<polygon points="437,135 426,130 427,142" fill="#8C3B2E"/>
<text x="385" y="152" text-anchor="middle" font-family="Arial" font-size="11" fill="#8C3B2E" font-weight="bold">Delete</text>
<rect x="442" y="110" width="176" height="50" rx="10" fill="#ffffff" stroke="#e3e6ea" stroke-width="2" stroke-dasharray="5 4"/>
<text x="530" y="140" text-anchor="middle" font-family="Arial" font-size="12" fill="#6b727b">gone, quietly</text>
</svg>
<figcaption>Every comment takes this journey. There is no side door.</figcaption>
</figure>

## The spam question

Open a comment form on the internet and the robots arrive within days. Wren meets them with proportionate force: a **honeypot** — an invisible field humans never see but crawlers dutifully fill in, at which point their comment is silently binned — plus **rate limiting** so nobody can flood you, and the same CSRF protection every Wren form carries. Whatever slips through costs you exactly one glance and one Delete click, because remember: nothing publishes itself.

## Your site, your rooms

Comments are per-post, not all-or-nothing. Every article and page has its own **Allow comments** switch, so the blog can be lively while the homepage and privacy policy stay silent — and a page can opt *in*, if a guestbook is your idea of fun. One setting in the admin turns the whole system off site-wide, should you ever want a quieter life.

## Try it, right now

This is the first Wren feature you can test without installing anything: **the form is just below.** Say hello, tell us what you're building, or point out a typo — your comment will sit politely in our moderation queue, exactly as described, until it's approved.

Wren is free, MIT-licensed, and [one small download away](https://github.com/Wren-CMS/wren-cms/releases/latest). The comment box is open.
