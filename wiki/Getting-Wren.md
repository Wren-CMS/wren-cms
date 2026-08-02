# Getting Wren

Download the latest release from
<https://github.com/Wren-CMS/wren-cms/releases/latest> — the zip contains
three files:

`index.php` is the entire CMS. `.htaccess` is optional but recommended on
Apache: it enables pretty URLs (`/my-article` instead of `/?q=my-article`) and
blocks direct downloads of your database file. `README.md` is a condensed
version of this wiki.

## What you need to run it

A web host with **PHP 8.1 or newer** and the **PDO SQLite** extension. That
combination is standard on virtually every host, including inexpensive shared
hosting. You do not need MySQL, shell access, Composer, Node, or anything else.

If you are unsure what your host runs, most control panels show the PHP
version prominently (cPanel calls it "Select PHP Version" or "MultiPHP
Manager"). Choose 8.1 or newer — 8.3 is ideal.

## What Wren deliberately is not

There are no plugins, no page builders, and no visitor accounts, and none are
planned. Wren is for fast, honest small sites — blogs, project sites,
documentation, personal pages. If you need a shop or a members' area, use
something bigger; if you want a site you fully understand, welcome.
