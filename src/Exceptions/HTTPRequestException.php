<?php

namespace MeiliSearch\Exceptions;

use function array_key_exists;
use function is_array;

class HTTPRequestException extends \Exception
{
    public $httpStatus = 0;
    public $httpMessage = null;
    public $httpBody = null;

    public function __construct($httpStatus, $httpBody, $previous = null)
    {
        $this->httpBody = $httpBody;
        if (!empty($this->httpBody)) {
            $this->httpMessage = is_array($this->httpBody) && array_key_exists('message', $this->httpBody) ?
                $this->httpBody['message'] : $this->httpBody;
        }
        $this->httpStatus = $httpStatus;
        parent::__construct($this->httpMessage, $this->httpStatus, $previous);
    }

    public function __toString()
    {
        $base = 'MeiliSearch HTTPRequestException: Http Status: '.$this->httpStatus;
        if (isset($this->httpMessage)) {
            return $base.' - Message: '.$this->httpMessage;
        } else {
            return $base;
        }
    }
}
