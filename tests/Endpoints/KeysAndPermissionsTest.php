<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Client;
use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class KeysAndPermissionsTest extends TestCase
{
    public function testGetKeys(): void
    {
        $response = $this->client->getKeys();

        $this->assertArrayHasKey('private', $response);
        $this->assertArrayHasKey('public', $response);
        $this->assertIsString($response['private']);
        $this->assertNotNull($response['private']);
        $this->assertIsString($response['public']);
        $this->assertNotNull($response['public']);
    }

    public function testSearchingIfPublicKeyProvided(): void
    {
        $this->client->createIndex('index');

        $newClient = new Client(self::HOST, $this->getKeys()['public']);
        $response = $newClient->index('index')->search('test');
        $this->assertArrayHasKey('hits', $response->toArray());
    }

    public function testGetSettingsIfPrivateKeyProvided(): void
    {
        $this->client->createIndex('index');
        $newClient = new Client(self::HOST, $this->getKeys()['private']);
        $response = $newClient->index('index')->getSettings();

        $this->assertEquals(['*'], $response['searchableAttributes']);
    }

    public function testExceptionIfNoMasterKeyProvided(): void
    {
        $newClient = new Client(self::HOST);

        $this->expectException(ApiException::class);
        $newClient->index('index')->search('test');
    }

    public function testExceptionIfBadKeyProvidedToGetSettings(): void
    {
        $this->client->createIndex('index');
        $response = $this->client->index('index')->getSettings();
        $this->assertEquals(['*'], $response['searchableAttributes']);

        $newClient = new Client(self::HOST, 'bad-key');

        $this->expectException(ApiException::class);
        $newClient->index('index')->getSettings();
    }

    public function testExceptionIfBadKeyProvidedToGetKeys(): void
    {
        $this->expectException(ApiException::class);
        $client = new Client(self::HOST, 'bad-key');
        $client->getKeys();
    }

    private function getKeys(): array
    {
        return $this->client->getKeys();
    }
}
