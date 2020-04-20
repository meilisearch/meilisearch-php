<?php

use MeiliSearch\Client;
use PHPUnit\Framework\TestCase;

class AcceptNewFieldsTest extends TestCase
{
    private static $client;
    private static $index;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = new Client('http://localhost:7700', 'masterKey');
        deleteAllIndexes(static::$client);
        static::$index = static::$client->createIndex('uid');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        deleteAllIndexes(static::$client);
    }

    public function testGetDefaultAcceptNewFields()
    {
        $res = static::$index->getAcceptNewFields();
        $this->assertTrue($res);
    }

    public function testUpdateAcceptNewFields()
    {
        $res = static::$index->updateAcceptNewFields(false);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index->waitForPendingUpdate($res['updateId']);
        $this->assertFalse(static::$index->getAcceptNewFields());
    }
}
