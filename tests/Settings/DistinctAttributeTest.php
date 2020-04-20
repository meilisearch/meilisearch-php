<?php

use MeiliSearch\Client;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__FILE__)).'/utils.php';

class DistinctAttributeTest extends TestCase
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

    public function testGetDefaultDistinctAttribute()
    {
        $res = static::$index->getDistinctAttribute();
        $this->assertNull($res);
    }

    public function testUpdateDistinctAttribute()
    {
        $new_da = 'description';
        $res = static::$index->updateDistinctAttribute($new_da);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index->waitForPendingUpdate($res['updateId']);
        $da = static::$index->getDistinctAttribute();
        $this->assertEquals($new_da, $da);
    }

    public function testResetDistinctAttribute()
    {
        $res = static::$index->resetDistinctAttribute();
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index->waitForPendingUpdate($res['updateId']);
        $this->assertNull(static::$index->getDistinctAttribute());
    }
}
