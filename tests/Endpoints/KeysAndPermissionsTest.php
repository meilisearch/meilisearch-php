<?php

namespace Tests\Endpoints;

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

class KeysAndPermissionsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
    }

    public function testGetKeys()
    {
        $response = $this->client->getKeys();

        $this->assertArrayHasKey('private', $response);
        $this->assertArrayHasKey('public', $response);
        $this->assertIsString($response['private']);
        $this->assertNotNull($response['private']);
        $this->assertIsString($response['public']);
        $this->assertNotNull($response['public']);
    }

    public function testSearchingIfPublicKeyProvided()
    {
        $this->client->createIndex('index');

        $newClient = new Client(self::HOST, $this->getKeys()['public']);
        $response = $newClient->getIndex('index')->search('test');
        $this->assertArrayHasKey('hits', $response);
    }

    public function testGetSettingsIfPrivateKeyProvided()
    {
        $this->client->createIndex('index');
        $newClient = new Client(self::HOST, $this->getKeys()['private']);
        $response = $newClient->getIndex('index')->getSettings();

        $this->assertTrue($response['acceptNewFields']);
    }

    public function testExceptionIfNoMasterKeyProvided()
    {
        $newClient = new Client(self::HOST);

        $this->expectException(HTTPRequestException::class);
        $newClient->getIndex('index')->search('test');
    }

    public function testExceptionIfBadKeyProvidedToGetSettings()
    {
        $this->client->createIndex('index');
        $response = $this->client->getIndex('index')->getSettings();
        $this->assertTrue($response['acceptNewFields']);

        $newClient = new Client(self::HOST, 'bad-key');

        $this->expectException(HTTPRequestException::class);
        $newClient->getIndex('index')->getSettings();
    }

    public function testExceptionIfBadKeyProvidedToGetKeys()
    {
        $this->expectException(HTTPRequestException::class);
        $client = new Client(self::HOST, 'bad-key');
        $client->getKeys();
    }

    private function getKeys()
    {
        return $this->client->getKeys();
    }
}
