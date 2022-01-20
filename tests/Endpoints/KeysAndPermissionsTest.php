<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Client;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class KeysAndPermissionsTest extends TestCase
{
    public function testGetKeysDefault(): void
    {
        $response = $this->client->getKeys();

        $this->assertIsArray($response);
        $this->assertCount(2, $response['results']);
        $this->assertArrayHasKey('actions', $response['results'][0]);
        $this->assertArrayHasKey('indexes', $response['results'][0]);
        $this->assertArrayHasKey('createdAt', $response['results'][0]);
        $this->assertArrayHasKey('expiresAt', $response['results'][0]);
        $this->assertArrayHasKey('updatedAt', $response['results'][0]);
    }

    public function testExceptionIfNoMasterKeyProvided(): void
    {
        $newClient = new Client($this->host);

        $this->expectException(ApiException::class);
        $newClient->index('index')->search('test');
    }

    public function testExceptionIfBadKeyProvidedToGetSettings(): void
    {
        $this->createEmptyIndex('index');
        $response = $this->client->index('index')->getSettings();
        $this->assertEquals(['*'], $response['searchableAttributes']);

        $newClient = new Client($this->host, 'bad-key');

        $this->expectException(ApiException::class);
        $newClient->index('index')->getSettings();
    }

    public function testExceptionIfBadKeyProvidedToGetKeys(): void
    {
        $this->expectException(ApiException::class);
        $client = new Client($this->host, 'bad-key');
        $client->getKeys();
    }

    public function testGetKey(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $response = $this->client->getKey($key['key']);

        $this->assertArrayHasKey('key', $response);
        $this->assertArrayHasKey('actions', $response);
        $this->assertIsArray($response['actions']);
        $this->assertIsArray($response['indexes']);
        $this->assertArrayHasKey('indexes', $response);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertArrayHasKey('expiresAt', $response);
        $this->assertArrayHasKey('updatedAt', $response);

        $this->client->deleteKey($key['key']);
    }

    public function testExceptionIfKeyDoesntExist(): void
    {
        $this->expectException(ApiException::class);
        $this->client->getKey('No existing key');
    }

    public function testCreateKey(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);

        $this->assertArrayHasKey('key', $key);
        $this->assertArrayHasKey('actions', $key);
        $this->assertIsArray($key['actions']);
        $this->assertSame($key['actions'], self::INFO_KEY['actions']);
        $this->assertIsArray($key['indexes']);
        $this->assertArrayHasKey('indexes', $key);
        $this->assertSame($key['indexes'], self::INFO_KEY['indexes']);
        $this->assertArrayHasKey('createdAt', $key);
        $this->assertNotNull($key['createdAt']);
        $this->assertArrayHasKey('expiresAt', $key);
        $this->assertNull($key['expiresAt']);
        $this->assertArrayHasKey('updatedAt', $key);
        $this->assertNotNull($key['updatedAt']);

        $this->client->deleteKey($key['key']);
    }

    public function testCreateKeyWithOptions(): void
    {
        $key = [
            'description' => 'test create',
            'actions' => ['search'],
            'indexes' => ['index'],
            'expiresAt' => date('Y-m-d', strtotime('+1 day')),
        ];
        $response = $this->client->createKey($key);

        $this->assertArrayHasKey('key', $response);
        $this->assertArrayHasKey('description', $response);
        $this->assertSame($response['description'], 'test create');
        $this->assertArrayHasKey('actions', $response);
        $this->assertIsArray($response['actions']);
        $this->assertSame($response['actions'], ['search']);
        $this->assertIsArray($response['indexes']);
        $this->assertArrayHasKey('indexes', $response);
        $this->assertSame($response['indexes'], ['index']);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertNotNull($response['createdAt']);
        $this->assertArrayHasKey('expiresAt', $response);
        $this->assertNotNull($response['expiresAt']);
        $this->assertArrayHasKey('updatedAt', $response);
        $this->assertNotNull($response['updatedAt']);

        $this->client->deleteKey($response['key']);
    }

    public function testCreateKeyWithoutActions(): void
    {
        $this->expectException(ApiException::class);
        $this->client->createKey([
            'description' => 'test create',
            'indexes' => ['index'],
            'expiresAt' => null,
        ]);
    }

    public function testUpdateKey(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $response = $this->client->updateKey($key['key'], [
            'description' => 'test update',
            'indexes' => ['*'],
            'expiresAt' => date('Y-m-d', strtotime('+1 day')),
        ]);

        $this->assertArrayHasKey('key', $response);
        $this->assertArrayHasKey('description', $response);
        $this->assertSame($response['description'], 'test update');
        $this->assertArrayHasKey('actions', $response);
        $this->assertIsArray($response['actions']);
        $this->assertSame($response['actions'], self::INFO_KEY['actions']);
        $this->assertIsArray($response['indexes']);
        $this->assertArrayHasKey('indexes', $response);
        $this->assertSame($response['indexes'], ['*']);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertNotNull($response['createdAt']);
        $this->assertArrayHasKey('expiresAt', $response);
        $this->assertNotNull($response['expiresAt']);
        $this->assertArrayHasKey('updatedAt', $response);
        $this->assertNotNull($response['updatedAt']);

        $this->client->deleteKey($response['key']);
    }

    public function testDeleteKey(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $this->client->deleteKey($key['key']);
        $this->expectException(ApiException::class);
        $this->client->getKey($key['key']);
    }

    public function testInextistingDeleteKey(): void
    {
        $this->expectException(ApiException::class);
        $this->client->deleteKey('No existing key');
    }
}
