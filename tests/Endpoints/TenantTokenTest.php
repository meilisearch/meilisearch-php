<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Client;
use Meilisearch\Contracts\CreateKeyQuery;
use Meilisearch\Contracts\Key;
use Meilisearch\Contracts\KeyAction;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\InvalidArgumentException;
use Tests\TestCase;

final class TenantTokenTest extends TestCase
{
    private Key $key;
    private string $privateKey;
    private Client $privateClient;
    private string $indexName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->indexName = $this->safeIndexName('tenantToken');
        $this->createEmptyIndex($this->indexName);

        $this->key = $this->client->createKey(new CreateKeyQuery(
            description: 'tenant token key',
            actions: [KeyAction::Any],
            indexes: ['tenant*'],
            expiresAt: new \DateTimeImmutable('2055-10-02T00:00:00Z'),
        ));

        $this->privateKey = $this->key->getKey();
        $this->privateClient = new Client($this->host, $this->privateKey);
    }

    protected function tearDown(): void
    {
        $this->client->deleteKey($this->privateKey);

        parent::tearDown();
    }

    public function testGenerateTenantTokenWithSearchRulesOnly(): void
    {
        $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS)->wait();

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), ['*']);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        self::assertCount(7, $response->getHits());
    }

    public function testGenerateTenantTokenWithSearchRulesAsObject(): void
    {
        $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS)->wait();

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), (object) ['*' => (object) []]);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        self::assertCount(7, $response->getHits());
    }

    public function testGenerateTenantTokenWithFilter(): void
    {
        $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS)->wait();
        $this->client->index('tenantToken')->updateFilterableAttributes([
            'id',
        ])->wait();

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), (object) ['tenantToken' => (object) ['filter' => 'id > 10']]);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        self::assertCount(4, $response->getHits());
    }

    public function testGenerateTenantTokenWithSearchRulesOnOneIndex(): void
    {
        $indexName = $this->safeIndexName('tenantTokenDuplicate');
        $this->createEmptyIndex($indexName);

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), [$this->indexName]);
        $tokenClient = new Client($this->host, $token);
        $tokenClient->index($this->indexName)->search('');

        $this->expectException(ApiException::class);

        $tokenClient->index($indexName)->search('');
    }

    public function testGenerateTenantTokenWithApiKey(): void
    {
        $options = [
            'apiKey' => $this->privateKey,
        ];

        $token = $this->client->generateTenantToken($this->key->getUid(), ['*'], $options);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index($this->indexName)->search('');

        self::assertSame([], $response->getHits());
    }

    public function testGenerateTenantTokenWithExpiresAt(): void
    {
        $date = new \DateTime();
        $tomorrow = $date->modify('+1 day');
        $options = [
            'expiresAt' => $tomorrow,
        ];

        $token = $this->privateClient->generateTenantToken($this->key->getUid(), ['*'], $options);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index($this->indexName)->search('');

        self::assertSame([], $response->getHits());
    }

    public function testGenerateTenantTokenWithSearchRulesEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->privateClient->generateTenantToken($this->key->getUid(), []);
    }

    public function testGenerateTenantTokenWithBadExpiresAt(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $date = new \DateTime();
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
