<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

final class InvalidArgumentException extends \Exception implements ExceptionInterface
{
    public static function emptyArgument(string $argumentName): self
    {
        return new self(
            \sprintf('Argument "%s" is empty.', $argumentName),
            400,
            null
        );
    }

    public static function dateIsExpired(\DateTimeInterface $date): self
    {
        return new self(
            \sprintf('DateTime "%s" is expired. The date expiresAt should be in the future.', $date->format('Y-m-d H:i:s')),
            400,
            null
        );
    }
}
