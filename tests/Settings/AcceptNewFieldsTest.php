<?php

namespace Tests\Settings;

use Tests\TestCase;

class AcceptNewFieldsTest extends TestCase
{
    private $index;

    public function __construct()
    {
        parent::__construct();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetDefaultAcceptNewFields()
    {
        $response = $this->index->getAcceptNewFields();
        $this->assertTrue($response);
    }

    public function testUpdateAcceptNewFields()
    {
        $promise = $this->index->updateAcceptNewFields(false);

        $this->assertIsValidPromise($promise);

        $this->index->waitForPendingUpdate($promise['updateId']);

        $this->assertFalse($this->index->getAcceptNewFields());
    }
}
