<?php

declare(strict_types=1);

namespace MeiliSearch;

class MeiliSearch
{
    public const VERSION = '0.23.0';

    public static function qualifiedVersion()
    {
        return sprintf('Meilisearch PHP (v%s)', MeiliSearch::VERSION);
    }
}
