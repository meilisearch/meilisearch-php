<?php

declare(strict_types=1);

namespace Tests;

use Meilisearch\Client;
use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Endpoints\Indexes;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class TestCase extends BaseTestCase
{
    protected const VECTOR_MOVIES = [
        [
            'title' => 'Shazam!',
            'release_year' => 2019,
            'id' => '287947',
            '_vectors' => ['manual' => [0.8, 0.4, -0.5]],
        ],
        [
            'title' => 'Captain Marvel',
            'release_year' => 2019,
            'id' => '299537',
            '_vectors' => ['manual' => [0.6, 0.8, -0.2]],
        ],
        [
            'title' => 'Escape Room',
            'release_year' => 2019,
            'id' => '522681',
            '_vectors' => ['manual' => [0.1, 0.6, 0.8]],
        ],
        [
            'title' => 'How to Train Your Dragon: The Hidden World',
            'release_year' => 2019,
            'id' => '166428',
            '_vectors' => ['manual' => [0.7, 0.7, -0.4]],
        ],
        [
            'title' => 'All Quiet on the Western Front',
            'release_year' => 1930,
            'id' => '143',
            '_vectors' => ['manual' => [-0.5, 0.3, 0.85]],
        ],
    ];

    protected const DOCUMENTS = [
        ['id' => 123, 'title' => 'Pride and Prejudice', 'comment' => 'A great book', 'genre' => 'romance'],
        ['id' => 456, 'title' => 'Le Petit Prince', 'comment' => 'A french book', 'genre' => 'adventure'],
        ['id' => 2, 'title' => 'Le Rouge et le Noir', 'comment' => 'Another french book', 'genre' => 'romance'],
        ['id' => 1, 'title' => 'Alice In Wonderland', 'comment' => 'A weird book', 'genre' => 'fantasy'],
        ['id' => 1344, 'title' => 'The Hobbit', 'comment' => 'An awesome book', 'genre' => 'romance'],
        ['id' => 4, 'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book', 'genre' => 'fantasy'],
        ['id' => 42, 'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
    ];

    protected const NESTED_DOCUMENTS = [
        ['id' => 1, 'title' => 'Pride and Prejudice', 'info' => ['comment' => 'A great book',   'reviewNb' => 50]],
        ['id' => 2, 'title' => 'Le Petit Prince', 'info' => ['comment' => 'A french book',   'reviewNb' => 600]],
        ['id' => 3, 'title' => 'Le Rouge et le Noir', 'info' => ['comment' => 'Another french book', 'reviewNb' => 700]],
        ['id' => 4, 'title' => 'Alice In Wonderland', 'comment' => 'A weird book', 'info' => ['comment' => 'A weird book', 'reviewNb' => 800]],
        ['id' => 5, 'title' => 'The Hobbit', 'info' => ['comment' => 'An awesome book', 'reviewNb' => 900]],
        ['id' => 6, 'title' => 'Harry Potter and the Half-Blood Prince', 'info' => ['comment' => 'The best book', 'reviewNb' => 1000]],
        ['id' => 7, 'title' => "The Hitchhiker's Guide to the Galaxy"],
    ];

    protected const INFO_KEY = [
        'actions' => ['search'],
        'indexes' => ['index'],
        'expiresAt' => null,
    ];

    protected Client $client;
    protected string $host;
    protected ?string $defaultKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->host = getenv('MEILISEARCH_URL');
        $this->client = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
    }

    protected function tearDown(): void
    {
        $indexes = $this->client->getIndexes((new IndexesQuery())->setLimit(100))->getResults();
        $tasks = [];

        foreach ($indexes as $index) {
            $tasks[] = $index->delete()['taskUid'];
        }

        $this->client->waitForTasks($tasks);
    }

    public function assertFinitePagination(array $response): void
    {
        $currentKeys = array_keys($response);
        $validBody = ['hitsPerPage', 'totalHits', 'totalPages', 'page', 'processingTimeMs', 'query', 'hits'];

        foreach ($validBody as $value) {
            self::assertContains(
                $value,
                $currentKeys,
                'Not a valid finite pagination response, since the "'.$value.'" key is not present in: ['.implode(
                    ', ',
                    $currentKeys
                ).']'
            );
        }
    }

    public function assertEstimatedPagination(array $response): void
    {
        $currentKeys = array_keys($response);
        $validBody = ['offset', 'limit', 'estimatedTotalHits', 'processingTimeMs', 'query', 'hits'];

        foreach ($validBody as $value) {
            self::assertContains(
                $value,
                $currentKeys,
                'Not a valid estimated pagination response, since the "'.$value.'" key is not present in: ['.implode(
                    ', ',
                    $currentKeys
                ).']'
            );
        }
    }

    public function createEmptyIndex($indexName, $options = []): Indexes
    {
        $task = $this->client->createIndex($indexName, $options);
        $this->client->waitForTask($task->getTaskUid());

        return $this->client->getIndex($task->getIndexUid());
    }

    public function safeIndexName(string $indexName = 'index'): string
    {
        return $indexName.'_'.bin2hex(random_bytes(16));
    }

    protected function createHttpClientMock(int $status = 200, string $content = '{', string $contentType = 'application/json')
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects(self::once())
            ->method('__toString')
            ->willReturn($content);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())
            ->method('getStatusCode')
            ->willReturn($status);

        $response->expects(self::any())
            ->method('getHeader')
            ->with('content-type')
            ->willReturn([$contentType]);
        $response->expects(self::once())
            ->method('getBody')
            ->willReturn($stream);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects(self::once())
            ->method('sendRequest')
            ->with(self::isInstanceOf(RequestInterface::class))
            ->willReturn($response);

        return $httpClient;
    }
}
