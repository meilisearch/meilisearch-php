<?php

use MeiliSearch\Client;
use PHPUnit\Framework\TestCase;

define('__ROOT__', dirname(dirname(__FILE__)));
require_once __ROOT__.'/utils.php';

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
        usleep(10 * 1000);
        $this->assertFalse(static::$index->getAcceptNewFields());
    }
}
