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
     * @var string URL
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
     * @param string $url
     * @param int $statusCode
     * @param string[][] $headers
     * @param string $response
     * @param boolean $success
     * @param string $screenshot
     */
    public function __construct(
        string $url,
        int    $statusCode,
        array  $headers,
        string $response,
        bool   $success,
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
     * @return string URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return int HTTP status code
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
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string response body
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @return boolean true, if request succeeded
     * @deprecated use isSuccess() instead
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getSuccess(): bool
    {
        return $this->isSuccess();
    }

    /**
     * @return boolean true, if request succeeded
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string screenshot in binary format
     */
    public function getScreenshot(): string
    {
        return $this->screenshot;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array|null data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): ?array
    {
        $data = get_object_vars($this);
        if (strlen($data["screenshot"]) > 0) {
            $data["screenshot"] = "data:image/jpeg;base64," . base64_encode($data["screenshot"]);
        }
        return $data;
    }
}
