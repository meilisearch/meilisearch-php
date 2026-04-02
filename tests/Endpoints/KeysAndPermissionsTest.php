<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Client;
use Meilisearch\Contracts\CreateKeyQuery;
use Meilisearch\Contracts\Key;
use Meilisearch\Contracts\KeyAction;
use Meilisearch\Contracts\KeysQuery;
use Meilisearch\Contracts\UpdateKeyQuery;
use Meilisearch\Exceptions\ApiException;
use Tests\TestCase;

final class KeysAndPermissionsTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach ($this->client->getKeys() as $key) {
            $this->client->deleteKey($key->getUid());
        }

        parent::tearDown();
    }

    public function testCreate(): void
    {
        $key = $this->client->createKey(new CreateKeyQuery(
            actions: [KeyAction::Any],
            indexes: ['*'],
        ));

        self::assertSame([KeyAction::Any], $key->getActions());
        self::assertSame(['*'], $key->getIndexes());
        self::assertNull($key->getName());
        self::assertNull($key->getDescription());
        self::assertNull($key->getExpiresAt());
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key->getUid());
        self::assertNotSame('', $key->getKey());
        self::assertEqualsWithDelta(microtime(true), (float) $key->getCreatedAt()->format('U.u'), 2);
        self::assertEqualsWithDelta(microtime(true), (float) $key->getUpdatedAt()->format('U.u'), 2);
    }

    public function testCreateWithFullInfo(): void
    {
        $key = $this->client->createKey(new CreateKeyQuery(
            actions: [KeyAction::TasksAny, KeyAction::IndexesGet],
            indexes: ['movies*'],
            name: 'task_manager',
            description: 'manages tasks',
            uid: 'a6524df1-fe31-4019-8578-0e0ee2302104',
            expiresAt: new \DateTimeImmutable('tomorrow 08:00:00'),
        ));

        self::assertSame([KeyAction::TasksAny, KeyAction::IndexesGet], $key->getActions());
        self::assertSame(['movies*'], $key->getIndexes());
        self::assertSame('task_manager', $key->getName());
        self::assertSame('manages tasks', $key->getDescription());
        self::assertEquals(new \DateTimeImmutable('tomorrow 08:00:00'), $key->getExpiresAt());
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key->getUid());
        self::assertNotSame('', $key->getKey());
        self::assertEqualsWithDelta(microtime(true), (float) $key->getCreatedAt()->format('U.u'), 2);
        self::assertEqualsWithDelta(microtime(true), (float) $key->getUpdatedAt()->format('U.u'), 2);
    }

    public function testUpdate(): void
    {
        $this->createKey('a1338fe4-2768-4710-a12b-be914aa68da7');

        $updatedKey = $this->client->updateKey(new UpdateKeyQuery('a1338fe4-2768-4710-a12b-be914aa68da7', 'task_manager', 'manages tasks'));

        self::assertSame('task_manager', $updatedKey->getName());
        self::assertSame('manages tasks', $updatedKey->getDescription());

        $removedDataKey = $this->client->updateKey(new UpdateKeyQuery($updatedKey->getKey(), null, null));

        self::assertNull($removedDataKey->getName());
        self::assertNull($removedDataKey->getDescription());
    }

    public function testDeleteKey(): void
    {
        $key = $this->client->createKey(new CreateKeyQuery(
            actions: [KeyAction::Any],
            indexes: ['*'],
        ));

        $this->client->deleteKey($key->getKey());

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API key `'.$key->getKey().'` not found.');

        $this->client->getKey($key->getKey());
    }

    public function testDeleteUnexistingKey(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API key `No existing key` not found.');

        $this->client->deleteKey('No existing key');
    }

    public function testGetKey(): void
    {
        $this->client->createKey(new CreateKeyQuery(
            actions: [KeyAction::TasksAny],
            indexes: ['movies*'],
            name: 'task_manager',
            description: 'manages tasks',
            uid: '030d67d8-0f8c-4ce1-85a7-81a016066317',
            expiresAt: new \DateTimeImmutable('tomorrow 08:00:00'),
        ));

        $key = $this->client->getKey('030d67d8-0f8c-4ce1-85a7-81a016066317');

        self::assertSame([KeyAction::TasksAny], $key->getActions());
        self::assertSame(['movies*'], $key->getIndexes());
        self::assertSame('task_manager', $key->getName());
        self::assertSame('manages tasks', $key->getDescription());
        self::assertEquals(new \DateTimeImmutable('tomorrow 08:00:00'), $key->getExpiresAt());
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key->getUid());
        self::assertNotSame('', $key->getKey());
        self::assertEqualsWithDelta(microtime(true), (float) $key->getCreatedAt()->format('U.u'), 2);
        self::assertEqualsWithDelta(microtime(true), (float) $key->getUpdatedAt()->format('U.u'), 2);
    }

    public function testGetKeyThrowsIfDoesntExist(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API key `No existing key` not found.');

        $this->client->getKey('No existing key');
    }

    public function testGetKeys(): void
    {
        $uids = [
            '77b29717-e3ba-470f-9db6-3cdbc289e77d',
            'b52e8bfd-477c-4f5d-855e-78d1f5dce5c4',
        ];

        foreach ($uids as $uid) {
            $this->createKey($uid);
        }

        $keys = $this->client->getKeys();

        self::assertCount(2, $keys);
        self::assertSame(2, $keys->getTotal());
        self::assertSame(20, $keys->getLimit());
        self::assertSame(0, $keys->getOffset());
        self::assertSame(array_reverse($uids), array_map(static fn (Key $key) => $key->getUid(), $keys->getResults()));
        $array = $keys->toArray();
        self::assertSame(2, $array['total']);
        self::assertSame(20, $array['limit']);
        self::assertSame(0, $array['offset']);
    }

    public function testGetKeysPaginated(): void
    {
        $uids = [
            '77b29717-e3ba-470f-9db6-3cdbc289e77d',
            'b52e8bfd-477c-4f5d-855e-78d1f5dce5c4',
            'c2885f87-92f5-4d82-8619-f6d65c086a5c',
        ];

        foreach ($uids as $uid) {
            $this->createKey($uid);
        }

        $keys = $this->client->getKeys(
            (new KeysQuery())
                ->setLimit(1)
                ->setOffset(1)
        );

        self::assertCount(1, $keys);
        self::assertSame(3, $keys->getTotal());
        self::assertSame(1, $keys->getLimit());
        self::assertSame(1, $keys->getOffset());
        self::assertSame(['b52e8bfd-477c-4f5d-855e-78d1f5dce5c4'], array_map(static fn (Key $key) => $key->getUid(), $keys->getResults()));
        $array = $keys->toArray();
        self::assertSame(3, $array['total']);
        self::assertSame(1, $array['limit']);
        self::assertSame(1, $array['offset']);
    }

    public function testGetRawKeys(): void
    {
        $uids = [
            '77b29717-e3ba-470f-9db6-3cdbc289e77d',
            'b52e8bfd-477c-4f5d-855e-78d1f5dce5c4',
        ];

        foreach ($uids as $uid) {
            $this->createKey($uid);
        }

        $raw = $this->client->getRawKeys();

        self::assertSame(0, $raw['offset']);
        self::assertSame(20, $raw['limit']);
        self::assertSame(2, $raw['total']);
        self::assertCount(2, $raw['results']);
        self::assertSame($uids[0], $raw['results'][1]['uid']);
        self::assertSame($uids[1], $raw['results'][0]['uid']);
    }

    public function testExceptionIfBadKeyProvidedToGetKeys(): void
    {
        $client = new Client($this->host, 'bad-key');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('The provided API key is invalid.');

        $client->getKeys();
    }

    public function testThrowsIfNoMasterKeyProvided(): void
    {
        $client = new Client($this->host);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('The Authorization header is missing. It must use the bearer authorization method.');

        $client->index('index')->search('test');
    }

    public function testThrowsIfBadKeyProvidedToGetSettings(): void
    {
        $index = $this->safeIndexName();
        $this->createEmptyIndex($index);

        $response = $this->client->index($index)->getSettings();

        self::assertSame(['*'], $response['searchableAttributes']);

        $client = new Client($this->host, 'bad-key');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('The provided API key is invalid.');

        $client->index($index)->getSettings();
    }

    private function createKey(string $uid): void
    {
        $this->client->createKey(new CreateKeyQuery(
            actions: [KeyAction::Any],
            indexes: ['*'],
            uid: $uid,
        ));
    }
}
