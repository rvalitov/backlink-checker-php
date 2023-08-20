<?php

namespace Valitov\BacklinkChecker;

use GuzzleHttp;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class SimpleBacklinkChecker
 * Checks the backlinks using a simple web engine without JavaScript support.
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov https://github.com/rvalitov
 */
class SimpleBacklinkChecker extends BacklinkChecker
{
    /**
     * @param string $url
     * @param boolean $makeScreenshot
     * @return HttpResponse
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function browsePage(string $url, bool $makeScreenshot): HttpResponse
    {
        $client = new GuzzleHttp\Client();
        try {
            /** @noinspection SpellCheckingInspection */
            $response = $client->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 ' .
                        '(KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36'
                ]
            ]);
        } catch (ClientException|ServerException|BadResponseException $e) {
            $response = $e->getResponse();
            return new HttpResponse(
                $url,
                $response->getStatusCode(),
                $response->getHeaders(),
                $response->getBody()->getContents(),
                false,
                ""
            );
        } catch (GuzzleException $e) {
            throw new RuntimeException($e->getMessage());
        }
        return new HttpResponse(
            $url,
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody()->getContents(),
            true,
            ""
        );
    }
}
