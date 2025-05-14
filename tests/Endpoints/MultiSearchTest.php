<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\FederationOptions;
use Meilisearch\Contracts\MultiSearchFederation;
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
        $this->booksIndex->updateFilterableAttributes(['genre']);
        $task = $this->booksIndex->updateDocuments(self::DOCUMENTS);
        $this->booksIndex->waitForTask($task['taskUid']);

        $this->songsIndex = $this->createEmptyIndex($this->safeIndexName('songs'));
        $this->songsIndex->updateFilterableAttributes(['duration-float']);
        $fileCsv = fopen('./tests/datasets/songs-custom-separator.csv', 'r');
        $documents = fread($fileCsv, filesize('./tests/datasets/songs-custom-separator.csv'));
        fclose($fileCsv);

        $task = $this->songsIndex->addDocumentsCsv($documents, null, '|');
        $this->songsIndex->waitForTask($task['taskUid']);
    }

    public function testSearchQueryData(): void
    {
        $data = (new SearchQuery())->setIndexUid($this->booksIndex->getUid())->setQuery('butler')->setSort(['author:desc']);

        self::assertSame([
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

        self::assertCount(2, $response['results']);

        self::assertArrayHasKey('indexUid', $response['results'][0]);
        self::assertArrayHasKey('hits', $response['results'][0]);
        self::assertArrayHasKey('query', $response['results'][0]);
        self::assertArrayHasKey('limit', $response['results'][0]);
        self::assertArrayHasKey('offset', $response['results'][0]);
        self::assertArrayHasKey('estimatedTotalHits', $response['results'][0]);
        self::assertCount(2, $response['results'][0]['hits']);

        self::assertArrayHasKey('indexUid', $response['results'][1]);
        self::assertArrayHasKey('hits', $response['results'][1]);
        self::assertArrayHasKey('query', $response['results'][1]);
        self::assertArrayHasKey('page', $response['results'][1]);
        self::assertArrayHasKey('hitsPerPage', $response['results'][1]);
        self::assertArrayHasKey('totalHits', $response['results'][1]);
        self::assertArrayHasKey('totalPages', $response['results'][1]);
        self::assertCount(1, $response['results'][1]['hits']);
    }

    public function testFederation(): void
    {
        $response = $this->client->multiSearch([
            (new SearchQuery())->setIndexUid($this->booksIndex->getUid())
                ->setQuery('princ')
                ->setSort(['author:desc']),
            (new SearchQuery())->setIndexUid($this->songsIndex->getUid())
                ->setQuery('be')
                ->setFilter(['duration-float > 3'])
                // By setting the weight to 0.9 this query should appear second
                ->setFederationOptions((new FederationOptions())->setWeight(0.9)),
        ],
            (new MultiSearchFederation())->setLimit(2)->setFacetsByIndex([$this->booksIndex->getUid() => ['genre'], $this->songsIndex->getUid() => ['duration-float']])->setMergeFacets(['maxValuesPerFacet' => 10])
        );

        self::assertArrayHasKey('hits', $response);
        self::assertArrayHasKey('processingTimeMs', $response);
        self::assertArrayHasKey('limit', $response);
        self::assertArrayHasKey('offset', $response);
        self::assertArrayHasKey('estimatedTotalHits', $response);
        self::assertArrayHasKey('facetDistribution', $response);
        self::assertCount(2, $response['hits']);
        self::assertSame(2, $response['limit']);
        self::assertSame(0, $response['offset']);

        self::assertArrayHasKey('id', $response['hits'][0]);
        self::assertArrayHasKey('_federation', $response['hits'][0]);
        self::assertArrayHasKey('indexUid', $response['hits'][0]['_federation']);
        self::assertArrayHasKey('queriesPosition', $response['hits'][0]['_federation']);
        self::assertArrayHasKey('weightedRankingScore', $response['hits'][0]['_federation']);
        self::assertStringStartsWith('books', $response['hits'][0]['_federation']['indexUid']);
        self::assertSame(0, $response['hits'][0]['_federation']['queriesPosition']);

        self::assertArrayHasKey('id', $response['hits'][1]);
        self::assertArrayHasKey('_federation', $response['hits'][1]);
        self::assertArrayHasKey('indexUid', $response['hits'][1]['_federation']);
        self::assertArrayHasKey('queriesPosition', $response['hits'][1]['_federation']);
        self::assertArrayHasKey('weightedRankingScore', $response['hits'][1]['_federation']);
        self::assertStringStartsWith('songs', $response['hits'][1]['_federation']['indexUid']);
        self::assertSame(1, $response['hits'][1]['_federation']['queriesPosition']);
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

        self::assertSame([1, 0.9, [0.9874]], $result['vector']);
        self::assertSame(['comment'], $result['attributesToSearchOn']);
        self::assertTrue($result['showRankingScore']);
        self::assertTrue($result['showRankingScoreDetails']);
    }

    public function testMultiSearchWithDistinctAttribute(): void
    {
        $response = $this->client->multiSearch([
            (new SearchQuery())->setIndexUid($this->booksIndex->getUid())
                ->setFilter(['genre = fantasy']),
            (new SearchQuery())->setIndexUid($this->booksIndex->getUid())
                ->setFilter(['genre = fantasy'])
                ->setDistinct('genre'),
        ]);

        self::assertCount(2, $response['results']);

        self::assertArrayHasKey('hits', $response['results'][0]);
        self::assertCount(2, $response['results'][0]['hits']);
        self::assertSame('fantasy', $response['results'][0]['hits'][0]['genre']);
        self::assertSame('fantasy', $response['results'][0]['hits'][1]['genre']);

        self::assertArrayHasKey('indexUid', $response['results'][1]);
        self::assertArrayHasKey('hits', $response['results'][1]);
        self::assertArrayHasKey('query', $response['results'][1]);
        self::assertCount(1, $response['results'][1]['hits']);
        self::assertSame('fantasy', $response['results'][1]['hits'][0]['genre']);
    }
}
