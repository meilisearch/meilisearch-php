<?php

namespace MeiliSearch\Exceptions;

use function array_key_exists;
use function is_array;

class HTTPRequestException extends \Exception
{
    public $http_status = 0;
    public $http_message = null;
    public $http_body = null;

    public function __construct($http_status, $http_body, $previous = null)
    {
        $this->http_body = $http_body;
        if (!empty($this->http_body)) {
            $this->http_message = is_array($this->http_body) && array_key_exists('message', $this->http_body) ?
                $this->http_body['message'] : $this->http_body;
        }
        $this->http_status = $http_status;
        parent::__construct($this->http_message, $this->http_status, $previous);
    }

    public function __toString()
    {
        $base = 'MeiliSearch HTTPRequestException: Http Status: '.$this->http_status;
        if (isset($this->http_message)) {
            return $base.' - Message: '.$this->http_message;
        } else {
            return $base;
        }
    }
}
