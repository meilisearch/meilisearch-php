<?php

declare(strict_types=1);

namespace MeiliSearch\Exceptions;

use Exception;

final class FailedJsonEncodingException extends Exception
{
    public function __construct($message = '')
    {
        parent::__construct($message);
    }
}
