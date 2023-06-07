<?php

declare(strict_types=1);

namespace Tests;

use Meilisearch\Meilisearch;

final class VersionTest extends TestCase
{
    public function testQualifiedVersion(): void
    {
        $qualifiedVersion = sprintf('Meilisearch PHP (v%s)', Meilisearch::VERSION);

        $this->assertEquals(Meilisearch::qualifiedVersion(), $qualifiedVersion);
    }
}
