<?php

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

class UpdatesTest extends TestCase
{
    private $index;

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
        $promise = $this->index->updateDocuments(self::DOCUMENTS);
        $response = $this->index->waitForPendingUpdate($promise['updateId']);

        return [$promise, $response];
    }
}
