<?php

namespace Valitov\BacklinkChecker;

use JsonSerializable;

/**
 * Class HttpResponse
 * A response from web request
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov https://github.com/rvalitov
 */
class HttpResponse implements JsonSerializable
{
    /**
     * @var string URL of the request
     */
    protected string $url;

    /**
     * @var int HTTP status code
     */
    protected int $statusCode;

    /**
     * @var string[][] headers of the response
     */
    protected array $headers;

    /**
     * @var string response body
     */
    protected string $response;

    /**
     * @var bool true, if request succeeded
     */
    protected bool $success;

    /**
     * @var string screenshot in binary format
     */
    protected string $screenshot;

    /**
     * HttpResponse constructor.
     * @param string $url URL of the request
     * @param int $statusCode HTTP status code
     * @param string[][] $headers headers of the response
     * @param string $response response body
     * @param boolean $success true, if request succeeded
     * @param string $screenshot screenshot in binary format
     */
    public function __construct(
        string $url,
        int $statusCode,
        array $headers,
        string $response,
        bool $success,
        string $screenshot,
    ) {
        $this->url = $url;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->response = $response;
        $this->success = $success;
        $this->screenshot = $screenshot;
    }

    /**
     * Returns the URL of the request
     * @return string URL of the request
     * @psalm-api
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Returns the HTTP status code of the response
     * @return int HTTP status code of the response
     * @psalm-api
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     * @psalm-api
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns the response body
     * @return string response body
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * Checks if the request succeeded
     * @return boolean true, if request succeeded
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Returns screenshot in binary format
     * @return string screenshot in binary format
     * @psalm-api
     */
    public function getScreenshot(): string
    {
        return $this->screenshot;
    }


    /**
     * Function to serialize the object to JSON
     * @return array<mixed> array representation of the object
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);
        if ($data["screenshot"] !== "") {
            /**
             * @phpstan-ignore argument.type
             */
            $data["screenshot"] = "data:image/jpeg;base64," . base64_encode($data["screenshot"]);
        }
        return $data;
    }
}
