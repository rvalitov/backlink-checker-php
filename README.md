# Backlink Validator for SEO

![PHP version icon](https://img.shields.io/static/v1?label=PHP\&message=8.0%20-%208.4\&color=blue)
![Platform icon](https://img.shields.io/badge/Platform-Windows%2C%20Linux%2C%20Mac-blue)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/328e8eae9a494098b8a8ee66d602f781)](https://app.codacy.com/gh/rvalitov/backlink-checker-php/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/328e8eae9a494098b8a8ee66d602f781)](https://app.codacy.com/gh/rvalitov/backlink-checker-php/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=rvalitov_backlink-checker-php&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=rvalitov_backlink-checker-php)
[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=rvalitov_backlink-checker-php&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=rvalitov_backlink-checker-php)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=rvalitov_backlink-checker-php&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=rvalitov_backlink-checker-php)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=rvalitov_backlink-checker-php&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=rvalitov_backlink-checker-php)
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=rvalitov_backlink-checker-php&metric=bugs)](https://sonarcloud.io/summary/new_code?id=rvalitov_backlink-checker-php)
[![Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=rvalitov_backlink-checker-php&metric=vulnerabilities)](https://sonarcloud.io/summary/new_code?id=rvalitov_backlink-checker-php)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=rvalitov_backlink-checker-php&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=rvalitov_backlink-checker-php)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=rvalitov_backlink-checker-php&metric=sqale_index)](https://sonarcloud.io/summary/new_code?id=rvalitov_backlink-checker-php)
[![PHP 8.0 Build Test](https://github.com/rvalitov/backlink-checker-php/actions/workflows/php-8.0.yml/badge.svg)](https://github.com/rvalitov/backlink-checker-php/actions/workflows/php-8.0.yml)
[![PHP 8.1 Build Test](https://github.com/rvalitov/backlink-checker-php/actions/workflows/php-8.1.yml/badge.svg)](https://github.com/rvalitov/backlink-checker-php/actions/workflows/php-8.1.yml)
[![PHP 8.2 Build Test](https://github.com/rvalitov/backlink-checker-php/actions/workflows/php-8.2.yml/badge.svg)](https://github.com/rvalitov/backlink-checker-php/actions/workflows/php-8.2.yml)
[![Linux Build Test](https://github.com/rvalitov/backlink-checker-php/actions/workflows/linux.yml/badge.svg)](https://github.com/rvalitov/backlink-checker-php/actions/workflows/linux.yml)
[![Windows Build Test](https://github.com/rvalitov/backlink-checker-php/actions/workflows/windows.yml/badge.svg)](https://github.com/rvalitov/backlink-checker-php/actions/workflows/windows.yml)
[![Mac Build Test](https://github.com/rvalitov/backlink-checker-php/actions/workflows/mac.yml/badge.svg)](https://github.com/rvalitov/backlink-checker-php/actions/workflows/mac.yml)
![License icon](https://img.shields.io/badge/License-GPL--3-blue)

![Social preview](media/social-preview.jpg)

This project is a package for developers to validate backlinks in SEO.
Unlike other tools that scan the Internet or analyze Google Search results to find backlinks,
our tool focuses exclusively on verifying backlinks from a list you already have.

You might get this list of backlinks through:

- **Purchasing backlinks**: Receiving a list of donor web pages from the seller or backlinks market.
- **Creating backlinks yourself**: Posting on forums, third-party websites, or other platforms.
- **SEO service providers**: your SEO expert or company shares a reports with backlinks as one of the SEO strategies.

Once you have a list of donor web pages, it's crucial to confirm that the backlinks are present and functional.
Moreover, ongoing validation is necessary to ensure the backlinks remain active and haven’t been removed over time.

This package simplifies backlink checking and verification by:

- Verifying the presence of fixed backlinks, such as `https://example.com`.
- Supporting powerful search patterns, like `*.example.com`, using
  [regular expressions](https://en.wikipedia.org/wiki/Regular_expression) defined by you.

With this package, monitoring and maintaining your backlinks becomes efficient and hassle-free.

## Browser Scraping Modes

### Simple

Pros:

- **Fast**: Simple mode is faster.
- **Low resource consumption**: Simple mode uses fewer resources.
- **Minimal dependencies**: Simple mode requires fewer dependencies.
- **Shared hosting**: Simple mode works on shared hosting.

Cons:

- **No JavaScript**: Simple mode does not support JavaScript,
  so it works only for simple or static HTML, for example, generated by Joomla, WordPress or Drupal.
  It will not find backlinks on websites that require JavaScript-enabled browser,
  for example, websites made with Laravel, Yii, React, etc.

### Chromium

The [Chromium headless mode](https://developers.google.com/web/updates/2017/04/headless-chrome) is used
for JavaScript-enabled browsing.
This approach allows parsing any website, and this is the universal mode,
but it uses more resources on the server and requires a little bit more time to configure the server.

Pros:

- **Universal**: Chromium mode supports JavaScript, so it can parse any website.

Cons:

- **Slower**: Chromium mode is slower.
- **Higher resource consumption**: Chromium mode uses more resources.
- **Advanced hosting plan**: requires a VPS or dedicated server, does not work on shared hosting.

## How to Install

### Step 1. Add the Package via Composer

You must have a [Composer](https://getcomposer.org/) installed.
Run the following command:

```bash
php composer require rvalitov/backlink-checker-php:^2.0.0
```

Here we use version 2.0.0 or later that support PHP 8.0 and latest versions of dependencies.
If you want to use an earlier version, please check the 1.x.x releases.

Some dependencies from version 1.x.x are not supported anymore.
Therefore, I had to switch to community-driven forks.
As a result, such forks are not published in the composer library and have `dev` status.
To use them, you need to add the following two repositories to your `composer.json` file,
so that composer knows where to look for them.

```json
"repositories": [
{
"type": "git",
"url": "https://github.com/zoonru/puphpeteer.git"
},
{
"type": "git",
"url": "https://github.com/zoonru/rialto.git"
}
]
```

Besides, please add the following config (for example, before or after the `repositories` section) to allow composer to
use `dev` versions of the packages:

```json
"minimum-stability": "dev",
"prefer-stable": true
```

After that run update:

```bash
php composer update
```

### Step 2. Install the Chromium

**Note:** You can skip this step if you don't need the Chromium mode.

You need to install the following packages first, to make the Chromium work.

**For Debian/Ubuntu:**

```bash
apt-get update
apt-get install gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget
```

You must have a [Node.Js](https://nodejs.org/) installed.
If it's not installed, install it using the official manual.
Then run the following command to install the Chromium:

```bash
npm install
```

### Step 3. Use Autoload

Include the `autoload.php` in your source PHP file, for example:

```php
<?php
require __DIR__ . '/vendor/autoload.php';
```

## How to Use

First, include the dependencies:

```php
<?php
require __DIR__ . "/vendor/autoload.php";

use Valitov\BacklinkChecker;
```

Then decide which mode to use, for Chromium mode use:

```php
$checker = new BacklinkChecker\ChromeBacklinkChecker();
```

Or if you want a simple mode without JavaScript support use:

```php
$checker = new BacklinkChecker\SimpleBacklinkChecker();
```

Make a scan of the desired URL with a desired pattern
(use the [PCRE pattern syntax](http://php.net/manual/en/reference.pcre.pattern.syntax.php)):

```php
$url = "https://example.com";
$pattern = "@https?://(www\.)?mywebsite\.com.*@";
$scanBacklinks = true;
$scanHotlinks = false;
$makeScreenshot = true;

try {
    $result = $checker->getBacklinks($url, $pattern, $scanBacklinks, $scanHotlinks, $makeScreenshot);
} catch (RuntimeException $e) {
    die("Error: " . $e->getMessage());
}
```

The function `getBacklinks` has the following additional options:

-`$scanBacklinks` - if set to `true`,
  then it scans for the backlinks
  (the text of the `href` attribute of [`<a>`](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/a) tag);
  otherwise scanning is not performed.
-`$scanHotlinks` - if set to `true`, then it scans for the [hotlink](https://simple.wikipedia.org/wiki/Hotlinking) (the
  text of the `src` attribute of [`<img>`](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img) tag);
  otherwise scanning is not performed.
-`$makeScreenshot` - if set to `true`, then we also take a screenshot of the viewport; otherwise screenshot is not
  made.
  This option makes sense only for Chromium mode (default viewport size is `800 x 600` px, image format: `JPEG`,
  image quality: `90`, image encoding: `binary`); for simple mode this option is ignored.

Now we should check the `$result`, if the function succeeded:

```php
$response = $result->getResponse();
if ($response->getSuccess()) {
    $links = $result->getBacklinks();
    if (sizeof($links) > 0)
        //Backlinks found
    else {
        //No backlinks found
    }
} else {
    //Error, usually network error, or server error
    die("Error, HTTP Code " . $response->getStatusCode());
}
```

The function `$result->getBacklinks()` returns an array of objects that describe the backlink. Each object supports the
following functions:

-`getBacklink` returns `string`, a backlink - an exact URL that matches the target domain;
-`getTag` returns `string`, the tag that is used for the backlink, can be `a` or `img`;
-`getTarget` returns `string`, contents of
  [`target`](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/A#attr-target) attribute of the `href`;
-`getNoFollow` returns `true` if the backlink has [`nofollow`](https://en.wikipedia.org/wiki/Nofollow) attribute;
-`getAnchor` returns `string` - anchor of the link, for example, inner text of `<a>` tag. This text is returned in a
  plain text format, all HTML tags are stripped.

The `$response` object supports the following functions:

-`getUrl` returns `string`, the URL of that was analyzed
-`getStatusCode` returns `int`, the [HTTP status code](https://en.wikipedia.org/wiki/List_of_HTTP_status_codes), or `0`
  or `-1` if there was a network error.
-`getScreenshot` returns `string`, the screenshot in binary format. If the screenshot was not taken or is not
  available, then the string is empty. If you want to display this screenshot as an image on a web page, then you should
  first save it to disk and use a link to it, or encode it into [base64](https://en.wikipedia.org/wiki/Data_URI_scheme)
  and insert into the web page directly. In this case, you can use a function like:

```php
$base64_image = "data:image/jpeg;base64," . base64_encode($response->getScreenshot());
```

**Note**. If you use function [`json_encode`](http://php.net/manual/en/function.json-encode.php) on an object that
contains the screenshot, then this screenshot will be converted
to [base64](https://en.wikipedia.org/wiki/Data_URI_scheme) format automatically.

## Examples

Examples are available in a dedicated project on [GitHub](https://github.com/rvalitov/backlink-checker-php-demo).
Tests are in `tests` folder.

## System Requirements

PHP 8.0.7+ required with the following extensions:

- [`json`](https://www.php.net/manual/en/json.installation.php)
- [`sockets`](https://www.php.net/manual/en/sockets.installation.php)
