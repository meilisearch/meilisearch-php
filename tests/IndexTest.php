<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use PHPUnit\Framework\TestCase;

require_once 'utils.php';

class IndexTest extends TestCase
{
    private static $index;
    private static $name;
    private static $uid;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$name = 'name';
        static::$uid = 'uid';
        $client = new Client('http://localhost:7700', 'apiKey');
        deleteAllIndexes($client);
        static::$index = $client->createIndex(static::$name, static::$uid);
    }

    public function testGetName()
    {
        $this->assertSame(static::$index->getName(), static::$name);
    }

    public function testGetUid()
    {
        $this->assertSame(static::$index->getUid(), static::$uid);
    }

    public function testShow()
    {
        $res = static::$index->show();
        $this->assertArrayHasKey('name', $res);
        $this->assertArrayHasKey('uid', $res);
        $this->assertArrayHasKey('createdAt', $res);
        $this->assertArrayHasKey('updatedAt', $res);
        $this->assertSame($res['name'], static::$name);
        $this->assertSame($res['uid'], static::$uid);
    }

    public function testUpdateName()
    {
        $new_name = 'new name';
        $res = static::$index->updateName($new_name);
        $this->assertArrayHasKey('name', $res);
        $this->assertArrayHasKey('uid', $res);
        $this->assertArrayHasKey('createdAt', $res);
        $this->assertArrayHasKey('updatedAt', $res);
        $this->assertSame($res['name'], $new_name);
        $this->assertSame($res['uid'], static::$uid);
        $res = static::$index->show();
        $this->assertSame($res['name'], $new_name);
    }

    public function testDelete()
    {
        $res = static::$index->delete();
        $this->assertEmpty($res);
    }

    public function testExceptionIfNoIndexWhenGettingName()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index->getName();
    }

    public function testExceptionIfNoIndexWhenShowing()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index->show();
    }

    public function testExceptionIfNoIndexWhenUpdating()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index->updateName('nope');
    }

    public function testExceptionIfNoIndexWhenDeleting()
    {
        $this->expectException(HTTPRequestException::class);
        static::$index->delete();
    }
}
