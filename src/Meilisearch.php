<?php

declare(strict_types=1);

namespace Meilisearch;

class Meilisearch
{
    public const VERSION = '1.4.2';

    public static function qualifiedVersion()
    {
        return sprintf('Meilisearch PHP (v%s)', self::VERSION);
    }
}
