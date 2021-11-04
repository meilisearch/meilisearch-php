<?php

declare(strict_types=1);

namespace MeiliSearch\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

class ApiException extends Exception
{
    public $httpStatus = 0;
    public $message = null;
    public $errorCode = null;
    public $errorType = null;
    public $errorLink = null;
    public $httpBody = null;

    public function __construct(ResponseInterface $response, $httpBody, $previous = null)
    {
        $this->httpBody = $httpBody;
        $this->httpStatus = $response->getStatusCode();
        $this->message = $this->getMessageFromHttpBody() ?? $response->getReasonPhrase();
        $this->errorCode = $this->getErrorCodeFromHttpBody();
        $this->errorLink = $this->getErrorLinkFromHttpBody();
        $this->errorType = $this->getErrorTypeFromHttpBody();

        parent::__construct($this->message, $this->httpStatus, $previous);
    }

    public function __toString()
    {
        $base = 'MeiliSearch ApiException: Http Status: '.$this->httpStatus;

        if ($this->message) {
            $base .= ' - Message: '.$this->message;
        }

        if ($this->errorCode) {
            $base .= ' - Code: '.$this->errorCode;
        }

        if ($this->errorType) {
            $base .= ' - Type: '.$this->errorType;
        }

        if ($this->errorLink) {
            $base .= ' - Link: '.$this->errorLink;
        }

        return $base;
    }

    private function getMessageFromHttpBody(): ?string
    {
        if (\is_array($this->httpBody) && \array_key_exists('message', $this->httpBody)) {
            return $this->httpBody['message'];
        }

        return null;
    }

    private function getErrorCodeFromHttpBody(): ?string
    {
        if (\is_array($this->httpBody) && \array_key_exists('code', $this->httpBody)) {
            return $this->httpBody['code'];
        }

        return null;
    }

    private function getErrorTypeFromHttpBody(): ?string
    {
        if (\is_array($this->httpBody) && \array_key_exists('type', $this->httpBody)) {
            return $this->httpBody['type'];
        }

        return null;
    }

    private function getErrorLinkFromHttpBody(): ?string
    {
        if (\is_array($this->httpBody) && \array_key_exists('link', $this->httpBody)) {
            return $this->httpBody['link'];
        }

        return null;
    }
}
