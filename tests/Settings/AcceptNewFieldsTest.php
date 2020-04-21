<?php

use MeiliSearch\Client;
use Tests\TestCase;

class AcceptNewFieldsTest extends TestCase
{
    private $index;

    public function __construct()
    {
        parent::__construct();

        $client = new Client('http://localhost:7700', 'masterKey');
        $client->deleteAllIndexes();
        $this->index = $client->createIndex('index');
    }

    public function testGetDefaultAcceptNewFields()
    {
        $res =$this->index->getAcceptNewFields();
        $this->assertTrue($res);
    }

    public function testUpdateAcceptNewFields()
    {
        $res =$this->index->updateAcceptNewFields(false);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('updateId', $res);
       $this->index->waitForPendingUpdate($res['updateId']);
        $this->assertFalse($this->index->getAcceptNewFields());
    }
}
