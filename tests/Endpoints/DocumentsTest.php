<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\InvalidArgumentException;
use MeiliSearch\Exceptions\JsonEncodingException;
use Tests\TestCase;

final class DocumentsTest extends TestCase
{
    public function testAddDocuments(): void
    {
        $index = $this->createEmptyIndex('documents');
        $promise = $index->addDocuments(self::DOCUMENTS);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);

        $response = $index->getDocuments();
        $this->assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddDocumentsInBatches(): void
    {
        $index = $this->createEmptyIndex('documents');
        $promises = $index->addDocumentsInBatches(self::DOCUMENTS, 2);

        $this->assertCount(4, $promises);

        foreach ($promises as $promise) {
            $this->assertIsValidPromise($promise);
            $index->waitForTask($promise['uid']);
        }

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

        $index = $this->createEmptyIndex('documents');
        $promise = $index->addDocuments($documents);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['uid']);

        $response = $index->getDocuments();
        $this->assertCount(\count($documents), $response);

        foreach ($documents as $k => $document) {
            $this->assertSame($document['title'], $response[$k]['title']);
            $this->assertSame($document['comment'], $response[$k]['comment']);
        }
    }

    public function testAddDocumentsCsv(): void
    {
        $index = $this->client->index('documentCsv');

        $fileCsv = fopen('./tests/datasets/songs.csv', 'r');
        $documentCsv = fread($fileCsv, filesize('./tests/datasets/songs.csv'));
        fclose($fileCsv);

        $promise = $index->addDocumentsCsv($documentCsv);

        $this->assertIsValidPromise($promise);

        $update = $index->waitForTask($promise['uid']);

        $this->assertEquals($update['status'], 'succeeded');
        $this->assertNotEquals($update['details']['receivedDocuments'], 0);

        $response = $index->getDocuments();
        $this->assertCount(20, $response);
    }

    public function testAddDocumentsJson(): void
    {
        $index = $this->client->index('documentJson');

        $fileJson = fopen('./tests/datasets/small_movies.json', 'r');
        $documentJson = fread($fileJson, filesize('./tests/datasets/small_movies.json'));
        fclose($fileJson);

        $promise = $index->addDocumentsJson($documentJson);

        $this->assertIsValidPromise($promise);

        $update = $index->waitForTask($promise['uid']);

        $this->assertEquals($update['status'], 'succeeded');
        $this->assertNotEquals($update['details']['receivedDocuments'], 0);

        $response = $index->getDocuments();
        $this->assertCount(20, $response);
    }

    public function testAddDocumentsNdJson(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $promise = $index->addDocumentsNdJson($documentNdJson);

        $this->assertIsValidPromise($promise);

        $update = $index->waitForTask($promise['uid']);

        $this->assertEquals($update['status'], 'succeeded');
        $this->assertNotEquals($update['details']['receivedDocuments'], 0);

        $response = $index->getDocuments();
        $this->assertCount(20, $response);
    }

    public function testCannotAddDocumentWhenJsonEncodingFails(): void
    {
        $this->expectException(JsonEncodingException::class);
        $this->expectExceptionMessage('Encoding payload to json failed: "Malformed UTF-8 characters, possibly incorrectly encoded".');

        $documents = ["\xB1\x31"];

        $index = $this->createEmptyIndex('documents');
        $index->addDocuments($documents);
    }

    public function testGetSingleDocumentWithIntegerDocumentId(): void
    {
        $index = $this->createEmptyIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['uid']);
        $doc = $this->findDocumentWithId(self::DOCUMENTS, 4);
        $response = $index->getDocument($doc['id']);

        $this->assertIsArray($response);
        $this->assertSame($doc['id'], $response['id']);
        $this->assertSame($doc['title'], $response['title']);
    }

    public function testGetSingleDocumentWithStringDocumentId(): void
    {
        $stringDocumentId = 'myUniqueId';
        $index = $this->createEmptyIndex('documents');
        $addDocumentResponse = $index->addDocuments([['id' => $stringDocumentId]]);
        $index->waitForTask($addDocumentResponse['uid']);
        $response = $index->getDocument($stringDocumentId);

        $this->assertIsArray($response);
        $this->assertSame($stringDocumentId, $response['id']);
    }

    public function testReplaceDocuments(): void
    {
        $index = $this->createEmptyIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['uid']);
        $replacement = [
            'id' => 2,
            'title' => 'The Red And The Black',
        ];
        $response = $index->addDocuments([$replacement]);

        $this->assertIsValidPromise($response);

        $index->waitForTask($response['uid']);
        $response = $index->getDocument($replacement['id']);

        $this->assertSame($replacement['id'], $response['id']);
        $this->assertSame($replacement['title'], $response['title']);
        $this->assertFalse(array_search('comment', $response, true));
        $response = $index->getDocuments();
        $this->assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testUpdateDocuments(): void
    {
        $index = $this->createEmptyIndex('documents');
        $promise = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($promise['uid']);
        $replacement = [
            'id' => 456,
            'title' => 'The Little Prince',
        ];
        $promise = $index->updateDocuments([$replacement]);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);
        $response = $index->getDocument($replacement['id']);

        $this->assertSame($replacement['id'], $response['id']);
        $this->assertSame($replacement['title'], $response['title']);
        $this->assertArrayHasKey('comment', $response);

        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testUpdateDocumentsInBatches(): void
    {
        $index = $this->createEmptyIndex('documents');
        $documentPromise = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($documentPromise['uid']);

        $replacements = [
            ['id' => 1, 'title' => 'Alice Outside Wonderland'],
            ['id' => 123, 'title' => 'Pride and Prejudice and Zombies'],
            ['id' => 1344, 'title' => 'The Rabbit'],
            ['id' => 2, 'title' => 'Le Rouge et le Chocolate Noir'],
            ['id' => 4, 'title' => 'Harry Potter and the Half-Blood Princess'],
            ['id' => 456, 'title' => 'The Little Prince'],
        ];
        $promises = $index->updateDocumentsInBatches($replacements, 4);
        $this->assertCount(2, $promises);

        foreach ($promises as $promise) {
            $this->assertIsValidPromise($promise);
            $index->waitForTask($promise['uid']);
        }

        foreach ($replacements as $replacement) {
            $response = $index->getDocument($replacement['id']);
            $this->assertSame($replacement['id'], $response['id']);
            $this->assertSame($replacement['title'], $response['title']);
            $this->assertArrayHasKey('comment', $response);
        }

        $response = $index->getDocuments();
        $this->assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddWithUpdateDocuments(): void
    {
        $index = $this->createEmptyIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['uid']);
        $document = [
            'id' => 9,
            'title' => '1984',
        ];
        $promise = $index->updateDocuments([$document]);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);
        $response = $index->getDocument($document['id']);

        $this->assertSame($document['id'], $response['id']);
        $this->assertSame($document['title'], $response['title']);
        $this->assertFalse(array_search('comment', $response, true));

        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS) + 1, $response);
    }

    public function testDeleteNonExistingDocument(): void
    {
        $index = $this->createEmptyIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['uid']);

        $documentId = 9;
        $promise = $index->deleteDocument($documentId);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);
        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS), $response);
        $this->assertNull($this->findDocumentWithId($response, $documentId));
    }

    public function testDeleteSingleExistingDocumentWithDocumentIdAsInteger(): void
    {
        $index = $this->createEmptyIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['uid']);

        $documentId = 123;
        $promise = $index->deleteDocument($documentId);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);
        $response = $index->getDocuments();

        $this->assertCount(\count(self::DOCUMENTS) - 1, $response);
        $this->assertNull($this->findDocumentWithId($response, $documentId));
    }

    public function testDeleteSingleExistingDocumentWithDocumentIdAsString(): void
    {
        $stringDocumentId = 'myUniqueId';
        $index = $this->createEmptyIndex('documents');
        $addDocumentResponse = $index->addDocuments([['id' => $stringDocumentId]]);
        $index->waitForTask($addDocumentResponse['uid']);

        $promise = $index->deleteDocument($stringDocumentId);
        $index->waitForTask($promise['uid']);

        $response = $index->getDocuments();

        $this->assertEmpty($response);
    }

    public function testDeleteMultipleDocumentsWithDocumentIdAsInteger(): void
    {
        $index = $this->createEmptyIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['uid']);
        $documentIds = [1, 2];
        $promise = $index->deleteDocuments($documentIds);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);
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
        $index = $this->createEmptyIndex('documents');
        $addDocumentResponse = $index->addDocuments($documents);
        $index->waitForTask($addDocumentResponse['uid']);

        $promise = $index->deleteDocuments(['myUniqueId1', 'myUniqueId3']);
        $index->waitForTask($promise['uid']);

        $response = $index->getDocuments();
        $this->assertCount(1, $response);
        $this->assertSame([['id' => 'myUniqueId2']], $response);
    }

    public function testDeleteAllDocuments(): void
    {
        $index = $this->createEmptyIndex('documents');
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['uid']);
        $promise = $index->deleteAllDocuments();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);
        $response = $index->getDocuments();

        $this->assertCount(0, $response);
    }

    public function testExceptionIfNoDocumentIdWhenGetting(): void
    {
        $index = $this->createEmptyIndex('new-index');

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
        $index = $this->createEmptyIndex('an-index');
        $response = $index->addDocuments($documents, 'unique');

        $this->assertArrayHasKey('uid', $response);
        $index->waitForTask($response['uid']);

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
        $index = $this->createEmptyIndex('index');
        $promise = $index->updateDocuments($documents, 'unique');

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);

        $this->assertSame('unique', $index->fetchPrimaryKey());
        $this->assertCount(1, $index->getDocuments());
    }

    /**
     * @dataProvider invalidDocumentIds
     */
    public function testFetchingDocumentWithInvalidId($documentId): void
    {
        $index = $this->createEmptyIndex('an-index');

        $this->expectException(InvalidArgumentException::class);
        $index->getDocument($documentId);
    }

    /**
     * @dataProvider invalidDocumentIds
     */
    public function testDeletingDocumentWithInvalidId($documentId): void
    {
        $index = $this->createEmptyIndex('an-index');

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
