<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use DateTimeInterface;
use Tests\TestCase;
use MeiliSearch\Client;
use MeiliSearch\Exceptions\ApiException;
use \Datetime;

final class TenantTokenTest extends TestCase
{
    private string $privateKey;
    private Client $privateClient;

    protected function setUp(): void
    {
        parent::setUp();
        $index = $this->createEmptyIndex('tenantToken');
        $promise = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($promise['uid']);

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
        $token = $this->privateClient->generateTenantToken(searchRules: array('*'));
        $tokenClient = new Client('http://127.0.0.1:7700', $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame(7, $response->getNbHits());
        $this->assertCount(7, $response->getHits());
    }

    public function testGenerateTenantTokenWithApiKey(): void
    {
        $token = $this->client->generateTenantToken(searchRules: array('*'), apiKey: $this->privateKey);
        $tokenClient = new Client('http://127.0.0.1:7700', $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame(7, $response->getNbHits());
        $this->assertCount(7, $response->getHits());
    }

    public function testGenerateTenantTokenWithExpiresAt(): void
    {
        $date = new DateTime();
        $tomorrow = $date->modify('+1 day')->getTimestamp();

        $token = $this->privateClient->generateTenantToken(searchRules: array('*'), expiresAt: $tomorrow);
        $tokenClient = new Client('http://127.0.0.1:7700', $token);
        $response = $tokenClient->index('tenantToken')->search('');

        $this->assertArrayHasKey('hits', $response->toArray());
        $this->assertArrayHasKey('offset', $response->toArray());
        $this->assertArrayHasKey('limit', $response->toArray());
        $this->assertArrayHasKey('processingTimeMs', $response->toArray());
        $this->assertArrayHasKey('query', $response->toArray());
        $this->assertSame(7, $response->getNbHits());
        $this->assertCount(7, $response->getHits());
    }


    public function testGenerateTenantTokenWithoutSearchRules(): void
    {
        $token = $this->privateClient->generateTenantToken(searchRules: '');
        $tokenClient = new Client('http://127.0.0.1:7700', $token);

        $this->expectException(ApiException::class);
        $tokenClient->index('tenantToken')->search('');
    }


    public function testGenerateTenantTokenWithMasterKey(): void
    {
        $token = $this->client->generateTenantToken(array('*'));
        $tokenClient = new Client('http://127.0.0.1:7700', $token);

        $this->expectException(ApiException::class);
        $tokenClient->index('tenantToken')->search('');
    }

    public function testGenerateTenantTokenWithBadExpiresAt(): void
    {
        $date = new DateTime();
        $yesterday = $date->modify('-2 day')->getTimestamp();

        $token = $this->privateClient->generateTenantToken(searchRules: array('*'), expiresAt: $yesterday);
        $tokenClient = new Client('http://127.0.0.1:7700', $token);

        $this->expectException(ApiException::class);
        $tokenClient->index('tenantToken')->search('');
    }
}
