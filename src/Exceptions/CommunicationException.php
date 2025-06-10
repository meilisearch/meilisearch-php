<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

class CommunicationException extends \Exception implements ExceptionInterface
{
    public function __toString(): string
    {
        return 'Meilisearch CommunicationException: '.$this->getMessage();
    }
}
