<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Client;
use Meilisearch\Contracts\KeysQuery;
use Meilisearch\Exceptions\ApiException;
use Tests\TestCase;

final class KeysAndPermissionsTest extends TestCase
{
    public function testGetRawKeysAlwaysReturnsArray(): void
    {
        /* @phpstan-ignore-next-line */
        self::assertIsArray($this->client->getRawKeys());
    }

    public function testGetKeysAlwaysReturnsArray(): void
    {
        /* @phpstan-ignore-next-line */
        self::assertIsIterable($this->client->getKeys());
    }

    public function testGetKeysDefault(): void
    {
        $response = $this->client->getKeys();

        self::assertGreaterThan(0, $response->count());
        self::assertIsArray($response[0]->getActions());
        self::assertIsArray($response[0]->getIndexes());
        self::assertNull($response[0]->getExpiresAt());
        self::assertNotNull($response[0]->getCreatedAt());
        self::assertNotNull($response[0]->getUpdatedAt());
    }

    public function testGetRawKeysDefault(): void
    {
        $response = $this->client->getRawKeys();

        self::assertGreaterThan(2, $response['results']);
        self::assertArrayHasKey('actions', $response['results'][0]);
        self::assertArrayHasKey('indexes', $response['results'][0]);
        self::assertArrayHasKey('createdAt', $response['results'][0]);
        self::assertArrayHasKey('expiresAt', $response['results'][0]);
        self::assertArrayHasKey('updatedAt', $response['results'][0]);
    }

    public function testExceptionIfNoMasterKeyProvided(): void
    {
        $newClient = new Client($this->host);

        $this->expectException(ApiException::class);
        $newClient->index('index')->search('test');
    }

    public function testExceptionIfBadKeyProvidedToGetSettings(): void
    {
        $index = $this->safeIndexName();
        $this->createEmptyIndex($index);
        $response = $this->client->index($index)->getSettings();
        self::assertSame(['*'], $response['searchableAttributes']);

        $newClient = new Client($this->host, 'bad-key');

        $this->expectException(ApiException::class);
        $newClient->index($index)->getSettings();
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

        self::assertCount(1, $response);
    }

    public function testGetKeysWithOffset(): void
    {
        $response = $this->client->getKeys((new KeysQuery())->setOffset(100));

        self::assertCount(0, $response);
    }

    public function testGetKey(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $response = $this->client->getKey($key->getKey());

        self::assertNotNull($response->getKey());
        self::assertNull($response->getDescription());
        self::assertIsArray($response->getActions());
        self::assertIsArray($response->getIndexes());
        self::assertNull($response->getExpiresAt());
        self::assertNotNull($response->getCreatedAt());
        self::assertNotNull($response->getUpdatedAt());

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

        self::assertNotNull($key->getKey());
        self::assertNull($key->getDescription());
        self::assertIsArray($key->getActions());
        self::assertSame($key->getActions(), self::INFO_KEY['actions']);
        self::assertIsArray($key->getIndexes());
        self::assertSame($key->getIndexes(), self::INFO_KEY['indexes']);
        self::assertNull($key->getExpiresAt());
        self::assertNotNull($key->getCreatedAt());
        self::assertNotNull($key->getUpdatedAt());

        $this->client->deleteKey($key->getKey());
    }

    public function testCreateKeyWithWildcard(): void
    {
        $key = $this->client->createKey([
            'actions' => ['tasks.*', 'indexes.get'],
            'indexes' => ['*'],
            'expiresAt' => null,
        ]);

        self::assertIsArray($key->getActions());
        self::assertSame($key->getActions(), ['tasks.*', 'indexes.get']);

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

        self::assertNotNull($response->getKey());
        self::assertNotNull($response->getDescription());
        self::assertSame($response->getDescription(), 'test create');
        self::assertIsArray($response->getActions());
        self::assertSame($response->getActions(), ['search']);
        self::assertIsArray($response->getIndexes());
        self::assertSame($response->getIndexes(), ['index']);
        self::assertNotNull($response->getExpiresAt());
        self::assertNotNull($response->getCreatedAt());
        self::assertNotNull($response->getUpdatedAt());

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

        self::assertNotNull($response->getKey());
        self::assertNotNull($response->getDescription());
        self::assertSame($response->getDescription(), 'test create');
        self::assertIsArray($response->getActions());
        self::assertSame($response->getActions(), ['search']);
        self::assertIsArray($response->getIndexes());
        self::assertSame($response->getIndexes(), ['index']);
        self::assertNotNull($response->getExpiresAt());
        self::assertNotNull($response->getCreatedAt());
        self::assertNotNull($response->getUpdatedAt());

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

        self::assertNotNull($response->getKey());
        self::assertNotNull($response->getDescription());
        self::assertSame($response->getDescription(), 'test update');
        self::assertIsArray($response->getActions());
        self::assertSame($response->getActions(), self::INFO_KEY['actions']);
        self::assertIsArray($response->getIndexes());
        self::assertSame($response->getIndexes(), ['index']);
        self::assertNull($response->getExpiresAt());
        self::assertNotNull($response->getCreatedAt());
        self::assertNotNull($response->getUpdatedAt());

        $this->client->deleteKey($response->getKey());
    }

    public function testCreateKeyWithUid(): void
    {
        $key = $this->client->createKey([
            'uid' => 'acab6d06-5385-47a2-a534-1ed4fd7f6402',
            'actions' => ['*'],
            'indexes' => ['*'],
            'expiresAt' => null,
        ]);

        self::assertNotNull($key->getKey());
        self::assertSame('acab6d06-5385-47a2-a534-1ed4fd7f6402', $key->getUid());

        $this->client->deleteKey($key->getKey());
    }

    public function testUpdateKeyWithExpInDate(): void
    {
        $key = $this->client->createKey(self::INFO_KEY);
        $response = $this->client->updateKey($key->getKey(), [
            'description' => 'test update',
            'indexes' => ['*'],
            'expiresAt' => date_create_from_format('Y-m-d', date('Y-m-d', strtotime('+1 day'))),
        ]);

        self::assertNotNull($response->getKey());
        self::assertNotNull($response->getDescription());
        self::assertSame($response->getDescription(), 'test update');
        self::assertIsArray($response->getActions());
        self::assertSame($response->getActions(), self::INFO_KEY['actions']);
        self::assertIsArray($response->getIndexes());
        self::assertSame($response->getIndexes(), ['index']);
        self::assertNull($response->getExpiresAt());
        self::assertNotNull($response->getCreatedAt());
        self::assertNotNull($response->getUpdatedAt());

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

        $newClient = new Client('https://localhost:7700', null, $httpClient);

        $response = $newClient->getKeys();

        self::assertCount(3, $response);

        for ($i = 0; $i < 3; ++$i) {
            self::assertNotNull($response[$i]->getExpiresAt(), (string) $i);
            self::assertNotNull($response[$i]->getCreatedAt(), (string) $i);
            self::assertNotNull($response[$i]->getUpdatedAt(), (string) $i);
        }
    }
}
