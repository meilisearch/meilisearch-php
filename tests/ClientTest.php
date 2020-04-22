<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use MeiliSearch\Index;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private static $client;
    private static $index1;
    private static $uid1;
    private static $index2;
    private static $uid2;
    private static $primary_key;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = new Client('http://localhost:7700', 'masterKey');
        deleteAllIndexes(static::$client);
        static::$uid1 = 'uid1';
        static::$uid2 = 'uid2';
        static::$primary_key = 'objectID';
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        deleteAllIndexes(static::$client);
    }

    // INDEXES

    public function testGetAllIndexesWhenEmpty()
    {
        $res = static::$client->getAllIndexes();
        $this->assertIsArray($res);
        $this->assertEmpty($res);
    }

    public function testCreateIndexWithOnlyUid()
    {
        static::$index1 = static::$client->createIndex(static::$uid1);
        $this->assertInstanceOf(Index::class, static::$index1);
        $this->assertSame(static::$uid1, static::$index1->getUid());
        $this->assertNull(static::$index1->getPrimaryKey());
    }

    public function testCreateIndexWithUidAndPrimaryKey()
    {
        static::$index2 = static::$client->createIndex([
            'uid' => static::$uid2,
            'primaryKey' => static::$primary_key,
        ]);
        $this->assertInstanceOf(Index::class, static::$index2);
        $this->assertSame(static::$uid2, static::$index2->getUid());
        $this->assertSame(static::$primary_key, static::$index2->getPrimaryKey());
    }

    public function testGetAllIndexes()
    {
        $res = static::$client->getAllIndexes();
        $this->assertIsArray($res);
        $this->assertCount(2, $res);
        $uids = array_map(function ($elem) {
            return $elem['uid'];
        }, $res);
        $this->assertContains(static::$uid1, $uids);
        $this->assertContains(static::$uid2, $uids);
    }

    public function testShowIndex()
    {
        $res = static::$client->showIndex(static::$uid2);
        $this->assertIsArray($res);
        $this->assertSame(static::$primary_key, $res['primaryKey']);
        $this->assertSame(static::$uid2, $res['uid']);
    }

    public function testDeleteIndex()
    {
        $res = static::$client->deleteIndex(static::$uid2);
        $this->assertEmpty($res);
        $res = static::$client->getAllIndexes();
        $this->assertCount(1, $res);
    }

    public function testGetIndex()
    {
        $res = static::$client->getIndex(static::$uid1);
        $this->assertInstanceOf(Index::class, static::$index1);
        $this->assertSame(static::$uid1, static::$index1->getUid());
        $this->assertNull(static::$index1->getPrimaryKey());
    }

    public function testExceptionIfUidTakenWhenCreating()
    {
        $this->expectException(HTTPRequestException::class);
        static::$client->createIndex(static::$uid1);
    }

    public function testExceptionIfNoUidWhenCreating()
    {
        $this->expectException(HTTPRequestException::class);
        static::$client->createIndex(['primaryKey' => 'id']);
    }

    public function testExceptionIfNoIndexWhenShowing()
    {
        $this->expectException(HTTPRequestException::class);
        static::$client->showIndex(static::$uid2);
    }

    public function testExceptionIfNoIndexWhenDeleting()
    {
        $this->expectException(HTTPRequestException::class);
        static::$client->deleteIndex(static::$uid2);
    }

    // HEALTH

    public function testHealth()
    {
        $res = static::$client->health();
        $this->assertEmpty($res);
    }

    // STATS

    public function testVersion()
    {
        $res = static::$client->version();
        $this->assertArrayHasKey('commitSha', $res);
        $this->assertArrayHasKey('buildDate', $res);
        $this->assertArrayHasKey('pkgVersion', $res);
    }

    public function testSysInfo()
    {
        $res = static::$client->sysInfo();
        $this->assertArrayHasKey('memoryUsage', $res);
        $this->assertArrayHasKey('processorUsage', $res);
        $this->assertArrayHasKey('global', $res);
        $this->assertArrayHasKey('process', $res);
        $this->assertIsNotString($res['processorUsage'][0]);
    }

    public function testPrettySysInfo()
    {
        $res = static::$client->prettySysInfo();
        $this->assertArrayHasKey('memoryUsage', $res);
        $this->assertArrayHasKey('processorUsage', $res);
        $this->assertArrayHasKey('global', $res);
        $this->assertArrayHasKey('process', $res);
        $this->assertIsString($res['processorUsage'][0]);
    }

    public function testStats()
    {
        $res = static::$client->stats();
        $this->assertArrayHasKey('databaseSize', $res);
        $this->assertArrayHasKey('lastUpdate', $res);
        $this->assertArrayHasKey('indexes', $res);
    }
}
