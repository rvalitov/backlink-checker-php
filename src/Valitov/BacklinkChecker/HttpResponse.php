<?php

namespace Valitov\BacklinkChecker;

use JsonSerializable;


/**
 * Class HttpResponse
 * A response from web request
 * @package Valitov\BacklinkChecker
 * @author Ramil Valitov ramilvalitov@gmail.com
 */
class HttpResponse implements JsonSerializable
{
    /**
     * @var string URL
     */
    protected $url;

    /**
     * @var int HTTP status code
     */
    protected $statusCode;

    /**
     * @var \string[][] headers of the response
     */
    protected $headers;

    /**
     * @var string response body
     */
    protected $response;

    /**
     * @var bool true, if request succeeded
     */
    protected $success;

    /**
     * @var string screenshot in binary format
     */
    protected $screenshot;

    /**
     * HttpResponse constructor.
     * @param string $url
     * @param int $statusCode
     * @param string[][] $headers
     * @param string $response
     * @param boolean $success
     * @param string $screenshot
     */
    public function __construct($url, $statusCode, $headers, $response, $success, $screenshot)
    {
        $this->url = $url;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->response = $response;
        $this->success = boolval($success);
        $this->screenshot = $screenshot;
    }

    /**
     * @return string URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int HTTP status code
     */
    public function getStatusCode()
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
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string response body
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return boolean true, if request succeeded
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return string screenshot in binary format
     */
    public function getScreenshot()
    {
        return $this->screenshot;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $data = get_object_vars($this);
        if (strlen($data["screenshot"]) > 0)
            $data["screenshot"] = "data:image/jpeg;base64," . base64_encode($data["screenshot"]);
        return $data;
    }
}