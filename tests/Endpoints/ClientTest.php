<?php

namespace Tests\Endpoints;

use Http\Client\Exception\NetworkException;
use MeiliSearch\Client;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\HTTPRequestException;
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

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testCreateIndexWithUidAndPrimaryKey()
    {
        $index = $this->client->createIndex(
            'index',
            ['primaryKey' => 'ObjectId']
        );

        $this->assertInstanceOf(Indexes::class, $index);
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
            ]
        );

        $this->assertInstanceOf(Indexes::class, $index);
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

        $this->assertInstanceOf(Indexes::class, $index);
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
        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testGetOrCreateIndexWithOnlyUid()
    {
        $index = $this->client->getOrCreateIndex('index');

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertNull($index->getPrimaryKey());
    }

    public function testGetOrCreateIndexWithUidAndPrimaryKey()
    {
        $index = $this->client->getOrCreateIndex(
            'index',
            ['primaryKey' => 'ObjectId']
        );

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testGetOrCreateIndexWithUidInOptions()
    {
        $index = $this->client->getOrCreateIndex(
            'index',
            [
                'uid' => 'wrong',
                'primaryKey' => 'ObjectId',
            ]
        );

        $this->assertInstanceOf(Indexes::class, $index);
        $this->assertSame('index', $index->getUid());
        $this->assertSame('ObjectId', $index->getPrimaryKey());
    }

    public function testGetOrCreateWithIndexAlreadyExists()
    {
        $index1 = $this->client->getOrCreateIndex('index');
        $index2 = $this->client->getOrCreateIndex('index');
        $index3 = $this->client->getOrCreateIndex('index');

        $this->assertSame('index', $index1->getUid());
        $this->assertSame('index', $index2->getUid());
        $this->assertSame('index', $index3->getUid());

        $update = $index1->addDocuments([['book_id' => 1, 'name' => 'Some book']]);
        $index1->waitForPendingUpdate($update['updateId']);

        $documents = $index2->getDocuments();
        $this->assertCount(1, $documents);
        $index2->delete();
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

    public function testStats()
    {
        $response = $this->client->stats();

        $this->assertArrayHasKey('databaseSize', $response);
        $this->assertArrayHasKey('lastUpdate', $response);
        $this->assertArrayHasKey('indexes', $response);
    }

    public function testBadClientUrl()
    {
        try {
            $this->client = new Client('http://127.0.0.1.com:1234', 'some-key');
            $this->client->createIndex('index');
        } catch (NetworkException $e) {
            $this->assertIsString($e->getMessage());

            return;
        }
        $this->fail('Bad client was accepted and the exception was not thrown');
    }
}
