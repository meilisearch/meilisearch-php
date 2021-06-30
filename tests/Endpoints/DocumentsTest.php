<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\FailedJsonEncodingException;
use MeiliSearch\Exceptions\InvalidArgumentException;
use Tests\TestCase;

final class DocumentsTest extends TestCase
{
    public function testAddDocuments(): void
    {
        $index = $this->client->createIndex('documents');
        $promise = $index->addDocuments(self::DOCUMENTS);

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);

        $response = $index->getDocuments();
        $this->assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddDocumentWithSpecialChars(): void
    {
        $documents = [
            ['id' => 60, 'title' => 'Sehr schön!', 'comment' => 'ßöüä'], // German
            ['id' => 61, 'title' => 'Très bien!', 'comment' => 'éèê'], // French
            ['id' => 62, 'title' => 'Очень красивый!', 'comment' => ''], // Russian
        ];

        $index = $this->client->createIndex('documents');
        $promise = $index->addDocuments($documents);

        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $response = $index->getDocuments();
        $this->assertCount(\count($documents), $response);

        foreach ($documents as $k => $document) {
            $this->assertSame($document['title'], $response[$k]['title']);
            $this->assertSame($document['comment'], $response[$k]['comment']);
        }
    }

    public function testCannotAddDocumentWhenJsonEncodingFails(): void
    {
        $this->expectException(FailedJsonEncodingException::class);
        $this->expectExceptionMessage('Encoding payload to json failed. Malformed UTF-8 characters, possibly incorrectly encoded');

        $documents = ["\xB1\x31"];

        $index = $this->client->createIndex('documents');
        $index->addDocuments($documents);
    }

    public function testGetSingleDocumentWithIntegerDocumentId(): void
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

    public function testGetSingleDocumentWithStringDocumentId(): void
    {
        $stringDocumentId = 'myUniqueId';
        $index = $this->client->createIndex('documents');
        $addDocumentResponse = $index->addDocuments([['id' => $stringDocumentId]]);
        $index->waitForPendingUpdate($addDocumentResponse['updateId']);
        $response = $index->getDocument($stringDocumentId);

        $this->assertIsArray($response);
        $this->assertSame($stringDocumentId, $response['id']);
    }

    public function testReplaceDocuments(): void
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $replacement = [
            'id' => 2,
            'title' => 'The Red And The Black',
        ];
        $response = $index->addDocuments([$replacement]);

        $this->assertIsValidPromise($response);

        $index->waitForPendingUpdate($response['updateId']);
        $response = $index->getDocument($replacement['id']);

        $this->assertSame($replacement['id'], $response['id']);
        $this->assertSame($replacement['title'], $response['title']);
        $this->assertFalse(array_search('comment', $response, true));
        $response = $index->getDocuments();
        $this->assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testUpdateDocuments(): void
    {
        $index = $this->client->createIndex('documents');
        $promise = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($promise['updateId']);
        $replacement = [
            'id' => 456,
            'title' => 'The Little Prince',
        ];
        $promise = $index->updateDocuments([$replacement]);

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);
        $response = $index->getDocument($replacement['id']);

        $this->assertSame($replacement['id'], $response['id']);
        $this->assertSame($replacement['title'], $response['title']);
        $this->assertArrayHasKey('comment', $response);

        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddWithUpdateDocuments(): void
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $document = [
            'id' => 9,
            'title' => '1984',
        ];
        $promise = $index->updateDocuments([$document]);

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);
        $response = $index->getDocument($document['id']);

        $this->assertSame($document['id'], $response['id']);
        $this->assertSame($document['title'], $response['title']);
        $this->assertFalse(array_search('comment', $response, true));

        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS) + 1, $response);
    }

    public function testDeleteNonExistingDocument(): void
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);

        $documentId = 9;
        $promise = $index->deleteDocument($documentId);

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);
        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS), $response);
        $this->assertNull($this->findDocumentWithId($response, $documentId));
    }

    public function testDeleteSingleExistingDocumentWithDocumentIdAsInteger(): void
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);

        $documentId = 123;
        $promise = $index->deleteDocument($documentId);

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);
        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS) - 1, $response);
        $this->assertNull($this->findDocumentWithId($response, $documentId));
    }

    public function testDeleteSingleExistingDocumentWithDocumentIdAsString(): void
    {
        $stringDocumentId = 'myUniqueId';
        $index = $this->client->createIndex('documents');
        $addDocumentResponse = $index->addDocuments([['id' => $stringDocumentId]]);
        $index->waitForPendingUpdate($addDocumentResponse['updateId']);

        $promise = $index->deleteDocument($stringDocumentId);
        $index->waitForPendingUpdate($promise['updateId']);

        $response = $index->getDocuments();

        $this->assertEmpty($response);
    }

    public function testDeleteMultipleDocumentsWithDocumentIdAsInteger(): void
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $documentIds = [1, 2];
        $promise = $index->deleteDocuments($documentIds);

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);
        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS) - 2, $response);
        $this->assertNull($this->findDocumentWithId($response, $documentIds[0]));
        $this->assertNull($this->findDocumentWithId($response, $documentIds[1]));
    }

    public function testDeleteMultipleDocumentsWithDocumentIdAsString(): void
    {
        $documents = [
            ['id' => 'myUniqueId1'],
            ['id' => 'myUniqueId2'],
            ['id' => 'myUniqueId3'],
        ];
        $index = $this->client->createIndex('documents');
        $addDocumentResponse = $index->addDocuments($documents);
        $index->waitForPendingUpdate($addDocumentResponse['updateId']);

        $promise = $index->deleteDocuments(['myUniqueId1', 'myUniqueId3']);
        $index->waitForPendingUpdate($promise['updateId']);

        $response = $index->getDocuments();
        $this->assertCount(1, $response);
        $this->assertSame([['id' => 'myUniqueId2']], $response);
    }

    public function testDeleteAllDocuments(): void
    {
        $index = $this->client->createIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForPendingUpdate($response['updateId']);
        $promise = $index->deleteAllDocuments();

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);
        $response = $index->getDocuments();

        $this->assertCount(0, $response);
    }

    public function testExceptionIfNoDocumentIdWhenGetting(): void
    {
        $index = $this->client->createIndex('new-index');

        $this->expectException(ApiException::class);

        $index->getDocument(1);
    }

    public function testAddDocumentWithPrimaryKey(): void
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

        $this->assertSame('unique', $index->fetchPrimaryKey());
        $this->assertCount(1, $index->getDocuments());
    }

    public function testUpdateDocumentWithPrimaryKey(): void
    {
        $documents = [
            [
                'id' => 1,
                'unique' => 1,
                'title' => 'Le Rouge et le Noir',
            ],
        ];
        $index = $this->client->createIndex('index');
        $promise = $index->updateDocuments($documents, 'unique');

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);

        $this->assertSame('unique', $index->fetchPrimaryKey());
        $this->assertCount(1, $index->getDocuments());
    }

    /**
     * @dataProvider invalidDocumentIds
     */
    public function testFetchingDocumentWithInvalidId($documentId): void
    {
        $index = $this->client->createIndex('an-index');

        $this->expectException(InvalidArgumentException::class);
        $index->getDocument($documentId);
    }

    /**
     * @dataProvider invalidDocumentIds
     */
    public function testDeletingDocumentWithInvalidId($documentId): void
    {
        $index = $this->client->createIndex('an-index');

        $this->expectException(InvalidArgumentException::class);
        $index->deleteDocument($documentId);
    }

    public function invalidDocumentIds(): array
    {
        return [
            'documentId as null' => [null],
            'documentId as bool' => [true],
            'documentId as empty string' => [''],
            'documentId as float' => [2.1],
            'documentId as array' => [[]],
            'documentId as object' => [new \stdClass()],
            'documentId as resource' => [tmpfile()],
        ];
    }

    private function findDocumentWithId($documents, $documentId)
    {
        foreach ($documents as $document) {
            if ($document['id'] == $documentId) {
                return $document;
            }
        }
    }
}
