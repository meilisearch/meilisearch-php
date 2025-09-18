<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

class TimeOutException extends \Exception implements ExceptionInterface
{
    public $code;
    public $message;

    public function __construct(?string $message = null, ?int $code = null, ?\Throwable $previous = null)
    {
        $this->message = $message ?? 'Request timed out';
        $this->code = $code ?? 408;

        parent::__construct($this->message, $this->code, $previous);
    }

    public function __toString()
    {
        $base = 'Meilisearch TimeOutException: Code: '.$this->code;
        if ('' !== $this->message) {
            return $base.' - Message: '.$this->message;
        } else {
            return $base;
        }
    }
}
