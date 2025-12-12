<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

final class TimeOutException extends \Exception implements \Stringable, ExceptionInterface
{
    public function __construct(?string $message = null, ?int $code = null, ?\Throwable $previous = null)
    {
        parent::__construct($message ?? 'Request timed out', $code ?? 408, $previous);
    }

    public function __toString(): string
    {
        $base = 'Meilisearch TimeOutException: Code: '.$this->code;
        if ('' !== $this->message) {
            return $base.' - Message: '.$this->message;
        }

        return $base;
    }
}
