<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use MeiliSearch\Index;
use PHPUnit\Framework\TestCase;

require_once 'utils.php';

class ClientTest extends TestCase
{
    private static $client;
    private static $index1;
    private static $name1;
    private static $uid1;
    private static $index2;
    private static $name2;
    private static $uid2;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$client = new Client('http://localhost:7700', 'apiKey');
        deleteAllIndexes(static::$client);
        static::$name1 = 'Index 1';
        static::$name2 = 'Index 2';
        static::$uid2 = 'uid2';
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

    public function testCreateIndexWithOnlyName()
    {
        static::$index1 = static::$client->createIndex(static::$name1);
        static::$uid1 = static::$index1->getUid();
        $this->assertInstanceOf(Index::class, static::$index1);
        $this->assertSame(static::$name1, static::$index1->getName());
        $this->assertFalse(empty(static::$index1->getUid()));
    }

    public function testCreateIndexWithNameAndUid()
    {
        static::$index2 = static::$client->createIndex(static::$name2, static::$uid2);
        $this->assertInstanceOf(Index::class, static::$index2);
        $this->assertSame(static::$name1, static::$index1->getName());
        $this->assertSame(static::$uid2, static::$index2->getUid());
    }

    public function testGetAllIndexes()
    {
        $res = static::$client->getAllIndexes();
        $this->assertIsArray($res);
        $this->assertCount(2, $res);
        $names = array_map(function ($elem) {
            return $elem['name'];
        }, $res);
        $this->assertContains(static::$name1, $names);
        $this->assertContains(static::$name2, $names);
    }

    public function testShowIndex()
    {
        $res = static::$client->showIndex(static::$uid2);
        $this->assertIsArray($res);
        $this->assertSame($res['name'], static::$name2);
        $this->assertSame($res['uid'], static::$uid2);
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
        $this->assertSame(static::$name1, static::$index1->getName());
        $this->assertSame(static::$uid1, static::$index1->getUid());
    }

    public function testExceptionIfUidTakenWhenCreating()
    {
        $this->expectException(HTTPRequestException::class);
        static::$client->createIndex('nope', static::$uid1);
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
