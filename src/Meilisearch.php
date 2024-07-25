<?php

declare(strict_types=1);

namespace Meilisearch;

class Meilisearch
{
    public const VERSION = '1.9.1';

    public static function qualifiedVersion()
    {
        return \sprintf('Meilisearch PHP (v%s)', self::VERSION);
    }
}
