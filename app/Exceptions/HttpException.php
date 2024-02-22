<?php

namespace App\Exceptions;

use JsonSerializable;
use RuntimeException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class HttpException extends RuntimeException implements JsonSerializable, HttpExceptionInterface
{
    /**
     * The friendly code for this exception.
     *
     * @var string
     */
    protected $errorCode = 'RUNTIME_ERROR';

    /**
     * Default http status code when the exception is rendered.
     *
     * @var int
     */
    protected $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * Default http headers to be used when the exception is rendered.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * A static method to create the instance of exception
     *
     * @return $this
     */
    public static function make()
    {
        return new self();
    }

    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns response headers.
     *
     * @return array Response headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * The friendly error code for this exception.
     *
     * @return void
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Set the message of the exception.
     *
     * @param string $message
     * @return self
     */
    public function withMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set the http status code of the exception.
     *
     * @param int $statusCode
     * @return self
     */
    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Set the http header of the exception.
     *
     * @param array $headers
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode
     *
     * @return void
     */
    public function jsonSerialize()
    {
        return [
            'error_code'  => $this->getErrorCode(),
            'message'     => $this->getMessage(),
            'http_status' => $this->getStatusCode()
        ];
    }
}
