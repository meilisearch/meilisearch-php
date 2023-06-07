<?php

declare(strict_types=1);

namespace Meilisearch;

/**
 * @final since 1.3.0
 */
class Meilisearch
{
    public const VERSION = '1.2.1';

    public static function qualifiedVersion()
    {
        return sprintf('Meilisearch PHP (v%s)', self::VERSION);
    }
}
