<?php

declare(strict_types=1);

namespace MeiliSearch\Exceptions;

class TimeOutException extends \Exception
{
    public $code = 408;
    public $message = 'Request timed out';

    public function __construct($message = null, $code = null, $previous = null)
    {
        if (isset($message)) {
            $this->message = $message;
        }
        if (isset($code)) {
            $this->code = $code;
        }
        parent::__construct($this->message, $this->code, $previous);
    }

    public function __toString()
    {
        $base = 'MeiliSearch TimeOutException: Code: '.$this->code;
        if (isset($this->message)) {
            return $base.' - Message: '.$this->message;
        } else {
            return $base;
        }
    }
}
