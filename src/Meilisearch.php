<?php

declare(strict_types=1);

namespace Meilisearch;

class Meilisearch
{
    public const VERSION = 'v1.6.1';

    public static function qualifiedVersion()
    {
        return sprintf('Meilisearch PHP (v%s)', self::VERSION);
    }
}
