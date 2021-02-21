<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class UpdatesTest extends TestCase
{
    private $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetOneUpdate(): void
    {
        [$promise, $response] = $this->seedIndex();

        $this->assertIsArray($response);
        $this->assertSame($response['status'], 'processed');
        $this->assertSame($response['updateId'], $promise['updateId']);
        $this->assertArrayHasKey('type', $response);
        $this->assertIsArray($response['type']);
        $this->assertArrayHasKey('duration', $response);
        $this->assertArrayHasKey('enqueuedAt', $response);
        $this->assertArrayHasKey('processedAt', $response);
    }

    public function testGetAllUpdates(): void
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

    public function testExceptionIfNoUpdateIdWhenGetting(): void
    {
        $this->expectException(ApiException::class);
        $this->index->getUpdateStatus(10000);
    }

    private function seedIndex(): array
    {
        $promise = $this->index->updateDocuments(self::DOCUMENTS);
        $response = $this->index->waitForPendingUpdate($promise['updateId']);

        return [$promise, $response];
    }
}
