<?php

namespace Tests;

use MeiliSearch\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $client;

    const DOCUMENTS = [
        ['id' => 123, 'title' => 'Pride and Prejudice', 'comment' => 'A great book'],
        ['id' => 456, 'title' => 'Le Petit Prince', 'comment' => 'A french book'],
        ['id' => 2, 'title' => 'Le Rouge et le Noir', 'comment' => 'Another french book'],
        ['id' => 1, 'title' => 'Alice In Wonderland', 'comment' => 'A weird book'],
        ['id' => 1344, 'title' => 'The Hobbit', 'comment' => 'An awesome book'],
        ['id' => 4, 'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book'],
        ['id' => 42, 'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client('http://localhost:7700', 'masterKey');
        $this->client->deleteAllIndexes();
    }

    public function assertIsValidPromise(array $promise)
    {
        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
    }
}
