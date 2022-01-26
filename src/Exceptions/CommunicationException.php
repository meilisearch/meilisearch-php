<?php

declare(strict_types=1);

namespace MeiliSearch\Exceptions;

use Exception;

class CommunicationException extends Exception
{
    public function __toString()
    {
        return 'Meilisearch CommunicationException: '.$this->getMessage();
    }
}
