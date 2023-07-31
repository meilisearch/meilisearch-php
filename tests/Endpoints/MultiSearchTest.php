<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class MultiSearchTest extends TestCase
{
    private Indexes $booksIndex;
    private Indexes $songsIndex;

    protected function setUp(): void
    {
        parent::setUp();
        $this->booksIndex = $this->createEmptyIndex($this->safeIndexName('books'));
        $this->booksIndex->updateSortableAttributes(['author']);
        $promise = $this->booksIndex->updateDocuments(self::DOCUMENTS);
        $this->booksIndex->waitForTask($promise['taskUid']);

        $this->songsIndex = $this->createEmptyIndex($this->safeIndexName('songs'));
        $this->songsIndex->updateFilterableAttributes(['duration-float']);
        $fileCsv = fopen('./tests/datasets/songs-custom-separator.csv', 'r');
        $documents = fread($fileCsv, filesize('./tests/datasets/songs-custom-separator.csv'));
        fclose($fileCsv);

        $promise = $this->songsIndex->addDocumentsCsv($documents, null, '|');
        $this->songsIndex->waitForTask($promise['taskUid']);
    }

    public function testSearchQueryData(): void
    {
        $data = (new SearchQuery())->setIndexUid($this->booksIndex->getUid())->setQuery('butler')->setSort(['author:desc']);

        $this->assertEquals([
            'indexUid' => $this->booksIndex->getUid(),
            'q' => 'butler',
            'sort' => ['author:desc'],
        ], $data->toArray());
    }

    public function testMultiSearch(): void
    {
        $response = $this->client->multiSearch([
            (new SearchQuery())->setIndexUid($this->booksIndex->getUid())
                ->setQuery('princ')
                ->setSort(['author:desc']),
            (new SearchQuery())->setIndexUid($this->songsIndex->getUid())
                ->setQuery('be')
                ->setHitsPerPage(4)
                ->setFilter(['duration-float > 3']),
        ]);

        $this->assertCount(2, $response['results']);

        $this->assertArrayHasKey('indexUid', $response['results'][0]);
        $this->assertArrayHasKey('hits', $response['results'][0]);
        $this->assertArrayHasKey('query', $response['results'][0]);
        $this->assertArrayHasKey('limit', $response['results'][0]);
        $this->assertArrayHasKey('offset', $response['results'][0]);
        $this->assertArrayHasKey('estimatedTotalHits', $response['results'][0]);
        $this->assertCount(2, $response['results'][0]['hits']);

        $this->assertArrayHasKey('indexUid', $response['results'][0]);
        $this->assertArrayHasKey('hits', $response['results'][1]);
        $this->assertArrayHasKey('query', $response['results'][1]);
        $this->assertArrayHasKey('page', $response['results'][1]);
        $this->assertArrayHasKey('hitsPerPage', $response['results'][1]);
        $this->assertArrayHasKey('totalHits', $response['results'][1]);
        $this->assertArrayHasKey('totalPages', $response['results'][1]);
        $this->assertCount(1, $response['results'][1]['hits']);
    }

    public function testSupportedQueryParams(): void
    {
        $query = (new SearchQuery())
            ->setIndexUid($this->booksIndex->getUid())
            ->setVector([1, 0.9, [0.9874]])
            ->setAttributesToSearchOn(['comment'])
            ->setShowRankingScore(true)
            ->setShowRankingScoreDetails(true);

        $result = $query->toArray();

        $this->assertEquals([1, 0.9, [0.9874]], $result['vector']);
        $this->assertEquals(['comment'], $result['attributesToSearchOn']);
        $this->assertEquals(true, $result['showRankingScore']);
        $this->assertEquals(true, $result['showRankingScoreDetails']);
    }
}
