<?php

namespace Valitov\BacklinkChecker;

use GuzzleHttp;

/**
 * Class SimpleBacklinkChecker
 * Checks the backlinks using a simple web engine without JavaScript support.
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov ramilvalitov@gmail.com
 */
class SimpleBacklinkChecker extends BacklinkChecker
{
    /**
     * @param string $url
     * @param boolean $makeScreenshot
     * @return HttpResponse
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function browsePage($url, $makeScreenshot)
    {
        if (!is_string($url))
            throw new \InvalidArgumentException("Argument must be string");
        $client = new GuzzleHttp\Client();
        try {
            /** @noinspection SpellCheckingInspection */
            $response = $client->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36'
                ]
            ]);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            return new HttpResponse($url, $response->getStatusCode(), $response->getHeaders(), $response->getBody()->getContents(), false, "");
        } catch (GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            return new HttpResponse($url, $response->getStatusCode(), $response->getHeaders(), $response->getBody()->getContents(), false, "");
        } catch (GuzzleHttp\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            return new HttpResponse($url, $response->getStatusCode(), $response->getHeaders(), $response->getBody()->getContents(), false, "");
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            throw new \RuntimeException($e->getMessage());
        }
        return new HttpResponse($url, $response->getStatusCode(), $response->getHeaders(), $response->getBody()->getContents(), true, "");
    }
}