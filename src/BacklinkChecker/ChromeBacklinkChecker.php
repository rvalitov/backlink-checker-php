<?php

namespace Valitov\BacklinkChecker;

use InvalidArgumentException;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use RuntimeException;
use UnexpectedValueException;

/**
 * Class ChromeBacklinkChecker
 * Perform checks with Chrome headless browser.
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov https://github.com/rvalitov
 */
class ChromeBacklinkChecker extends BacklinkChecker
{
    /**
     * Retrieves the HTML content of the page and makes a screenshot.
     * @param string $url - the URL of the page
     * @param boolean $makeScreenshot - if true, a screenshot will be made
     * @return HttpResponse - the response object
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @todo Add support for response headers
     * @noinspection PhpUndefinedFieldInspection
     */
    protected function browsePage(string $url, bool $makeScreenshot): HttpResponse
    {
        $puppeteer = new Puppeteer();
        /**
         * On Debian the headless mode does not start with the following error:
         * No usable sandbox! Update your kernel or see
         * https://chromium.googlesource.com/chromium/src/+/master/docs/linux_suid_sandbox_development.md for more
         * information on developing with the SUID sandbox.
         * This issue is described here https://github.com/Googlechrome/puppeteer/issues/290
         * The solution is to add the following args.
         */
        /** @noinspection SpellCheckingInspection */
        $browser = $puppeteer->launch([
            "args" => [
                "--no-sandbox",
                "--disable-setuid-sandbox",
            ],
        ]);

        $page = $browser->newPage();
        /** @noinspection SpellCheckingInspection */
        /**
         * We must use `networkidle2` option to wait that the web page is complete. The networkidle option is now
         * deprecated.
         */
        $response = $page->goto($url, [
            "waitUntil" => "networkidle2",
        ]);
        if ($makeScreenshot) {
            /**
             * Make a screenshot. We use a base64 encoding here, otherwise we may have problems, because of
             * converting a Buffer into string - approach that should work but does not:
             * $image->toString('binary');
             * We have encoding problems. If we pass base64, then we get a string and can process it normally.
             */
            $image = $page->screenshot([
                "type" => "jpeg",
                "quality" => 90,
                "encoding" => "base64",
            ]);
            if (!$image || !is_string($image)) {
                $browser->close();
                throw new UnexpectedValueException("Failed to make a screenshot");
            }
            $image = base64_decode($image);
        } else {
            $image = "";
        }

        if (!$response) {
            $browser->close();
            return new HttpResponse($url, 500, [[]], "Failed to fetch data", false, "");
        }
        // @phpstan-ignore property.notFound
        if (!$response->ok) {
            $browser->close();
            return new HttpResponse($url, intval($response->status()), [[]], $response->text(), false, $image);
        }

        // @phpstan-ignore method.staticCall
        $data = $page->evaluate(JsFunction::createWithBody('return document.documentElement.outerHTML'));
        $browser->close();
        if (!is_string($data)) {
            throw new UnexpectedValueException("Failed to get the page content");
        }
        return new HttpResponse($url, intval($response->status()), [[]], $data, true, $image);
    }
}
