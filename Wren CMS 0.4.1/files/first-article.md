<style>
.wa-fig { background: var(--paper); border: 1px solid var(--line); border-radius: 10px; padding: 22px 18px 10px; margin: 22px 0; text-align: center; }
.wa-fig figcaption { font-size: 12px; color: var(--muted); padding: 8px 0 6px; }
.wa-fig svg { max-width: 100%; height: auto; }
</style>

Twenty years ago, a CMS called sNews made a quiet, radical argument: a whole website engine could live in one PHP file that a curious person could read from top to bottom. No installers, no frameworks, no mystery. It powered thousands of small sites before development wound down, but the idea never stopped being right.

**Wren is that idea, rebuilt from scratch for 2026.**

One `index.php`. A SQLite database it creates by itself on first run. Markdown writing, pages and articles, RSS, a theming system that is a single HTML file with nine tags, and the security a modern site demands — hashed passwords, CSRF protection, prepared statements — built in from the first line rather than bolted on afterwards.

<figure class="wa-fig">
<svg viewBox="0 0 640 150" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Diagram: one file plus one database equals a whole website">
<rect x="30" y="35" width="150" height="80" rx="10" fill="#ffffff" stroke="#e3e6ea" stroke-width="2"/>
<text x="105" y="70" text-anchor="middle" font-family="Consolas, Monaco, monospace" font-size="16" fill="#33373b">index.php</text>
<text x="105" y="93" text-anchor="middle" font-family="Arial" font-size="11" fill="#6b727b">the whole engine</text>
<text x="215" y="83" text-anchor="middle" font-family="Arial" font-size="26" fill="#e67300" font-weight="bold">+</text>
<rect x="250" y="35" width="150" height="80" rx="10" fill="#ffffff" stroke="#e3e6ea" stroke-width="2"/>
<text x="325" y="70" text-anchor="middle" font-family="Consolas, Monaco, monospace" font-size="16" fill="#33373b">wren.db</text>
<text x="325" y="93" text-anchor="middle" font-family="Arial" font-size="11" fill="#6b727b">creates itself</text>
<text x="435" y="83" text-anchor="middle" font-family="Arial" font-size="26" fill="#e67300" font-weight="bold">=</text>
<rect x="470" y="25" width="150" height="100" rx="10" fill="#33373b"/>
<circle cx="513" cy="53" r="12" fill="#e67300"/>
<circle cx="521" cy="45" r="7" fill="#e67300"/>
<polygon points="505,49 494,38 510,44" fill="#f4f5f7"/>
<rect x="500" y="78" width="90" height="6" rx="3" fill="#6b727b"/>
<rect x="500" y="92" width="70" height="6" rx="3" fill="#6b727b"/>
<text x="545" y="120" text-anchor="middle" font-family="Arial" font-size="11" fill="#cfd3d8">your website</text>
</svg>
<figcaption>The complete architecture diagram. There is no page two.</figcaption>
</figure>

## Why so small?

Because small is a feature. A codebase of about a thousand readable lines means you can audit every query it runs, understand every request it serves, and fix anything yourself. Upgrades are "replace one file" — the database migrates itself. Backups are "copy two files." When something is simple enough to fit in your head, it stops being infrastructure and starts being yours.

That cuts both ways, and we're honest about it: there are no plugins, no page builders, no user roles, and no plans for any of them. Wren is for fast, honest small sites — blogs, project pages, documentation, the web's connective tissue — not shops or social networks.

## A busy first day

Wren went from first commit to version 0.4.0 in a single day of releases, each named after a wren species:

<figure class="wa-fig">
<svg viewBox="0 0 640 130" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Release timeline from 0.1.0 to 0.4.0">
<line x1="60" y1="55" x2="580" y2="55" stroke="#e3e6ea" stroke-width="4" stroke-linecap="round"/>
<circle cx="90" cy="55" r="10" fill="#e67300"/>
<text x="90" y="33" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="#33373b">0.1.0</text>
<text x="90" y="82" text-anchor="middle" font-family="Arial" font-size="11" fill="#6b727b">Troglodytes</text>
<text x="90" y="97" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">the core</text>
<circle cx="253" cy="55" r="10" fill="#e67300"/>
<text x="253" y="33" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="#33373b">0.2.0</text>
<text x="253" y="82" text-anchor="middle" font-family="Arial" font-size="11" fill="#6b727b">Aedon</text>
<text x="253" y="97" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">static homepages</text>
<circle cx="416" cy="55" r="10" fill="#e67300"/>
<text x="416" y="33" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="#33373b">0.3.0</text>
<text x="416" y="82" text-anchor="middle" font-family="Arial" font-size="11" fill="#6b727b">Hiemalis</text>
<text x="416" y="97" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">title control</text>
<circle cx="565" cy="55" r="10" fill="#ff7a00"/>
<circle cx="565" cy="55" r="15" fill="none" stroke="#ff7a00" stroke-width="2" opacity="0.4"/>
<text x="565" y="33" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="#33373b">0.4.0</text>
<text x="565" y="82" text-anchor="middle" font-family="Arial" font-size="11" fill="#6b727b">Pacificus</text>
<text x="565" y="97" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">SEO throughout</text>
</svg>
<figcaption>Four releases, one day. Small software moves quickly.</figcaption>
</figure>

Along the way Wren grew static homepages (so it runs page-first sites, not just blogs), per-page title control, automatic database migrations, and a full SEO layer: search descriptions, canonical URLs, Open Graph tags, and a live `sitemap.xml` — this very site is the proof, since wrencms.com runs on Wren.

## What's next

The roadmap stays deliberately short: comments with moderation, image uploads, and search are the candidates being weighed for coming releases — always against the question that governs everything here: *does it still fit in your head?*

Wren is MIT-licensed and free forever. [Download the latest release](https://github.com/Wren-CMS/wren-cms/releases/latest), read the source [on GitHub](https://github.com/Wren-CMS/wren-cms), and if you build something with it, we'd genuinely love to hear.

*Named for a tiny bird with a very loud song.*
