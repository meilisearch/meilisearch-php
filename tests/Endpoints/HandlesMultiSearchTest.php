<?php

declare(strict_types=1);

namespace Endpoints;

use Meilisearch\Contracts\MultiSearchFederation;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class HandlesMultiSearchTest extends TestCase
{
    private Indexes $firstIndex;
    private Indexes $secondIndex;

    protected function setUp(): void
    {
        parent::setUp();
        $this->firstIndex = $this->createEmptyIndex($this->safeIndexName());
        $this->secondIndex = $this->createEmptyIndex($this->safeIndexName());
    }

    public function test_passing_empty_multi_search_federation_does_not_cause_exception(): void
    {
        $this->expectNotToPerformAssertions();

        $this->client->multiSearch([
            (new SearchQuery())->setIndexUid($this->firstIndex->getUid()),
            (new SearchQuery())->setIndexUid($this->secondIndex->getUid())
        ],
            (new MultiSearchFederation())
        );
    }
}
