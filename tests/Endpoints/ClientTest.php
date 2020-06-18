<?php

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\HTTPRequestException;
use MeiliSearch\Index;
use Tests\TestCase;

class ClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
    }

    public function testGetAllIndexesWhenEmpty()
    {
        $response = $this->client->getAllIndexes();

        $this->assertIsArray($response);
        $this->assertEmpty($response);
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
        $index = $this->client->createIndex(
            'index',
            ['primaryKey' => 'ObjectId'],
        );

        $this->assertInstanceOf(Index::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testCreateIndexWithUidInOptions()
    {
        $index = $this->client->createIndex(
            'index',
            [
                'uid' => 'wrong',
                'primaryKey' => 'ObjectId',
            ],
        );

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

        $uids = array_map(function ($index) {
            return $index->getUid();
        }, $response);

        $this->assertContains($indexA, $uids);
        $this->assertContains($indexB, $uids);
    }

    public function testShowIndex()
    {
        $uid = 'index';
        $index = $this->client->createIndex(
            $uid,
            ['primaryKey' => 'objectID']
        );

        $response = $this->client->showIndex($uid);

        $this->assertInstanceOf(Index::class, $index);
        $this->assertIsArray($response);
        $this->assertSame('objectID', $response['primaryKey']);
        $this->assertSame($uid, $response['uid']);
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

    public function testDeleteAllIndexes()
    {
        $this->client->createIndex('index-1');
        $this->client->createIndex('index-2');

        $response = $this->client->getAllIndexes();

        $this->assertCount(2, $response);

        $this->client->deleteAllIndexes();
        $response = $this->client->getAllIndexes();

        $this->assertCount(0, $response);
    }

    public function testDeleteAllIndexesWhenThereAreNoIndexes()
    {
        $response = $this->client->getAllIndexes();
        $this->assertCount(0, $response);

        $this->client->deleteAllIndexes();

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
        $this->expectException(\TypeError::class);
        $this->client->createIndex(null);

        $this->expectException(HTTPRequestException::class);
        $this->client->createIndex('');
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

    public function testHealth()
    {
        $response = $this->client->health();

        $this->assertEmpty($response);
    }

    public function testVersion()
    {
        $response = $this->client->version();

        $this->assertArrayHasKey('commitSha', $response);
        $this->assertArrayHasKey('buildDate', $response);
        $this->assertArrayHasKey('pkgVersion', $response);
    }

    public function testSysInfo()
    {
        $response = $this->client->sysInfo();

        $this->assertArrayHasKey('memoryUsage', $response);
        $this->assertArrayHasKey('processorUsage', $response);
        $this->assertArrayHasKey('global', $response);
        $this->assertArrayHasKey('process', $response);
        $this->assertIsNotString($response['processorUsage'][0]);
    }

    public function testPrettySysInfo()
    {
        $response = $this->client->prettySysInfo();

        $this->assertArrayHasKey('memoryUsage', $response);
        $this->assertArrayHasKey('processorUsage', $response);
        $this->assertArrayHasKey('global', $response);
        $this->assertArrayHasKey('process', $response);
        $this->assertIsString($response['processorUsage'][0]);
    }

    public function testStats()
    {
        $response = $this->client->stats();

        $this->assertArrayHasKey('databaseSize', $response);
        $this->assertArrayHasKey('lastUpdate', $response);
        $this->assertArrayHasKey('indexes', $response);
    }
}
