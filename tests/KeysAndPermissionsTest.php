<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

class KeysAndPermissionsTest extends TestCase
{
    private static $client;
    private static $index;
    private static $uid;
    private static $public_key;
    private static $private_key;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = new Client('http://localhost:7700', 'masterKey');
        static::$uid = 'uid';
        deleteAllIndexes(static::$client);
        static::$index = static::$client->createIndex(static::$uid);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        deleteAllIndexes(static::$client);
    }

    public function testGetKeys()
    {
        $res = static::$client->getKeys();
        $this->assertArrayHasKey('private', $res);
        $this->assertArrayHasKey('public', $res);
        static::$public_key = $res['public'];
        static::$private_key = $res['private'];
    }

    public function testSearchIfPublicKeyProvided()
    {
        $new_client = new Client('http://localhost:7700', static::$public_key);
        $res = $new_client->getIndex(static::$uid)->search('test');
        $this->assertArrayHasKey('hits', $res);
    }

    public function testGetSettingsIfPrivateKeyProvided()
    {
        $new_client = new Client('http://localhost:7700', static::$private_key);
        $res = $new_client->getIndex(static::$uid)->getSettings();
        $this->assertTrue($res['acceptNewFields']);
    }

    public function testExceptionIfNoMasterKeyProvided()
    {
        $this->expectException(HTTPRequestException::class);
        $new_client = new Client('http://localhost:7700');
        $new_client->getIndex(static::$uid)->search('test');
    }

    public function testExceptionIfBadKeyProvidedToGetSettings()
    {
        $this->expectException(HTTPRequestException::class);
        $new_client = new Client('http://localhost:7700', static::$public_key);
        $new_client->getIndex(static::$uid)->getSettings();
    }

    public function testExceptionIfBadKeyProvidedToGetKeys()
    {
        $this->expectException(HTTPRequestException::class);
        $client = new Client('http://localhost:7700', static::$private_key);
        $client->getKeys();
    }
}
