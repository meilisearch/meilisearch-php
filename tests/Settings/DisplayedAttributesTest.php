<?php

use MeiliSearch\Client;
use Tests\TestCase;

class DisplayedAttributesTest extends TestCase
{
    private $client;
    private $index1;

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client('http://localhost:7700', 'masterKey');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
    }

    public function testGetDefaultDisplayedAttributes()
    {
        $indexA = $this->client->createIndex('indexA');
        $indexB = $this->client->createIndex(['uid' => 'indexB', 'primaryKey' => 'objectID']);

        $attributesA = $indexA->getDisplayedAttributes();
        $attributesB = $indexB->getDisplayedAttributes();

        $this->assertIsArray($attributesA);
        $this->assertEmpty($attributesA);

        $this->assertIsArray($attributesB);
        $this->assertEquals(['objectID'], $attributesB);
    }

    public function testUpdateDisplayedAttributes()
    {
        $newAttributes = ['title'];
        $index = $this->client->createIndex('index');

        $promise = $index->updateDisplayedAttributes($newAttributes);

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $displayedAttributes = $index->getDisplayedAttributes();

        $this->assertIsArray($displayedAttributes);
        $this->assertEquals($newAttributes, $displayedAttributes);
    }

    public function testResetDisplayedAttributes()
    {
        $index = $this->client->createIndex('index');
        $newAttributes = ['title'];

        $promise = $index->updateDisplayedAttributes($newAttributes);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->resetDisplayedAttributes();

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);

        $index->waitForPendingUpdate($promise['updateId']);

        $displayedAttributes = $index->getDisplayedAttributes();
        $this->assertIsArray($displayedAttributes);
        // according to issue #21 the resetDisplayedAttributes reverts back to the default keys in the index
        $this->assertNotEmpty($displayedAttributes);
        $this->assertContains('title', $displayedAttributes);
    }
}
