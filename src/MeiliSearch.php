<?php

declare(strict_types=1);

namespace Meilisearch;

class MeiliSearch
{
    public const VERSION = '0.26.1';

    public static function qualifiedVersion()
    {
        return sprintf('Meilisearch PHP (v%s)', self::VERSION);
    }
}
