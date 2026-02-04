<?php

declare(strict_types=1);

namespace Meilisearch;

class Meilisearch
{
    public const VERSION = '2.0.0-beta.4';

    /**
     * @return non-empty-string
     */
    public static function qualifiedVersion(): string
    {
        return \sprintf('Meilisearch PHP (v%s)', self::VERSION);
    }
}
