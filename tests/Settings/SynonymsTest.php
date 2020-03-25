<?php

use MeiliSearch\Client;
use PHPUnit\Framework\TestCase;

define('__ROOT__', dirname(dirname(__FILE__)));
require_once __ROOT__.'/utils.php';

class SynonymsTest extends TestCase
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

    public function testGetDefaultSynonyms()
    {
        $res = static::$index->getSynonyms();
        $this->assertIsArray($res);
        $this->assertEmpty($res);
    }

    public function testUpdateSynonyms()
    {
        $new_s = [
            'hp' => ['harry potter'],
        ];
        $res = static::$index->updateSynonyms($new_s);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index->waitForUpdateStatus($res['updateId']);
        $s = static::$index->getSynonyms();
        $this->assertIsArray($s);
        $this->assertEquals($new_s, $s);
    }

    public function testResetSynonyms()
    {
        $res = static::$index->resetSynonyms();
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
        static::$index->waitForUpdateStatus($res['updateId']);
        $s = static::$index->getSynonyms();
        $this->assertIsArray($s);
        $this->assertEmpty($s);
    }
}
