<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

class CommunicationException extends \Exception implements ExceptionInterface
{
    public function __toString()
    {
        return 'Meilisearch CommunicationException: '.$this->getMessage();
    }
}
