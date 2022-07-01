<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Client;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;
use MeiliSearch\Contracts\KeysQuery;

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
        $this->assertIsIterable($this->client->getKeys());
    }

    public function testGetKeysDefault(): void
    {
        $response = $this->client->getKeys();

        $this->assertGreaterThan(0, $response->count());
        $this->assertIsArray($response[0]->getActions());
        $this->assertIsArray($response[0]->getIndexes());
        $this->assertNull($response[0]->getExpiresAt());
        $this->assertNotNull($response[0]->getCreatedAt());
        $this->assertNotNull($response[0]->getUpdatedAt());
    }

    public function testGetRawKeysDefault(): void
    {
        $response = $this->client->getRawKeys();

        $this->assertGreaterThan(2, $response['results']);
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

    public function testGetKeysWithLimit(): void
    {
        $response = $this->client->getKeys((new KeysQuery())->setLimit(1));

        $this->assertCount(1, $response);
    }

    public function testGetKey(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $response = $this->client->getKey($key->getKey());

        $this->assertNotNull($response->getKey());
        $this->assertNull($response->getDescription());
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
        $this->assertNull($key->getDescription());
        $this->assertIsArray($key->getActions());
        $this->assertSame($key->getActions(), self::INFO_KEY['actions']);
        $this->assertIsArray($key->getIndexes());
        $this->assertSame($key->getIndexes(), self::INFO_KEY['indexes']);
        $this->assertNull($key->getExpiresAt());
        $this->assertNotNull($key->getCreatedAt());
        $this->assertNotNull($key->getUpdatedAt());

        $this->client->deleteKey($key->getKey());
    }

    public function testCreateKeyWithExpInString(): void
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

    public function testCreateKeyWithExpInDate(): void
    {
        $key = [
            'description' => 'test create',
            'actions' => ['search'],
            'indexes' => ['index'],
            'expiresAt' => date_create_from_format('Y-m-d', date('Y-m-d', strtotime('+1 day'))),
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

    public function testUpdateKeyWithExpInString(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $response = $this->client->updateKey($key->getKey(), [
            'description' => 'test update',
        ]);

        $this->assertNotNull($response->getKey());
        $this->assertNotNull($response->getDescription());
        $this->assertSame($response->getDescription(), 'test update');
        $this->assertIsArray($response->getActions());
        $this->assertSame($response->getActions(), self::INFO_KEY['actions']);
        $this->assertIsArray($response->getIndexes());
        $this->assertSame($response->getIndexes(), ['index']);
        $this->assertNull($response->getExpiresAt());
        $this->assertNotNull($response->getCreatedAt());
        $this->assertNotNull($response->getUpdatedAt());

        $this->client->deleteKey($response->getKey());
    }

    public function testUpdateKeyWithExpInDate(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $response = $this->client->updateKey($key->getKey(), [
            'description' => 'test update',
            'indexes' => ['*'],
            'expiresAt' => date_create_from_format('Y-m-d', date('Y-m-d', strtotime('+1 day'))),
        ]);

        $this->assertNotNull($response->getKey());
        $this->assertNotNull($response->getDescription());
        $this->assertSame($response->getDescription(), 'test update');
        $this->assertIsArray($response->getActions());
        $this->assertSame($response->getActions(), self::INFO_KEY['actions']);
        $this->assertIsArray($response->getIndexes());
        $this->assertSame($response->getIndexes(), ['index']);
        $this->assertNull($response->getExpiresAt());
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

    public function testDateParsing(): void
    {
        $httpClient = $this->createHttpClientMock(200, '
        {
            "results": [
                {
                  "description": "test_key_1",
                  "name": null,
                  "uid": "e3091e84-928c-44b5-8a61-7e5b15cd5009",
                  "key": "z1ySBsnp002e8bc6a31b794a95d623333be1fe4fd2d7eacdeaf7baf2c439866723e659ee",
                  "actions": ["*"],
                  "indexes": ["*"],
                  "expiresAt": "2023-06-14T10:34:03Z",
                  "createdAt": "2022-06-14T10:34:03Z",
                  "updatedAt": "2022-06-14T10:34:03Z"
                },
                {
                  "description": "test_key_2",
                  "name": null,
                  "uid": "85f12b91-cf39-493a-9364-7d8b85b87798",
                  "key": "z2ySBsnp002e8bc6a31b794a95d623333be1fe4fd2d7eacdeaf7baf2c439866723e659ee",
                  "actions": ["*"],
                  "indexes": ["*"],
                  "expiresAt": "2023-06-14T10:34:03.629Z",
                  "createdAt": "2022-06-14T10:34:03.627Z",
                  "updatedAt": "2022-06-14T10:34:03.627Z"
                },
                {
                  "description": "test_key_3",
                  "name": "test_key_3",
                  "uid": "6dffa3ee-b98f-4218-827a-7a062f23ebf5",
                  "key": "z3ySBsnp002e8bc6a31b794a95d623333be1fe4fd2d7eacdeaf7baf2c439866723e659ee",
                  "actions": ["*"],
                  "indexes": ["*"],
                  "expiresAt": "2023-06-14T10:34:03.629690014Z",
                  "createdAt": "2022-06-14T10:34:03.627606639Z",
                  "updatedAt": "2022-06-14T10:34:03.627606639Z"
                }
              ],
              "limit": 10,
              "offset": 0,
              "total": 3
          }
        ');

        $newClient = new \MeiliSearch\Client('https://localhost:7700', null, $httpClient);

        $response = $newClient->getKeys();

        $this->assertCount(3, $response);

        for ($i = 0; $i < 3; ++$i) {
            $this->assertNotNull($response[$i]->getExpiresAt(), (string) $i);
            $this->assertNotNull($response[$i]->getCreatedAt(), (string) $i);
            $this->assertNotNull($response[$i]->getUpdatedAt(), (string) $i);
        }
    }
}
