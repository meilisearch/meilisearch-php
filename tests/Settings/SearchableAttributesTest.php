<?php

use MeiliSearch\Client;
use Tests\TestCase;

class SearchableAttributesTest extends TestCase
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

    public function testGetDefaultSearchableAttributes()
    {
        $res = static::$index1->getSearchableAttributes();
        $this->assertIsArray($res);
        $this->assertEmpty($res);
        $res = static::$index2->getSearchableAttributes();
        $this->assertIsArray($res);
        $this->assertEquals([static::$primary_key], $res);
    }

    public function testUpdateSearchableAttributes()
    {
        $new_sa = [
            'title',
            'description',
        ];
        $res = static::$index1->updateSearchableAttributes($new_sa);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index1->waitForPendingUpdate($res['updateId']);
        $sa = static::$index1->getSearchableAttributes();
        $this->assertIsArray($sa);
        $this->assertEquals($new_sa, $sa);
    }

    public function testResetSearchableAttributes()
    {
        $res = static::$index1->resetSearchableAttributes();
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index1->waitForPendingUpdate($res['updateId']);
        $sa = static::$index1->getSearchableAttributes();
        $this->assertIsArray($sa);
    }
}
