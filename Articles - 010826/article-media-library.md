<style>
.wd-fig { background: var(--paper); border: 1px solid var(--line); border-radius: 10px; padding: 22px 18px 10px; margin: 22px 0; text-align: center; }
.wd-fig figcaption { font-size: 12px; color: var(--muted); padding: 8px 0 6px; }
.wd-fig svg { max-width: 100%; height: auto; }
</style>

For its first week of life, Wren had a quiet embarrassment: a CMS made for writing about things couldn't show you a picture of them. Images meant hosting files somewhere else and pasting URLs like it was 2003. As of version 1.0, that's over — **Wren has a media library.**

## Upload, click, paste

There's a new **Media** tab in the admin. Drop in a JPG, PNG, GIF or WebP and it appears in a thumbnail grid with one deliberately pleasing detail: every image shows its markdown address in a little box, and **clicking the box copies it**. Getting a photo into an article is upload, click, paste — no URL-wrangling, no remembering syntax.

Images live as plain files in a `media/` folder beside `index.php`. No database blobs, no opaque asset pipeline: backing up your entire site is still "copy one folder," and your photos are exactly where you'd look for them.

## The part you don't see

File uploads are, historically, how content systems get broken into — so this feature earned the most suspicious testing Wren has ever had. Every upload runs a gauntlet of three independent checks, and during development we threw genuinely hostile files at it: a PHP web-shell dressed up as a `.png` (rejected — it doesn't decode as an image), a real image named `shell.php` (rejected — wrong extension), and a few uglier things besides. None reached the disk.

<figure class="wd-fig">
<svg viewBox="0 0 640 200" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Diagram: an uploaded file passes three checks — extension, file type, and image decode — before reaching the media folder">
<rect x="20" y="70" width="110" height="60" rx="10" fill="#ffffff" stroke="#e3e6ea" stroke-width="2"/>
<text x="75" y="96" text-anchor="middle" font-family="Arial" font-size="13" fill="#33373b">upload</text>
<text x="75" y="114" text-anchor="middle" font-family="Consolas, monospace" font-size="11" fill="#6b727b">photo.jpg</text>
<path d="M135 100 H160" stroke="#e67300" stroke-width="3" stroke-linecap="round"/>
<rect x="165" y="70" width="100" height="60" rx="10" fill="#fff6ec" stroke="#e67300" stroke-width="1.5"/>
<text x="215" y="94" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="12" fill="#33373b">extension</text>
<text x="215" y="112" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">whitelist</text>
<path d="M270 100 H295" stroke="#e67300" stroke-width="3" stroke-linecap="round"/>
<rect x="300" y="70" width="100" height="60" rx="10" fill="#fff6ec" stroke="#e67300" stroke-width="1.5"/>
<text x="350" y="94" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="12" fill="#33373b">file type</text>
<text x="350" y="112" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">MIME sniff</text>
<path d="M405 100 H430" stroke="#e67300" stroke-width="3" stroke-linecap="round"/>
<rect x="435" y="70" width="100" height="60" rx="10" fill="#fff6ec" stroke="#e67300" stroke-width="1.5"/>
<text x="485" y="94" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="12" fill="#33373b">decodes?</text>
<text x="485" y="112" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b727b">real image test</text>
<path d="M540 100 H565" stroke="#e67300" stroke-width="3" stroke-linecap="round"/>
<polygon points="565,100 555,94 555,106" fill="#e67300"/>
<rect x="570" y="76" width="52" height="48" rx="8" fill="#33373b"/>
<text x="596" y="98" text-anchor="middle" font-family="Consolas, monospace" font-size="10" fill="#ffb066">/media</text>
<text x="596" y="114" text-anchor="middle" font-family="Arial" font-size="9" fill="#cfd3d8">saved</text>
<path d="M215 135 V166 M350 135 V166 M485 135 V166" stroke="#e3e6ea" stroke-width="2"/>
<text x="350" y="186" text-anchor="middle" font-family="Arial" font-size="11" fill="#8C3B2E">fail any check &rarr; rejected, never touches the disk</text>
</svg>
<figcaption>Three checks, in series. A web-shell in a party hat is still a web-shell.</figcaption>
</figure>

Beyond the gauntlet: filenames are sanitised (`Holiday Pic (Final)!.JPG` becomes `holiday-pic-final.jpg`), name collisions get a tidy `-2` suffix, and the media folder carries a guard that refuses to execute anything code-shaped — a last line of defence for a door that's already triple-locked.

## Still one small file

The engine remains a single `index.php`. Your site is now: that file, a database it manages itself, and a folder of your images — all of it visible, portable, and yours. Upgrading from any earlier version is the usual sentence: replace `index.php`, and the database updates itself.

Wren is free and MIT-licensed. [Download the latest release](https://github.com/Wren-CMS/wren-cms/releases/latest) and put some pictures on the internet.
