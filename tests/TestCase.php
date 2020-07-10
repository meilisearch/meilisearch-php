<?php

declare(strict_types=1);

namespace Tests;

use MeiliSearch\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    const DOCUMENTS = [
        ['id' => 123, 'title' => 'Pride and Prejudice', 'comment' => 'A great book', 'genre' => 'romance'],
        ['id' => 456, 'title' => 'Le Petit Prince', 'comment' => 'A french book', 'genre' => 'adventure'],
        ['id' => 2, 'title' => 'Le Rouge et le Noir', 'comment' => 'Another french book', 'genre' => 'romance'],
        ['id' => 1, 'title' => 'Alice In Wonderland', 'comment' => 'A weird book', 'genre' => 'fantasy'],
        ['id' => 1344, 'title' => 'The Hobbit', 'comment' => 'An awesome book', 'genre' => 'romance'],
        ['id' => 4, 'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book', 'genre' => 'fantasy'],
        ['id' => 42, 'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
    ];

    const HOST = 'http://localhost:7700';

    const DEFAULT_KEY = 'masterKey';

    protected $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client(self::HOST, self::DEFAULT_KEY);
        $this->client->deleteAllIndexes();
    }

    public function assertIsValidPromise(array $promise)
    {
        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
    }
}
