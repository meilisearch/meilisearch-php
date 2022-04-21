<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class SearchTestNestedFields extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex('nestedIndex');
        $promise = $this->index->updateDocuments(self::NESTED_DOCUMENTS);
        $this->index->waitForTask($promise['uid']);
    }

    public function testBasicSearchOnNestedFields(): void
    {
        $response = $this->index->search('An awesome');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(1, $response->getHits());

        $response = $this->index->search('An awesome', [], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertSame(1, $response['nbHits']);
        $this->assertSame(5, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithMultiplesResultsOnNestedFields(): void
    {
        $response = $this->index->search('book');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(6, $response->getHits());

        $response = $this->index->search('book', [], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertSame(6, $response['nbHits']);
        $this->assertSame(4, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithOptions(): void
    {
        $response = $this->index->search('book', ['limit' => 1]);

        $this->assertCount(1, $response->getHits());

        $response = $this->index->search('book', ['limit' => 1], [
            'raw' => true,
        ]);

        $this->assertCount(1, $response['hits']);
        $this->assertSame(4, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithSearchableAtributes(): void
    {
        $response = $this->index->updateSearchableAttributes(['title', 'info.comment']);
        $this->index->waitForTask($response['uid']);

        $response = $this->index->search('An awesome');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(1, $response->getHits());

        $response = $this->index->search('An awesome', [], [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertSame(1, $response['nbHits']);
        $this->assertSame(5, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithSortableAtributes(): void
    {
        $response = $this->index->updateSortableAttributes(['info.reviewNb']);
        $this->index->waitForTask($response['uid']);

        $response = $this->index->search('An awesome');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(1, $response->getHits());

        $response = $this->index->search('An awesome', [
            'sort' => ['info.reviewNb:desc'],
        ],
        [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertSame(1, $response['nbHits']);
        $this->assertSame(5, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithSortableAtributesAndSearchableAttributes(): void
    {
        $response = $this->index->updateSettings([
            'searchableAttributes' => ['title', 'info.comment'],
            'sortableAttributes' => ['info.reviewNb'],
        ]);
        $this->index->waitForTask($response['uid']);

        $response = $this->index->search('An awesome');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(1, $response->getHits());

        $response = $this->index->search('An awesome', [
            'sort' => ['info.reviewNb:desc'],
        ],
        [
            'raw' => true,
        ]);

        $this->assertArrayHasKey('hits', $response);
        $this->assertSame(1, $response['nbHits']);
        $this->assertSame(5, $response['hits'][0]['id']);
    }
}
