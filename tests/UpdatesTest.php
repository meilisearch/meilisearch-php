<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

class UpdatesTest extends TestCase
{
    private $index;
    private $documents;
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client('http://localhost:7700', 'masterKey');
        $this->documents = [
            ['id' => 123,  'title' => 'Pride and Prejudice',                    'comment' => 'A great book'],
            ['id' => 456,  'title' => 'Le Petit Prince',                        'comment' => 'A french book'],
            ['id' => 2,    'title' => 'Le Rouge et le Noir',                    'comment' => 'Another french book'],
            ['id' => 1,    'title' => 'Alice In Wonderland',                    'comment' => 'A weird book'],
            ['id' => 1344, 'title' => 'The Hobbit',                             'comment' => 'An awesome book'],
            ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book'],
            ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetOneUpdate()
    {
        list($promise, $response) = $this->seedIndex();

        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'processed');
        $this->assertSame($response['updateId'], $promise['updateId']);
        $this->assertArrayHasKey('type', $response);
        $this->assertIsArray($response['type']);
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('processedAt', $response);
    }

    public function testGetAllUpdates()
    {
        $this->seedIndex();

        $response = $this->index->getAllUpdateStatus();

        $this->assertCount(1, $response);
        $this->assertSame('processed', $response[0]['status']);
        $this->assertArrayHasKey('updateId', $response[0]);
        $this->assertArrayHasKey('type', $response[0]);
        $this->assertIsArray($response[0]['type']);
        $this->assertArrayHasKey('duration', $response[0]);
        $this->assertArrayHasKey('enqueuedAt', $response[0]);
        $this->assertArrayHasKey('processedAt', $response[0]);
    }

    public function testExceptionIfNoUpdateIdWhenGetting()
    {
        $this->expectException(HTTPRequestException::class);
        $this->index->getUpdateStatus(10000);
    }

    private function seedIndex(): array
    {
        $promise = $this->index->updateDocuments($this->documents);
        $response = $this->index->waitForPendingUpdate($promise['updateId']);

        return [$promise, $response];
    }
}
