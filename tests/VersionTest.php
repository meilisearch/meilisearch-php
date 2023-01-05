<?php

declare(strict_types=1);

namespace Tests;

use Meilisearch\MeiliSearch;

class VersionTest extends TestCase
{
    public function testQualifiedVersion(): void
    {
        $qualifiedVersion = sprintf('Meilisearch PHP (v%s)', MeiliSearch::VERSION);

        $this->assertEquals(MeiliSearch::qualifiedVersion(), $qualifiedVersion);
    }
}
