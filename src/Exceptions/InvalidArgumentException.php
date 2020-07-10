<?php

declare(strict_types=1);

namespace MeiliSearch\Exceptions;

use Exception;

class InvalidArgumentException extends Exception
{
    public static function invalidType(string $argumentName, array $validTypes)
    {
        return new self(
            sprintf('Argument "%s" is not a valid type! Please provide an argument that is of type: "%s"', $argumentName, \implode('","', $validTypes)),
            400,
            null
        );
    }

    public static function emptyArgument(string $argumentName)
    {
        return new self(
            sprintf('Argument "%s" is empty.', $argumentName),
            400,
            null
        );
    }
}
