<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use MeiliSearch\Index;
use Tests\TestCase;

class ClientTest extends TestCase
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client('http://localhost:7700', 'masterKey');
    }

    // INDEXES

    public function testGetAllIndexesWhenEmpty()
    {
        $res = $this->client->getAllIndexes();
        $this->assertIsArray($res);
        $this->assertEmpty($res);
    }

    public function testCreateIndexWithOnlyUid()
    {
        $index = $this->client->createIndex('index');
        $this->assertInstanceOf(Index::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testCreateIndexWithUidAndPrimaryKey()
    {
        $index = $this->client->createIndex([
            'uid' => 'index',
            'primaryKey' => 'ObjectId',
        ]);
        $this->assertInstanceOf(Index::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testGetAllIndexes()
    {
        $indexA = 'indexA';
        $indexB = 'indexB';
        $this->client->createIndex($indexA);
        $this->client->createIndex($indexB);

        $response = $this->client->getAllIndexes();

        $this->assertIsArray($response);
        $this->assertCount(2, $response);

        $uids = array_column($response, 'uid');

        $this->assertContains($indexA, $uids);
        $this->assertContains($indexB, $uids);
    }

    public function testShowIndex()
    {
        $index = 'index';
        $this->client->createIndex([
            'uid' => $index,
            'primaryKey' => 'objectID',
        ]);

        $response = $this->client->showIndex($index);

        $this->assertIsArray($response);
        $this->assertSame('objectID', $response['primaryKey']);
        $this->assertSame($index, $response['uid']);
    }

    public function testDeleteIndex()
    {
        $this->client->createIndex('index');

        $response = $this->client->getAllIndexes();
        $this->assertCount(1, $response);

        $response = $this->client->deleteIndex('index');

        $this->assertEmpty($response);
        $response = $this->client->getAllIndexes();

        $this->assertCount(0, $response);
    }

    public function testGetIndex()
    {
        $this->client->createIndex('index');

        $index = $this->client->getIndex('index');
        $this->assertInstanceOf(Index::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testExceptionIfUidTakenWhenCreating()
    {
        $this->client->createIndex('index');

        $this->expectException(HTTPRequestException::class);

        $this->client->createIndex('index');
    }

    public function testExceptionIfNoUidWhenCreating()
    {
        $this->expectException(HTTPRequestException::class);
        $this->client->createIndex(['primaryKey' => 'id']);
    }

    public function testExceptionIfNoIndexWhenShowing()
    {
        $this->expectException(HTTPRequestException::class);
        $this->client->showIndex('a-non-existing-index');
    }

    public function testExceptionIfNoIndexWhenDeleting()
    {
        $this->expectException(HTTPRequestException::class);
        $this->client->deleteIndex('a-non-existing-index');
    }

    // HEALTH

    public function testHealth()
    {
        $res = $this->client->health();
        $this->assertEmpty($res);
    }

    // STATS

    public function testVersion()
    {
        $res = $this->client->version();
        $this->assertArrayHasKey('commitSha', $res);
        $this->assertArrayHasKey('buildDate', $res);
        $this->assertArrayHasKey('pkgVersion', $res);
    }

    public function testSysInfo()
    {
        $res = $this->client->sysInfo();
        $this->assertArrayHasKey('memoryUsage', $res);
        $this->assertArrayHasKey('processorUsage', $res);
        $this->assertArrayHasKey('global', $res);
        $this->assertArrayHasKey('process', $res);
        $this->assertIsNotString($res['processorUsage'][0]);
    }

    public function testPrettySysInfo()
    {
        $res = $this->client->prettySysInfo();
        $this->assertArrayHasKey('memoryUsage', $res);
        $this->assertArrayHasKey('processorUsage', $res);
        $this->assertArrayHasKey('global', $res);
        $this->assertArrayHasKey('process', $res);
        $this->assertIsString($res['processorUsage'][0]);
    }

    public function testStats()
    {
        $res = $this->client->stats();
        $this->assertArrayHasKey('databaseSize', $res);
        $this->assertArrayHasKey('lastUpdate', $res);
        $this->assertArrayHasKey('indexes', $res);
    }

    protected function tearDown(): void
    {
        $this->client->deleteAllIndexes();
        parent::tearDown();
    }
}
