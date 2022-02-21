<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Client;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class KeysAndPermissionsTest extends TestCase
{
    public function testGetRawKeysAlwaysReturnsArray(): void
    {
        /* @phpstan-ignore-next-line */
        $this->assertIsArray($this->client->getRawKeys());
    }

    public function testGetKeysAlwaysReturnsArray(): void
    {
        /* @phpstan-ignore-next-line */
        $this->assertIsArray($this->client->getKeys());
    }

    public function testGetKeysDefault(): void
    {
        $response = $this->client->getKeys();

        $this->assertCount(2, $response);
        $this->assertIsArray($response[0]->getActions());
        $this->assertIsArray($response[0]->getIndexes());
        $this->assertNull($response[0]->getExpiresAt());
        $this->assertNotNull($response[0]->getCreatedAt());
        $this->assertNotNull($response[0]->getUpdatedAt());
    }

    public function testGetRawKeysDefault(): void
    {
        $response = $this->client->getRawKeys();

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
        $response = $this->client->getKey($key->getKey());

        $this->assertNotNull($response->getKey());
        $this->assertIsArray($response->getActions());
        $this->assertIsArray($response->getIndexes());
        $this->assertNull($response->getExpiresAt());
        $this->assertNotNull($response->getCreatedAt());
        $this->assertNotNull($response->getUpdatedAt());

        $this->client->deleteKey($key->getKey());
    }

    public function testExceptionIfKeyDoesntExist(): void
    {
        $this->expectException(ApiException::class);
        $this->client->getKey('No existing key');
    }

    public function testCreateKey(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);

        $this->assertNotNull($key->getKey());
        $this->assertIsArray($key->getActions());
        $this->assertSame($key->getActions(), self::INFO_KEY['actions']);
        $this->assertIsArray($key->getIndexes());
        $this->assertSame($key->getIndexes(), self::INFO_KEY['indexes']);
        $this->assertNull($key->getExpiresAt());
        $this->assertNotNull($key->getCreatedAt());
        $this->assertNotNull($key->getUpdatedAt());

        $this->client->deleteKey($key->getKey());
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

        $this->assertNotNull($response->getKey());
        $this->assertNotNull($response->getDescription());
        $this->assertSame($response->getDescription(), 'test create');
        $this->assertIsArray($response->getActions());
        $this->assertSame($response->getActions(), ['search']);
        $this->assertIsArray($response->getIndexes());
        $this->assertSame($response->getIndexes(), ['index']);
        $this->assertNotNull($response->getExpiresAt());
        $this->assertNotNull($response->getCreatedAt());
        $this->assertNotNull($response->getUpdatedAt());

        $this->client->deleteKey($response->getKey());
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
        $response = $this->client->updateKey($key->getKey(), [
            'description' => 'test update',
            'indexes' => ['*'],
            'expiresAt' => date('Y-m-d', strtotime('+1 day')),
        ]);

        $this->assertNotNull($response->getKey());
        $this->assertNotNull($response->getDescription());
        $this->assertSame($response->getDescription(), 'test update');
        $this->assertIsArray($response->getActions());
        $this->assertSame($response->getActions(), self::INFO_KEY['actions']);
        $this->assertIsArray($response->getIndexes());
        $this->assertSame($response->getIndexes(), ['*']);
        $this->assertNotNull($response->getExpiresAt());
        $this->assertNotNull($response->getCreatedAt());
        $this->assertNotNull($response->getUpdatedAt());

        $this->client->deleteKey($response->getKey());
    }

    public function testDeleteKey(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $this->client->deleteKey($key->getKey());
        $this->expectException(ApiException::class);
        $this->client->getKey($key->getKey());
    }

    public function testInextistingDeleteKey(): void
    {
        $this->expectException(ApiException::class);
        $this->client->deleteKey('No existing key');
    }
}
