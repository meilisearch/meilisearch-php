<?php

declare(strict_types=1);

namespace Tests;

use Meilisearch\Meilisearch;

class VersionTest extends TestCase
{
    public function testQualifiedVersion(): void
    {
        $qualifiedVersion = \sprintf('Meilisearch PHP (v%s)', Meilisearch::VERSION);

        self::assertSame(Meilisearch::qualifiedVersion(), $qualifiedVersion);
    }
}
