<?php

use Tests\TestCase;
use MeiliSearch\Client;

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
        static::$client->deleteAllIndexes();static::$primary_key = 'objectID';
        static::$index1 = static::$client->createIndex('uid1');
        static::$index2 = static::$client->createIndex(['uid' => 'uid2', 'primaryKey' => static::$primary_key]);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$client->deleteAllIndexes();}

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
        static::$index1->waitForPendingUpdate($res['updateId']);
        $da = static::$index1->getDisplayedAttributes();
        $this->assertIsArray($da);
        $this->assertEquals($new_da, $da);
    }

    public function testResetDisplayedAttributes()
    {
        $res = static::$index1->resetDisplayedAttributes();
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index1->waitForPendingUpdate($res['updateId']);
        $da = static::$index1->getDisplayedAttributes();
        $this->assertIsArray($da);
    }
}
