<?php

declare(strict_types=1);

namespace Tests;

use MeiliSearch\Client;
use MeiliSearch\Endpoints\Indexes;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected const DOCUMENTS = [
        ['id' => 123, 'title' => 'Pride and Prejudice', 'comment' => 'A great book', 'genre' => 'romance'],
        ['id' => 456, 'title' => 'Le Petit Prince', 'comment' => 'A french book', 'genre' => 'adventure'],
        ['id' => 2, 'title' => 'Le Rouge et le Noir', 'comment' => 'Another french book', 'genre' => 'romance'],
        ['id' => 1, 'title' => 'Alice In Wonderland', 'comment' => 'A weird book', 'genre' => 'fantasy'],
        ['id' => 1344, 'title' => 'The Hobbit', 'comment' => 'An awesome book', 'genre' => 'romance'],
        ['id' => 4, 'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book', 'genre' => 'fantasy'],
        ['id' => 42, 'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
    ];

    protected const INFO_KEY = [
        'description' => 'test',
        'actions' => ['search'],
        'indexes' => ['index'],
        'expiresAt' => null,
    ];

    protected Client $client;
    protected $host;
    protected $defaultKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->host = getenv('HOST');
        $this->client = new Client($this->host, getenv('API_KEY'));
    }

    protected function tearDown(): void
    {
        $res = $this->client->deleteAllIndexes();
        $uids = array_map(function ($task) {
            return $task['uid'];
        }, $res);
        $this->client->waitForTasks($uids);
    }

    public function assertIsValidPromise(array $promise): void
    {
        $this->assertIsArray($promise);
        $this->assertArrayHasKey('uid', $promise);
    }

    public function createEmptyIndex($indexName, $options = []): Indexes
    {
        $response = $this->client->createIndex($indexName, $options);
        $this->client->waitForTask($response['uid']);

        return $this->client->getIndex($response['indexUid']);
    }
}
