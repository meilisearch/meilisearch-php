<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

final class JsonEncodingException extends \Exception implements ExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
