<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\DocumentsQuery;
use Meilisearch\Contracts\Http;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\InvalidArgumentException;
use Meilisearch\Exceptions\InvalidResponseBodyException;
use Meilisearch\Exceptions\JsonEncodingException;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

final class DocumentsTest extends TestCase
{
    public function testAddDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $promise = $index->addDocuments(self::DOCUMENTS);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $response = $index->getDocuments();
        self::assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddDocumentsInBatches(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $promises = $index->addDocumentsInBatches(self::DOCUMENTS, 2);

        self::assertCount(4, $promises);

        foreach ($promises as $promise) {
            $this->assertIsValidPromise($promise);
            $index->waitForTask($promise['taskUid']);
        }

        $response = $index->getDocuments();
        self::assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddDocumentWithSpecialChars(): void
    {
        $documents = [
            ['id' => 60, 'title' => 'Sehr schön!', 'comment' => 'ßöüä'], // German
            ['id' => 61, 'title' => 'Très bien!', 'comment' => 'éèê'], // French
            ['id' => 62, 'title' => 'Очень красивый!', 'comment' => ''], // Russian
        ];

        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $promise = $index->addDocuments($documents);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocuments();
        self::assertCount(\count($documents), $response);

        foreach ($documents as $k => $document) {
            self::assertSame($document['title'], $response[$k]['title']);
            self::assertSame($document['comment'], $response[$k]['comment']);
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

        $update = $index->waitForTask($promise['taskUid']);

        self::assertEquals('succeeded', $update['status']);
        self::assertNotEquals(0, $update['details']['receivedDocuments']);

        $response = $index->getDocuments();
        self::assertCount(20, $response);
    }

    public function testAddDocumentsCsvWithCustomSeparator(): void
    {
        $index = $this->client->index('documentCsvWithCustomSeparator');

        $csv = file_get_contents('./tests/datasets/songs-custom-separator.csv', true);

        $promise = $index->addDocumentsCsv($csv, null, '|');

        $this->assertIsValidPromise($promise);

        $update = $index->waitForTask($promise['taskUid']);

        self::assertEquals($update['status'], 'succeeded');
        self::assertEquals($update['details']['receivedDocuments'], 6);

        $documents = $index->getDocuments()->getResults();
        self::assertEquals('Teenage Neon Jungle', $documents[4]['album']);
        self::assertEquals('631152000', $documents[5]['released-timestamp']);
    }

    public function testAddDocumentsJson(): void
    {
        $index = $this->client->index('documentJson');

        $fileJson = fopen('./tests/datasets/small_movies.json', 'r');
        $documentJson = fread($fileJson, filesize('./tests/datasets/small_movies.json'));
        fclose($fileJson);

        $promise = $index->addDocumentsJson($documentJson);

        $this->assertIsValidPromise($promise);

        $update = $index->waitForTask($promise['taskUid']);

        self::assertEquals('succeeded', $update['status']);
        self::assertNotEquals(0, $update['details']['receivedDocuments']);

        $response = $index->getDocuments();
        self::assertCount(20, $response);
    }

    public function testAddDocumentsNdJson(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $promise = $index->addDocumentsNdjson($documentNdJson);

        $this->assertIsValidPromise($promise);

        $update = $index->waitForTask($promise['taskUid']);

        self::assertEquals('succeeded', $update['status']);
        self::assertNotEquals(0, $update['details']['receivedDocuments']);

        $response = $index->getDocuments();
        self::assertCount(20, $response);
    }

    public function testCannotAddDocumentWhenJsonEncodingFails(): void
    {
        $this->expectException(JsonEncodingException::class);
        $this->expectExceptionMessage('Encoding payload to json failed: "Malformed UTF-8 characters, possibly incorrectly encoded".');

        $documents = ["\xB1\x31"];

        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments($documents);
    }

    public function testGetSingleDocumentWithIntegerDocumentId(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['taskUid']);
        $doc = $this->findDocumentWithId(self::DOCUMENTS, 4);
        $response = $index->getDocument($doc['id']);

        self::assertIsArray($response);
        self::assertSame($doc['id'], $response['id']);
        self::assertSame($doc['title'], $response['title']);
    }

    public function testGetSingleDocumentWithFields(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['taskUid']);
        $doc = $this->findDocumentWithId(self::DOCUMENTS, 4);
        $response = $index->getDocument($doc['id'], ['title']);

        self::assertIsArray($response);
        self::assertSame($doc['title'], $response['title']);
        self::assertArrayNotHasKey('id', $response);
    }

    public function testGetSingleDocumentWithStringDocumentId(): void
    {
        $stringDocumentId = 'myUniqueId';
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $addDocumentResponse = $index->addDocuments([['id' => $stringDocumentId]]);
        $index->waitForTask($addDocumentResponse['taskUid']);
        $response = $index->getDocument($stringDocumentId);

        self::assertIsArray($response);
        self::assertSame($stringDocumentId, $response['id']);
    }

    public function testReplaceDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['taskUid']);
        $replacement = [
            'id' => 2,
            'title' => 'The Red And The Black',
        ];
        $response = $index->addDocuments([$replacement]);

        $this->assertIsValidPromise($response);

        $index->waitForTask($response['taskUid']);
        $response = $index->getDocument($replacement['id']);

        self::assertSame($replacement['id'], $response['id']);
        self::assertSame($replacement['title'], $response['title']);
        self::assertFalse(array_search('comment', $response, true));
        $response = $index->getDocuments();
        self::assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testUpdateDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $promise = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($promise['taskUid']);
        $replacement = [
            'id' => 456,
            'title' => 'The Little Prince',
        ];
        $promise = $index->updateDocuments([$replacement]);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $response = $index->getDocument($replacement['id']);

        self::assertSame($replacement['id'], $response['id']);
        self::assertSame($replacement['title'], $response['title']);
        self::assertArrayHasKey('comment', $response);

        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testUpdateDocumentsInBatches(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $documentPromise = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($documentPromise['taskUid']);

        $replacements = [
            ['id' => 1, 'title' => 'Alice Outside Wonderland'],
            ['id' => 123, 'title' => 'Pride and Prejudice and Zombies'],
            ['id' => 1344, 'title' => 'The Rabbit'],
            ['id' => 2, 'title' => 'Le Rouge et le Chocolate Noir'],
            ['id' => 4, 'title' => 'Harry Potter and the Half-Blood Princess'],
            ['id' => 456, 'title' => 'The Little Prince'],
        ];
        $promises = $index->updateDocumentsInBatches($replacements, 4);
        self::assertCount(2, $promises);

        foreach ($promises as $promise) {
            $this->assertIsValidPromise($promise);
            $index->waitForTask($promise['taskUid']);
        }

        foreach ($replacements as $replacement) {
            $response = $index->getDocument($replacement['id']);
            self::assertSame($replacement['id'], $response['id']);
            self::assertSame($replacement['title'], $response['title']);
            self::assertArrayHasKey('comment', $response);
        }

        $response = $index->getDocuments();
        self::assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddDocumentsCsvInBatches(): void
    {
        $index = $this->client->index('documentCsv');

        $fileCsv = fopen('./tests/datasets/songs.csv', 'r');
        $documentCsv = fread($fileCsv, filesize('./tests/datasets/songs.csv'));
        fclose($fileCsv);

        // Total number of lines excluding header
        $total = \count(preg_split("/\r\n|\n|\r/", trim($documentCsv))) - 1;

        $promises = $index->addDocumentsCsvInBatches($documentCsv, 250);

        self::assertCount(2, $promises);

        foreach ($promises as $promise) {
            $this->assertIsValidPromise($promise);
            $index->waitForTask($promise['taskUid']);
        }

        $response = $index->getDocuments();
        self::assertSame($total, $response->getTotal());
    }

    public function testAddDocumentsCsvInBatchesWithDelimiter(): void
    {
        $documentCsv = 'id;title'.PHP_EOL;
        $documentCsv .= '888221515;Young folks'.PHP_EOL;
        $documentCsv .= '235115704;Mister Klein'.PHP_EOL;

        $index = $this
            ->getMockBuilder('\Meilisearch\Endpoints\Indexes')
            ->onlyMethods(['addDocumentsCsv'])
            ->disableOriginalConstructor()
            ->getMock();

        $index->expects(self::exactly(2))
              ->method('addDocumentsCsv')
              ->willReturnCallback(function (string $documents, $primaryKey, $delimiter): void {
                  static $invocation = 0;
                  // withConsecutive has no replacement https://github.com/sebastianbergmann/phpunit/issues/4026
                  switch (++$invocation) {
                      case 1:
                          static::assertSame(["id;title\n888221515;Young folks", null, ';'], [$documents, $primaryKey, $delimiter]);
                          break;
                      case 2:
                          static::assertSame(["id;title\n235115704;Mister Klein", null, ';'], [$documents, $primaryKey, $delimiter]);
                          break;
                      default:
                          self::fail();
                  }
              });

        $index->addDocumentsCsvInBatches($documentCsv, 1, null, ';');
    }

    public function testAddDocumentsNdjsonInBatches(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $total = \count(preg_split("/\r\n|\n|\r/", trim($documentNdJson)));

        $promises = $index->addDocumentsNdjsonInBatches($documentNdJson, 150);

        self::assertCount(2, $promises);

        foreach ($promises as $promise) {
            $this->assertIsValidPromise($promise);
            $index->waitForTask($promise['taskUid']);
        }

        $response = $index->getDocuments();
        self::assertSame($total, $response->getTotal());
    }

    public function testAddWithUpdateDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['taskUid']);
        $document = [
            'id' => 9,
            'title' => '1984',
        ];
        $promise = $index->updateDocuments([$document]);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $response = $index->getDocument($document['id']);

        self::assertSame($document['id'], $response['id']);
        self::assertSame($document['title'], $response['title']);
        self::assertFalse(array_search('comment', $response, true));

        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS) + 1, $response);
    }

    public function testDeleteNonExistingDocument(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['taskUid']);

        $documentId = 9;
        $promise = $index->deleteDocument($documentId);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS), $response);
        self::assertNull($this->findDocumentWithId($response, $documentId));
    }

    public function testDeleteSingleExistingDocumentWithDocumentIdAsInteger(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['taskUid']);

        $documentId = 123;
        $promise = $index->deleteDocument($documentId);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS) - 1, $response);
        self::assertNull($this->findDocumentWithId($response, $documentId));
    }

    public function testDeleteSingleExistingDocumentWithDocumentIdAsString(): void
    {
        $stringDocumentId = 'myUniqueId';
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $addDocumentResponse = $index->addDocuments([['id' => $stringDocumentId]]);
        $index->waitForTask($addDocumentResponse['taskUid']);

        $promise = $index->deleteDocument($stringDocumentId);
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocuments();

        self::assertEmpty($response);
    }

    public function testDeleteMultipleDocumentsWithDocumentIdAsInteger(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['taskUid']);
        $documentIds = [1, 2];
        $promise = $index->deleteDocuments($documentIds);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS) - 2, $response);
        self::assertNull($this->findDocumentWithId($response, $documentIds[0]));
        self::assertNull($this->findDocumentWithId($response, $documentIds[1]));
    }

    public function testDeleteMultipleDocumentsWithFilter(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS);
        $index->updateFilterableAttributes(['id']);

        $filter = ['filter' => ['id > 0']];
        $promise = $index->deleteDocuments($filter);

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $response = $index->getDocuments();

        self::assertEmpty($response);
    }

    public function testMessageHintException(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(0);
        $mockedException = new InvalidResponseBodyException($responseMock, 'Invalid response');

        try {
            $httpMock = $this->createMock(Http::class);
            $httpMock->expects(self::once())
                ->method('post')
                ->willThrowException($mockedException);

            $indexMock = new Indexes($httpMock, 'uid');
            $indexMock->deleteDocuments(['filter' => ['id > 0']]);
        } catch (\Exception $ex) {
            $rethrowed = ApiException::rethrowWithHint($mockedException, 'deleteDocuments');

            self::assertSame($ex->getPrevious()->getMessage(), 'Invalid response');
            self::assertSame($ex->getMessage(), $rethrowed->getMessage());
        }
    }

    public function testDeleteMultipleDocumentsWithDocumentIdAsString(): void
    {
        $documents = [
            ['id' => 'myUniqueId1'],
            ['id' => 'myUniqueId2'],
            ['id' => 'myUniqueId3'],
        ];
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $addDocumentResponse = $index->addDocuments($documents);
        $index->waitForTask($addDocumentResponse['taskUid']);

        $promise = $index->deleteDocuments(['myUniqueId1', 'myUniqueId3']);
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocuments();
        self::assertCount(1, $response);
        self::assertSame([['id' => 'myUniqueId2']], $response->getResults());
    }

    public function testDeleteAllDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $response = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($response['taskUid']);
        $promise = $index->deleteAllDocuments();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $response = $index->getDocuments();

        self::assertCount(0, $response);
    }

    public function testExceptionIfNoDocumentIdWhenGetting(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('books-1'));

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
        $index = $this->createEmptyIndex($this->safeIndexName('movies-1'));
        $response = $index->addDocuments($documents, 'unique');

        self::assertArrayHasKey('taskUid', $response);
        $index->waitForTask($response['taskUid']);

        self::assertSame('unique', $index->fetchPrimaryKey());
        self::assertCount(1, $index->getDocuments());
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
        $index = $this->createEmptyIndex($this->safeIndexName());
        $promise = $index->updateDocuments($documents, 'unique');

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);

        self::assertSame('unique', $index->fetchPrimaryKey());
        self::assertCount(1, $index->getDocuments());
    }

    public function testGetDocumentsWithPagination(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $promise = $index->addDocuments(self::DOCUMENTS);
        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocuments((new DocumentsQuery())->setLimit(3));

        self::assertCount(3, $response);
    }

    public function testGetDocumentsWithFilter(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->updateFilterableAttributes(['genre', 'id']);
        $promise = $index->addDocuments(self::DOCUMENTS);
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocuments((new DocumentsQuery())->setFilter(['id > 100']));

        self::assertCount(3, $response);
    }

    public function testGetDocumentsWithFilterCorrectFieldFormat(): void
    {
        $fields = ['the', 'clash'];

        $queryFields = (new DocumentsQuery())
            ->setFields($fields)
            ->setFilter(['id > 100'])
            ->toArray()['fields'];

        self::assertEquals($fields, $queryFields);
    }

    public function testGetDocumentsWithoutFilterCorrectFieldsFormat(): void
    {
        $fields = ['anti', 'flag'];

        $queryFields = (new DocumentsQuery())
            ->setFields($fields)
            ->toArray()['fields'];

        self::assertEquals(
            implode(',', $fields),
            $queryFields
        );
    }

    public function testGetDocumentsMessageHintException(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(0);
        $mockedException = new InvalidResponseBodyException($responseMock, 'Invalid response');

        try {
            $httpMock = $this->createMock(Http::class);
            $httpMock->expects(self::once())
                ->method('post')
                ->willThrowException($mockedException);

            $indexMock = new Indexes($httpMock, 'uid');
            $indexMock->getDocuments((new DocumentsQuery())->setFilter(['id > 1']));
        } catch (\Exception $ex) {
            $rethrowed = ApiException::rethrowWithHint($mockedException, 'getDocuments');

            self::assertSame($ex->getPrevious()->getMessage(), 'Invalid response');
            self::assertSame($ex->getMessage(), $rethrowed->getMessage());
        }
    }

    public function testUpdateDocumentsJson(): void
    {
        $index = $this->client->index('documentJson');

        $fileJson = fopen('./tests/datasets/small_movies.json', 'r');
        $documentJson = fread($fileJson, filesize('./tests/datasets/small_movies.json'));
        fclose($fileJson);

        $promise = $index->addDocumentsJson($documentJson);
        $index->waitForTask($promise['taskUid']);

        $replacement = [
            [
                'id' => 522681,
                'title' => 'No Escape Room',
            ],
        ];

        $promise = $index->updateDocumentsJson(json_encode($replacement));
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocument($replacement[0]['id']);

        self::assertSame($replacement[0]['id'], $response['id']);
        self::assertSame($replacement[0]['title'], $response['title']);

        $documents = $index->getDocuments();

        self::assertCount(20, $documents);
    }

    public function testUpdateDocumentsCsv(): void
    {
        $index = $this->client->index('documentCsv');

        $fileCsv = fopen('./tests/datasets/songs.csv', 'r');
        $documentCsv = fread($fileCsv, filesize('./tests/datasets/songs.csv'));
        fclose($fileCsv);

        $promise = $index->addDocumentsCsv($documentCsv);
        $index->waitForTask($promise['taskUid']);

        $replacement = 'id,title'.PHP_EOL;
        $replacement .= '888221515,Young folks'.PHP_EOL;

        $promise = $index->updateDocumentsCsv($replacement);
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocument(888221515);

        self::assertSame(888221515, (int) $response['id']);
        self::assertSame('Young folks', $response['title']);

        $documents = $index->getDocuments();

        self::assertSame(499, $documents->getTotal());
    }

    public function testUpdateDocumentsCsvWithDelimiter(): void
    {
        $index = $this->client->index('documentCsv');

        $csv = file_get_contents('./tests/datasets/songs.csv', true);

        $promise = $index->addDocumentsCsv($csv);
        $index->waitForTask($promise['taskUid']);

        $replacement = 'id|title'.PHP_EOL;
        $replacement .= '888221515|Young folks'.PHP_EOL;

        $promise = $index->updateDocumentsCsv($replacement, null, '|');
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocument(888221515);

        self::assertSame(888221515, (int) $response['id']);
        self::assertSame('Young folks', $response['title']);
    }

    public function testUpdateDocumentsNdjson(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $promise = $index->addDocumentsNdjson($documentNdJson);
        $index->waitForTask($promise['taskUid']);

        $replacement = json_encode(['id' => 412559401, 'title' => 'WASPTHOVEN']).PHP_EOL;
        $replacement .= json_encode(['id' => 70764404, 'artist' => 'Ailitp']).PHP_EOL;

        $promise = $index->updateDocumentsNdjson($replacement);
        $index->waitForTask($promise['taskUid']);

        $response = $index->getDocument(412559401);
        self::assertSame(412559401, (int) $response['id']);
        self::assertSame('WASPTHOVEN', $response['title']);

        $response = $index->getDocument(70764404);
        self::assertSame(70764404, (int) $response['id']);
        self::assertSame('Ailitp', $response['artist']);

        $documents = $index->getDocuments();

        self::assertSame(225, $documents->getTotal());
    }

    public function testUpdateDocumentsCsvInBatches(): void
    {
        $index = $this->client->index('documentCsv');

        $documentCsv = file_get_contents('./tests/datasets/songs.csv', true);

        $addPromise = $index->addDocumentsCsv($documentCsv);
        $index->waitForTask($addPromise['taskUid']);

        $replacement = 'id,title'.PHP_EOL;
        $replacement .= '888221515,Young folks'.PHP_EOL;
        $replacement .= '235115704,Mister Klein'.PHP_EOL;

        $promises = $index->updateDocumentsCsvInBatches($replacement, 1);
        self::assertCount(2, $promises);
        foreach ($promises as $promise) {
            $this->assertIsValidPromise($promise);
            $index->waitForTask($promise['taskUid']);
        }

        $response = $index->getDocument(888221515);
        self::assertSame(888221515, (int) $response['id']);
        self::assertSame('Young folks', $response['title']);

        $response = $index->getDocument(235115704);
        self::assertSame(235115704, (int) $response['id']);
        self::assertSame('Mister Klein', $response['title']);
    }

    public function testUpdateDocumentsCsvInBatchesWithDelimiter(): void
    {
        $matcher = self::atLeastOnce();
        $replacement = 'id;title'.PHP_EOL;
        $replacement .= '888221515;Young folks'.PHP_EOL;
        $replacement .= '235115704;Mister Klein'.PHP_EOL;

        $index = $this
            ->getMockBuilder('\Meilisearch\Endpoints\Indexes')
            ->onlyMethods(['updateDocumentsCsv'])
            ->disableOriginalConstructor()
            ->getMock();

        $index->expects($matcher)
              ->method('updateDocumentsCsv')
              ->willReturnCallback(function (string $param) use ($matcher): void {
                  // withConsecutive has no replacement https://github.com/sebastianbergmann/phpunit/issues/4026
                  switch ($matcher->numberOfInvocations()) {
                      case 1:
                          self::assertEquals($param, ["id;title\n888221515;Young folks", null, ';']);
                          break;
                      case 2:
                          self::assertEquals($param, ["id;title\n235115704;Mister Klein", null, ';']);
                          break;
                      default:
                          self::fail();
                  }
              })
              ->willReturn([], []);

        $index->updateDocumentsCsvInBatches($replacement, 1, null, ';');
    }

    public function testUpdateDocumentsNdjsonInBatches(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $addPromise = $index->addDocumentsNdjson($documentNdJson);
        $index->waitForTask($addPromise['taskUid']);

        $replacement = json_encode(['id' => 412559401, 'title' => 'WASPTHOVEN']).PHP_EOL;
        $replacement .= json_encode(['id' => 70764404, 'artist' => 'Ailitp']).PHP_EOL;

        $promises = $index->updateDocumentsNdjsonInBatches($replacement, 1);
        self::assertCount(2, $promises);
        foreach ($promises as $promise) {
            $this->assertIsValidPromise($promise);
            $index->waitForTask($promise['taskUid']);
        }

        $response = $index->getDocument(412559401);
        self::assertSame(412559401, (int) $response['id']);
        self::assertSame('WASPTHOVEN', $response['title']);

        $response = $index->getDocument(70764404);
        self::assertSame(70764404, (int) $response['id']);
        self::assertSame('Ailitp', $response['artist']);
    }

    /**
     * @dataProvider invalidDocumentIds
     */
    public function testFetchingDocumentWithInvalidId($documentId): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies-1'));

        $this->expectException(InvalidArgumentException::class);
        $index->getDocument($documentId);
    }

    /**
     * @dataProvider invalidDocumentIds
     */
    public function testDeletingDocumentWithInvalidId($documentId): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies-1'));

        $this->expectException(InvalidArgumentException::class);
        $index->deleteDocument($documentId);
    }

    public static function invalidDocumentIds(): array
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
            if ($document['id'] === $documentId) {
                return $document;
            }
        }

        return null;
    }
}
