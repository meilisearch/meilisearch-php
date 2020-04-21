<?php

use Tests\TestCase;
use MeiliSearch\Client;

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
        $response = $this->index->getAcceptNewFields();
        $this->assertTrue($response);
    }

    public function testUpdateAcceptNewFields()
    {
        $promise = $this->index->updateAcceptNewFields(false);
        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $this->index->waitForPendingUpdate($promise['updateId']);
        $this->assertFalse($this->index->getAcceptNewFields());
    }
}
