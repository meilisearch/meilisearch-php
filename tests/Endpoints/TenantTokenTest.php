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
    private string $privateKey;
    private Client $privateClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createEmptyIndex('tenantToken');

        $response = $this->client->getKeys();
        $this->privateKey = array_reduce($response, function ($carry, $item) {
            if ($item->getDescription() && str_contains($item->getDescription(), 'Default Admin API')) {
                return $item->getKey();
            }
        });
        $this->privateClient = new Client($this->host, $this->privateKey);
    }

    public function testGenerateTenantTokenWithSearchRulesOnly(): void
    {
        $promise = $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS);
        $this->client->waitForTask($promise['uid']);

        $token = $this->privateClient->generateTenantToken(['*']);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(7, $response->getHits());
    }

    public function testGenerateTenantTokenWithSearchRulesAsObject(): void
    {
        $promise = $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS);
        $this->client->waitForTask($promise['uid']);

        $token = $this->privateClient->generateTenantToken((object) ['*' => (object) []]);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(7, $response->getHits());
    }

    public function testGenerateTenantTokenWithFilter(): void
    {
        $promise = $this->client->index('tenantToken')->addDocuments(self::DOCUMENTS);
        $this->client->waitForTask($promise['uid']);
        $promiseFromFilter = $this->client->index('tenantToken')->updateFilterableAttributes([
            'id',
        ]);
        $this->client->waitForTask($promiseFromFilter['uid']);

        $token = $this->privateClient->generateTenantToken((object) ['tenantToken' => (object) ['filter' => 'id > 10']]);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertCount(4, $response->getHits());
    }

    public function testGenerateTenantTokenWithSearchRulesOnOneIndex(): void
    {
        $this->createEmptyIndex('tenantTokenDuplicate');

        $token = $this->privateClient->generateTenantToken(['tenantToken']);
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

        $token = $this->client->generateTenantToken(['*'], $options);
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

        $token = $this->privateClient->generateTenantToken(['*'], $options);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
    }

    public function testGenerateTenantTokenWithSearchRulesEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->privateClient->generateTenantToken([]);
    }

    public function testGenerateTenantTokenWithBadExpiresAt(): void
    {
        /* @phpstan-ignore-next-line */
        $date = new DateTime();
        $yesterday = $date->modify('-1 day');
        $options = [
            'expiresAt' => $yesterday,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->privateClient->generateTenantToken(['*'], $options);
    }

    public function testGenerateTenantTokenWithNoApiKey(): void
    {
        $client = new Client($this->host);

        $this->expectException(InvalidArgumentException::class);
        $client->generateTenantToken(['*']);
    }

    public function testGenerateTenantTokenWithEmptyApiKey(): void
    {
        $client = new Client($this->host);

        $this->expectException(InvalidArgumentException::class);
        $client->generateTenantToken(['*'], ['apiKey' => '']);
    }
}
