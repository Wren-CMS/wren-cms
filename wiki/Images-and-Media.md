# Images and Media

The **Media** tab in the admin handles images. Upload a JPG, PNG, GIF, or WebP
and it appears in a thumbnail grid showing its size — and, usefully, its
markdown address in a small box: **click the box and the markdown is copied**,
ready to paste into any article or page. Deleting an image is a button on its
card.

Images are stored as plain files in a `media/` folder that Wren creates beside
`index.php`. Nothing goes into the database, so backing up your images means
copying that folder, and your files are always exactly where you would look
for them.

## Validation and safety

Every upload passes three independent checks — an extension whitelist, a MIME
type sniff, and a real image-decode test — so a script dressed up as an image
is rejected before it ever touches the disk. Filenames are sanitised
(`Holiday Pic (Final)!.JPG` becomes `holiday-pic-final.jpg`), collisions get a
`-2` suffix, and the media folder carries an `.htaccess` guard that refuses to
execute anything code-shaped as a final line of defence.

The maximum upload size is whatever your host's PHP allows
(`upload_max_filesize`); the Media page displays the current limit. SVG is
deliberately not accepted, because SVG files can contain scripts.
