<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class SearchNestedFieldsTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName('nestedIndex'));
        $task = $this->index->updateDocuments(self::NESTED_DOCUMENTS);
        $this->index->waitForTask($task['taskUid']);
    }

    public function testBasicSearchOnNestedFields(): void
    {
        $response = $this->index->search('An awesome');

        self::assertArrayHasKey('hits', $response->toArray());
        self::assertCount(1, $response->getHits());

        $response = $this->index->search('An awesome', [], [
            'raw' => true,
        ]);

        self::assertArrayHasKey('hits', $response);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertSame(5, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithMultiplesResultsOnNestedFields(): void
    {
        $response = $this->index->search('book');

        self::assertArrayHasKey('hits', $response->toArray());
        self::assertCount(6, $response->getHits());

        $response = $this->index->search('book', [], [
            'raw' => true,
        ]);

        self::assertArrayHasKey('hits', $response);
        self::assertSame(6, $response['estimatedTotalHits']);
        self::assertSame(1, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithOptions(): void
    {
        $response = $this->index->search('book', ['limit' => 1]);

        self::assertCount(1, $response->getHits());

        $response = $this->index->search('book', ['limit' => 1], [
            'raw' => true,
        ]);

        self::assertCount(1, $response['hits']);
        self::assertSame(1, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithSearchableAtributes(): void
    {
        $response = $this->index->updateSearchableAttributes(['title', 'info.comment']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('An awesome');

        self::assertArrayHasKey('hits', $response->toArray());
        self::assertCount(1, $response->getHits());

        $response = $this->index->search('An awesome', [], [
            'raw' => true,
        ]);

        self::assertArrayHasKey('hits', $response);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertSame(5, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithSortableAtributes(): void
    {
        $response = $this->index->updateSortableAttributes(['info.reviewNb']);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('An awesome');

        self::assertArrayHasKey('hits', $response->toArray());
        self::assertCount(1, $response->getHits());

        $response = $this->index->search('An awesome', [
            'sort' => ['info.reviewNb:desc'],
        ], [
            'raw' => true,
        ]);

        self::assertArrayHasKey('hits', $response);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertSame(5, $response['hits'][0]['id']);
    }

    public function testSearchOnNestedFieldWithSortableAtributesAndSearchableAttributes(): void
    {
        $response = $this->index->updateSettings([
            'searchableAttributes' => ['title', 'info.comment'],
            'sortableAttributes' => ['info.reviewNb'],
        ]);
        $this->index->waitForTask($response['taskUid']);

        $response = $this->index->search('An awesome');

        self::assertArrayHasKey('hits', $response->toArray());
        self::assertCount(1, $response->getHits());

        $response = $this->index->search('An awesome', [
            'sort' => ['info.reviewNb:desc'],
        ], [
            'raw' => true,
        ]);

        self::assertArrayHasKey('hits', $response);
        self::assertSame(1, $response['estimatedTotalHits']);
        self::assertSame(5, $response['hits'][0]['id']);
    }
}
