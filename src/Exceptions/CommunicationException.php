<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

/**
 * @final since 1.3.0
 */
class CommunicationException extends \Exception
{
    public function __toString()
    {
        return 'Meilisearch CommunicationException: '.$this->getMessage();
    }
}
