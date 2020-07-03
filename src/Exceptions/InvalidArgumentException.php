<?php

declare(strict_types=1);

namespace MeiliSearch\Exceptions;

use Exception;

class InvalidArgumentException extends Exception
{
    private $argumentName;

    public function __construct(string $argumentName, array $validTypes)
    {
        $this->argumentName = $argumentName;

        parent::__construct(
            \sprintf('Argument "%s" is not a valid type! Please provide an argument that is of type: "%s"', $this->argumentName, \implode('","', $validTypes)),
            400,
            null
        );
    }

    public function getArgumentName()
    {
        return $this->argumentName;
    }
}
