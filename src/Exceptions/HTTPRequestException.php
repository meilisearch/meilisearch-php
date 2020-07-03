<?php

namespace MeiliSearch\Exceptions;

use Exception;

class HTTPRequestException extends Exception
{
    public $httpStatus = 0;
    public $httpMessage = null;
    public $httpBody = null;

    public function __construct($httpStatus, $httpBody, $previous = null)
    {
        $this->httpBody = $httpBody;
        $this->httpMessage = $this->getMessageFromHttpBody();
        $this->httpStatus = $httpStatus;
        parent::__construct($this->httpMessage, $this->httpStatus, $previous);
    }

    public function __toString()
    {
        $base = 'MeiliSearch HTTPRequestException: Http Status: '.$this->httpStatus;

        if ($this->httpMessage) {
            return $base.' - Message: '.$this->httpMessage;
        }

        return $base;
    }

    /**
     * @return string|null
     */
    public function getMessageFromHttpBody(): ?string
    {
        if (is_array($this->httpBody) && array_key_exists('message', $this->httpBody)) {
            return $this->httpBody['message'];
        }

        return $this->httpBody;
    }
}
