<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use PHPUnit\Framework\TestCase;

require_once 'utils.php';

class IndexTest extends TestCase
{
    private static $index1;
    private static $index2;
    private static $uid1;
    private static $uid2;
    private static $primary_key;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$uid1 = 'uid1';
        static::$uid2 = 'uid2';
        static::$primary_key = 'objectID';
        $client = new Client('http://localhost:7700', 'masterKey');
        deleteAllIndexes($client);
        static::$index1 = $client->createIndex(static::$uid1);
        static::$index2 = $client->createIndex([
            'uid' => static::$uid2,
            'primaryKey' => static::$primary_key,
        ]);
    }

    public function testgetPrimaryKey()
    {
        $this->assertNull(static::$index1->getPrimaryKey());
        $this->assertSame(static::$primary_key, static::$index2->getPrimaryKey());
    }

    public function testGetUid()
    {
        $this->assertSame(static::$uid1, static::$index1->getUid());
        $this->assertSame(static::$uid2, static::$index2->getUid());
    }

    public function testShow()
    {
        $res = static::$index2->show();
        $this->assertArrayHasKey('primaryKey', $res);
        $this->assertArrayHasKey('uid', $res);
        $this->assertArrayHasKey('createdAt', $res);
        $this->assertArrayHasKey('updatedAt', $res);
        $this->assertSame($res['primaryKey'], static::$primary_key);
        $this->assertSame($res['uid'], static::$uid2);
    }

    public function testUpdate()
    {
        $id = 'id';
        $res = static::$index1->update(['primaryKey' => $id]);
        $this->assertSame($res['primaryKey'], $id);
        $this->assertSame($res['uid'], static::$uid1);
    }

    public function testExceptionIfPrimaryKeyIsPresentWhenUpdating()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index2->update(['primaryKey' => 'objectID']);
    }

    public function testIndexStats()
    {
        $res = static::$index1->stats();
        $this->assertArrayHasKey('numberOfDocuments', $res);
        $this->assertArrayHasKey('isIndexing', $res);
        $this->assertArrayHasKey('fieldsFrequency', $res);
    }

    public function testDelete()
    {
        $res = static::$index1->delete();
        $this->assertEmpty($res);
        $res = static::$index2->delete();
        $this->assertEmpty($res);
    }

    public function testExceptionIfNoIndexWhenShowing()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index1->show();
    }

    public function testExceptionIfNoIndexWhenUpdating()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index1->update(['primaryKey' => 'objectID']);
    }

    public function testExceptionIfNoIndexWhenDeleting()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index1->delete();
    }
}
