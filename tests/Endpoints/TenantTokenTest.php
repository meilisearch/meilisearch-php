<?php

declare(strict_types=1);

namespace Tests\Endpoints;

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
        $this->privateKey = array_reduce($response['results'], function ($carry, $item) {
            if (str_contains($item['description'], 'Default Admin API')) {
                return $item['key'];
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
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame(7, $response->getNbHits());
        $this->assertCount(7, $response->getHits());
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
        $response = $tokenClient->index('tenantTokenDuplicate')->search('');
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
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
    }

    public function testGenerateTenantTokenWithExpiresAt(): void
    {
        $date = new DateTime();
        $tomorrow = $date->modify('+1 day');
        $options = [
            'expiresAt' => $tomorrow,
        ];
        $token = $this->privateClient->generateTenantToken(['*'], $options);
        $tokenClient = new Client($this->host, $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
    }

    public function testGenerateTenantTokenWithEmptySearchRules(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $token = $this->privateClient->generateTenantToken('');
    }

    public function testGenerateTenantTokenWithSearchRulesEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $token = $this->privateClient->generateTenantToken([]);
    }

    public function testGenerateTenantTokenWithBadExpiresAt(): void
    {
        $date = new DateTime();
        $yesterday = $date->modify('-1 day');
        $options = [
            'expiresAt' => $yesterday,
        ];

        $this->expectException(InvalidArgumentException::class);
        $token = $this->privateClient->generateTenantToken(['*'], $options);
    }

    public function testGenerateTenantTokenWithNoApiKey(): void
    {
        $client = new Client($this->host);
        $this->expectException(InvalidArgumentException::class);
        $token = $client->generateTenantToken(['*']);
    }
}
