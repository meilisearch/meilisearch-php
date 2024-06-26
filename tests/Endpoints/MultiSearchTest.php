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
        $this->booksIndex->updateFilterableAttributes(['genre']);
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
                ->setDistinct('genre'),
        ]);

        self::assertArrayHasKey('hits', $response['results'][0]);
        self::assertCount(4, $response['results'][0]['hits']);

        $genresSeen = [];

        foreach ($response['results'][0]['hits'] as $_ => $hit) {
            $genre = $hit['genre'];
            self::assertFalse(isset($genresSeen[$genre]));
            $genresSeen[$genre] = true;
        }
    }
}
