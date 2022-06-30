<?php

declare(strict_types=1);

namespace Tests\Endpoints;

/* @phpstan-ignore-next-line */
use Datetime;
use MeiliSearch\Client;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\InvalidArgumentException;
use Tests\TestCase;

final class TenantTokenTest extends TestCase
{
    private $key;
    private Client $privateClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createEmptyIndex('tenantToken');

        $response = $this->client->getKeys();
        $this->key = $this->client->createKey([
            'description' => 'tenant token key',
            'actions' => ['*'],
            'indexes' => ['*'],
            'expiresAt' => '2055-10-02T00:00:00Z',
        ]);

        $this->privateKey = $this->key->getKey();
        $this->privateClient = new Client($this->host, $this->privateKey);
    }

    protected function tearDown(): void
    {
        $this->client->deleteKey($this->privateKey);
    }

    public function testGenerateTenantTokenWithSearchRulesOnly(): void
    {
        $promise = $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS);
        $this->client->waitForTask($promise['taskUid']);

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), ['*']);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(7, $response->getHits());
    }

    public function testGenerateTenantTokenWithSearchRulesAsObject(): void
    {
        $promise = $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS);
        $this->client->waitForTask($promise['taskUid']);

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), (object) ['*' => (object) []]);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(7, $response->getHits());
    }

    public function testGenerateTenantTokenWithFilter(): void
    {
        $promise = $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS);
        $this->client->waitForTask($promise['taskUid']);
        $promiseFromFilter = $this->client->index('tenantToken')->updateFilterableAttributes([
            'id',
        ]);
        $this->client->waitForTask($promiseFromFilter['taskUid']);

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), (object) ['tenantToken' => (object) ['filter' => 'id > 10']]);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(4, $response->getHits());
    }

    public function testGenerateTenantTokenWithSearchRulesOnOneIndex(): void
    {
        $this->createEmptyIndex('tenantTokenDuplicate');

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), ['tenantToken']);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->expectException(ApiException::class);
        $tokenClient->index('tenantTokenDuplicate')->search('');
    }

    public function testGenerateTenantTokenWithApiKey(): void
    {
        $options = [
            'apiKey' => $this->privateKey,
        ];

        $token = $this->client->generateTenantToken($this->key->getUid(), ['*'], $options);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
    }

    public function testGenerateTenantTokenWithExpiresAt(): void
    {
        /* @phpstan-ignore-next-line */
        $date = new DateTime();
        $tomorrow = $date->modify('+1 day');
        $options = [
            'expiresAt' => $tomorrow,
        ];

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), ['*'], $options);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
    }

    public function testGenerateTenantTokenWithSearchRulesEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->privateClient->generateTenantToken($this->key->getUid(), []);
    }

    public function testGenerateTenantTokenWithBadExpiresAt(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /* @phpstan-ignore-next-line */
        $date = new DateTime();
        $yesterday = $date->modify('-1 day');
        $options = [
            'expiresAt' => $yesterday,
        ];

        $this->privateClient->generateTenantToken($this->key->getUid(), ['*'], $options);
    }

    public function testGenerateTenantTokenWithNoApiKey(): void
    {
        $client = new Client($this->host);

        $this->expectException(InvalidArgumentException::class);
        $client->generateTenantToken($this->key->getUid(), ['*']);
    }

    public function testGenerateTenantTokenWithEmptyApiKey(): void
    {
        $client = new Client($this->host);

        $this->expectException(InvalidArgumentException::class);
        $client->generateTenantToken($this->key->getUid(), ['*'], ['apiKey' => '']);
    }
}
