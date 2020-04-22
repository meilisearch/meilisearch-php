<?php

use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

class DocumentsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
    }

    public function testAddDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
    }

    public function testGetDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);

        $response = $index->getDocuments();
        $this->assertCount(count(self::DOCUMENTS), $response);
    }

    public function testGetDocument()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $doc = $this->findDocumentWithId(self::DOCUMENTS, 4);
        $response = $index->getDocument($doc['id']);
        $this->assertIsArray($response);
        $this->assertSame($doc['id'], $response['id']);
        $this->assertSame($doc['title'], $response['title']);
    }

    public function testReplaceDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $replacement = [
            'id' => 2,
            'title' => 'The Red And The Black',
        ];
        $response = $index->addDocuments([$replacement]);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);

        $response = $index->getDocument($replacement['id']);
        $this->assertSame($replacement['id'], $response['id']);
        $this->assertSame($replacement['title'], $response['title']);
        $this->assertFalse(array_search('comment', $response));
        $response = $index->getDocuments();
        $this->assertCount(count(self::DOCUMENTS), $response);
    }

    public function testUpdateDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $replacement = [
            'id' => 456,
            'title' => 'The Little Prince',
        ];

        $response = $index->updateDocuments([$replacement]);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocument($replacement['id']);
        $this->assertSame($replacement['id'], $response['id']);
        $this->assertSame($replacement['title'], $response['title']);
        $this->assertArrayHasKey('comment', $response);
        $response = $index->getDocuments();
        $this->assertCount(count(self::DOCUMENTS), $response);
    }

    public function testAddWithUpdateDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $document = [
            'id' => 9,
            'title' => '1984',
        ];
        $response = $index->updateDocuments([$document]);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocument($document['id']);
        $this->assertSame($document['id'], $response['id']);
        $this->assertSame($document['title'], $response['title']);
        $this->assertFalse(array_search('comment', $response));
        $response = $index->getDocuments();
        $this->assertCount(count(self::DOCUMENTS) + 1, $response);
    }

    public function testDeleteNonExistingDocument()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $id = 9;
        $response = $index->deleteDocument($id);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocuments();
        $this->assertCount(count(self::DOCUMENTS), $response);
        $this->assertNull($this->findDocumentWithId($response, $id));
    }

    public function testDeleteSingleExistingDocument()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);

        $id = 123;
        $response = $index->deleteDocument($id);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocuments();
        $this->assertCount(count(self::DOCUMENTS) - 1, $response);
        $this->assertNull($this->findDocumentWithId($response, $id));
    }

    public function testDeleteMultipleDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $ids = [1, 2];
        $response = $index->deleteDocuments($ids);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('updateId', $response);
        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocuments();
        $this->assertCount(count(self::DOCUMENTS) - 2, $response);
        $this->assertNull($this->findDocumentWithId($response, $ids[0]));
        $this->assertNull($this->findDocumentWithId($response, $ids[1]));
    }

    public function testDeleteAllDocuments()
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
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
