<?php

use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

class DocumentsTest extends TestCase
{
    private $client;
    private $documents;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client('http://localhost:7700', 'masterKey');
        $this->documents = [
            ['id' => 123,  'title' => 'Pride and Prejudice',                    'comment' => 'A great book'],
            ['id' => 456,  'title' => 'Le Petit Prince',                        'comment' => 'A french book'],
            ['id' => 2,    'title' => 'Le Rouge et le Noir',                    'comment' => 'Another french book'],
            ['id' => 1,    'title' => 'Alice In Wonderland',                    'comment' => 'A weird book'],
            ['id' => 1344, 'title' => 'The Hobbit',                             'comment' => 'An awesome book'],
            ['id' => 4,    'title' => 'Harry Potter and the Half-Blood Prince', 'comment' => 'The best book'],
            ['id' => 42,   'title' => 'The Hitchhiker\'s Guide to the Galaxy'],
        ];
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->client->deleteAllIndexes();}

    public function testAddDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments($this->documents);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
    }

    // DOCUMENTS

    public function testGetDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response=$index->addDocuments($this->documents);
        $index->waitForPendingUpdate($response['updateId']);

        $response = $index->getDocuments();
        $this->assertCount(count($this->documents), $response);
    }

    public function testGetDocument()
    {
        $index = $this->client->createIndex('documents');
        $response=$index->addDocuments($this->documents);
        $index->waitForPendingUpdate($response['updateId']);
        $doc = $this->findDocumentWithId($this->documents, 4);
        $response = $index->getDocument($doc['id']);
        $this->assertIsArray($response);
        $this->assertSame($doc['id'], $response['id']);
        $this->assertSame($doc['title'], $response['title']);
    }

    public function testReplaceDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response=$index->addDocuments($this->documents);
        $index->waitForPendingUpdate($response['updateId']);

        $id = 2;
        $new_title = 'The Red And The Black';
        $response = $index->addDocuments([['id' => $id, 'title' => $new_title]]);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocument($id);
        $this->assertSame($id, $response['id']);
        $this->assertSame($new_title, $response['title']);
        $this->assertFalse(array_search('comment', $response));
        $response = $index->getDocuments();
        $this->assertCount(count($this->documents), $response);
    }

    public function testUpdateDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response=$index->addDocuments($this->documents);
        $index->waitForPendingUpdate($response['updateId']);
        $id = 456;
        $new_title = 'The Little Prince';
        $response = $index->updateDocuments([['id' => $id, 'title' => $new_title]]);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocument($id);
        $this->assertSame($id, $response['id']);
        $this->assertSame($new_title, $response['title']);
        $this->assertArrayHasKey('comment', $response);
        $response = $index->getDocuments();
        $this->assertCount(count($this->documents), $response);
    }

    public function testAddWithUpdateDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response=$index->addDocuments($this->documents);
        $index->waitForPendingUpdate($response['updateId']);
        $id = 9;
        $title = '1984';
        $response = $index->updateDocuments([['id' => $id, 'title' => $title]]);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocument($id);
        $this->assertSame($id, $response['id']);
        $this->assertSame($title, $response['title']);
        $this->assertFalse(array_search('comment', $response));
        $response = $index->getDocuments();
        $this->assertCount(count($this->documents) + 1, $response);
    }

    public function testDeleteDocument()
    {
        $index = $this->client->createIndex('documents');
        $response=$index->addDocuments($this->documents);
        $index->waitForPendingUpdate($response['updateId']);
        $id = 9;
        $response = $index->deleteDocument($id);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocuments();
        $this->assertCount(count($this->documents), $response);
        $this->assertNull($this->findDocumentWithId($response, $id));
    }

    public function testDeleteMultipleDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response=$index->addDocuments($this->documents);
        $index->waitForPendingUpdate($response['updateId']);
        $ids = [1, 2];
        $response = $index->deleteDocuments($ids);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocuments();
        $this->assertCount(count($this->documents) - 2, $response);
        $this->assertNull($this->findDocumentWithId($response, $ids[0]));
        $this->assertNull($this->findDocumentWithId($response, $ids[1]));
    }

    public function testDeleteAllDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response=$index->addDocuments($this->documents);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->deleteAllDocuments();
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocuments();
        $this->assertCount(0, $response);
    }

    public function testExceptionIfNoDocumentIdWhenGetting()
    {
        $index = $this->client->createIndex('new-index');
        $this->expectException(HTTPRequestException::class);
        $index->getDocument(1);
    }

    public function testAddDocumentWithPrimaryKey()
    {
        $documents = [
            [
                'id' => 1,
                'unique' => 1,
                'title' => 'Le Rouge et le Noir',
            ],
        ];
        $index = $this->client->createIndex('an-index');
        $response = $index->addDocuments($documents, 'unique');
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $this->assertSame('unique', $index->getPrimaryKey());
        $this->assertCount(1, $index->getDocuments());
    }

    public function testUpdateDocumentWithPrimaryKey()
    {
        $documents = [
            [
                'id' => 1,
                'unique' => 1,
                'title' => 'Le Rouge et le Noir',
            ],
        ];
        $index = $this->client->createIndex('udpateUid');
        $response = $index->updateDocuments($documents, 'unique');
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $this->assertSame('unique', $index->getPrimaryKey());
        $this->assertCount(1, $index->getDocuments());
    }

    private function findDocumentWithId($documents, $id)
    {
        foreach ($documents as $document) {
            if ($document['id'] == $id) {
                return $document;
            }
        }
    }
}
