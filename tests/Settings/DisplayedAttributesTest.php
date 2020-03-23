<?php

use MeiliSearch\Client;
use PHPUnit\Framework\TestCase;

define('__ROOT__', dirname(dirname(__FILE__)));
require_once __ROOT__.'/utils.php';

class DisplayedAttributesTest extends TestCase
{
    private static $client;
    private static $index1;
    private static $index2;
    private static $primary_key;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = new Client('http://localhost:7700', 'masterKey');
        deleteAllIndexes(static::$client);
        static::$primary_key = 'objectID';
        static::$index1 = static::$client->createIndex('uid1');
        static::$index2 = static::$client->createIndex(['uid' => 'uid2', 'primaryKey' => static::$primary_key]);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        deleteAllIndexes(static::$client);
    }

    public function testGetDefaultDisplayedAttributes()
    {
        $res = static::$index1->getDisplayedAttributes();
        $this->assertIsArray($res);
        $this->assertEmpty($res);
        $res = static::$index2->getDisplayedAttributes();
        $this->assertIsArray($res);
        $this->assertEquals([static::$primary_key], $res);
    }

    public function testUpdateDisplayedAttributes()
    {
        $new_da = ['title'];
        $res = static::$index1->updateDisplayedAttributes($new_da);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index1->waitUpdateId($res['updateId']);
        $da = static::$index1->getDisplayedAttributes();
        $this->assertIsArray($da);
        $this->assertEquals($new_da, $da);
    }

    public function testResetDisplayedAttributes()
    {
        $res = static::$index1->resetDisplayedAttributes();
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index1->waitUpdateId($res['updateId']);
        $da = static::$index1->getDisplayedAttributes();
        $this->assertIsArray($da);
    }
}
