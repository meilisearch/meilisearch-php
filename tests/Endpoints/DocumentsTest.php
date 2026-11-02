<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\DocumentsQuery;
use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\TaskDetails\DocumentAdditionOrUpdateDetails;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Endpoints\Index;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Exceptions\InvalidResponseBodyException;
use Meilisearch\Http\Client;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

final class DocumentsTest extends TestCase
{
    public function testAddDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS)->wait();

        $response = $index->getDocuments();
        self::assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddDocumentsWithCustomMetadata(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $task = $index->addDocuments(self::DOCUMENTS, customMetadata: 'add-documents-metadata');

        self::assertSame('add-documents-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('add-documents-metadata', $completedTask->getCustomMetadata());
    }

    public function testAddDocumentsInBatches(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $tasks = $index->addDocumentsInBatches(self::DOCUMENTS, 2);

        self::assertCount(4, $tasks);

        foreach ($tasks as $task) {
            $task->wait();
        }

        $response = $index->getDocuments();
        self::assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testAddDocumentsInBatchesWithCustomMetadata(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $tasks = $index->addDocumentsInBatches(self::DOCUMENTS, 2, customMetadata: 'add-documents-in-batches-metadata');

        self::assertCount(4, $tasks);

        foreach ($tasks as $task) {
            self::assertSame('add-documents-in-batches-metadata', $task->getCustomMetadata());

            $completedTask = $task->wait();

            self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
            self::assertSame('add-documents-in-batches-metadata', $completedTask->getCustomMetadata());
        }
    }

    public function testAddDocumentWithSpecialChars(): void
    {
        $documents = [
            ['id' => 60, 'title' => 'Sehr schön!', 'comment' => 'ßöüä'], // German
            ['id' => 61, 'title' => 'Très bien!', 'comment' => 'éèê'], // French
            ['id' => 62, 'title' => 'Очень красивый!', 'comment' => ''], // Russian
        ];

        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments($documents)->wait();

        $response = $index->getDocuments();
        self::assertCount(\count($documents), $response);

        $results = $response->getResults();
        foreach ($documents as $k => $document) {
            self::assertSame($document['title'], $results[$k]['title']);
            self::assertSame($document['comment'], $results[$k]['comment']);
        }
    }

    public function testAddDocumentsCsv(): void
    {
        $index = $this->client->index('documentCsv');

        $fileCsv = fopen('./tests/datasets/songs.csv', 'r');
        $documentCsv = fread($fileCsv, filesize('./tests/datasets/songs.csv'));
        fclose($fileCsv);

        $task = $index->addDocumentsCsv($documentCsv)->wait();

        self::assertSame(TaskStatus::Succeeded, $task->getStatus());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $details = $task->getDetails());
        self::assertNotSame(0, $details->receivedDocuments);

        $response = $index->getDocuments();
        self::assertCount(20, $response);
    }

    public function testAddDocumentsCsvWithCustomSeparator(): void
    {
        $index = $this->client->index('documentCsvWithCustomSeparator');

        $csv = file_get_contents('./tests/datasets/songs-custom-separator.csv', true);

        $task = $index->addDocumentsCsv($csv, null, '|')->wait();

        self::assertSame(TaskStatus::Succeeded, $task->getStatus());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $details = $task->getDetails());
        self::assertSame(6, $details->receivedDocuments);

        $documents = $index->getDocuments()->getResults();
        self::assertSame('Teenage Neon Jungle', $documents[4]['album']);
        self::assertSame('631152000', $documents[5]['released-timestamp']);
    }

    public function testAddDocumentsCsvWithCustomMetadata(): void
    {
        $index = $this->client->index('documentCsvWithCustomMetadata');

        $csv = file_get_contents('./tests/datasets/songs-custom-separator.csv');

        $task = $index->addDocumentsCsv($csv, null, '|', 'add-documents-csv-metadata');

        self::assertSame('add-documents-csv-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $details = $completedTask->getDetails());
        self::assertSame(6, $details->receivedDocuments);
        self::assertSame('add-documents-csv-metadata', $completedTask->getCustomMetadata());
    }

    public function testAddDocumentsJson(): void
    {
        $index = $this->client->index('documentJson');

        $fileJson = fopen('./tests/datasets/small_movies.json', 'r');
        $documentJson = fread($fileJson, filesize('./tests/datasets/small_movies.json'));
        fclose($fileJson);

        $task = $index->addDocumentsJson($documentJson)->wait();

        self::assertSame(TaskStatus::Succeeded, $task->getStatus());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $details = $task->getDetails());
        self::assertNotSame(0, $details->receivedDocuments);

        $response = $index->getDocuments();
        self::assertCount(20, $response);
    }

    public function testAddDocumentsJsonWithCustomMetadata(): void
    {
        $index = $this->client->index('documentJsonWithCustomMetadata');

        $documentJson = file_get_contents('./tests/datasets/small_movies.json');

        $task = $index->addDocumentsJson($documentJson, customMetadata: 'add-documents-json-metadata');

        self::assertSame('add-documents-json-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('add-documents-json-metadata', $completedTask->getCustomMetadata());
    }

    public function testAddDocumentsNdJson(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $task = $index->addDocumentsNdjson($documentNdJson)->wait();

        self::assertSame(TaskStatus::Succeeded, $task->getStatus());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $details = $task->getDetails());
        self::assertNotSame(0, $details->receivedDocuments);

        $response = $index->getDocuments();
        self::assertCount(20, $response);
    }

    public function testAddDocumentsNdjsonWithCustomMetadata(): void
    {
        $index = $this->client->index('documentNdJsonWithCustomMetadata');

        $documentNdJson = file_get_contents('./tests/datasets/songs.ndjson');

        $task = $index->addDocumentsNdjson($documentNdJson, customMetadata: 'add-documents-ndjson-metadata');

        self::assertSame('add-documents-ndjson-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('add-documents-ndjson-metadata', $completedTask->getCustomMetadata());
    }

    public function testCannotAddDocumentWhenJsonEncodingFails(): void
    {
        $documents = ["\xB1\x31"];

        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');
        // The argument type is wrong on purpose. addDocuments expects a list of
        // documents; this malformed string is what makes JSON encoding throw.
        $index->addDocuments($documents); // @phpstan-ignore argument.type
    }

    public function testGetSingleDocumentWithIntegerDocumentId(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $doc = $this->findDocumentWithId(self::DOCUMENTS, 4);
        $response = $index->getDocument($doc['id']);

        self::assertSame($doc['id'], $response['id']);
        self::assertSame($doc['title'], $response['title']);
    }

    public function testGetSingleDocumentWithFields(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $doc = $this->findDocumentWithId(self::DOCUMENTS, 4);
        $response = $index->getDocument($doc['id'], ['title']);

        self::assertSame($doc['title'], $response['title']);
        self::assertArrayNotHasKey('id', $response);
    }

    public function testGetSingleDocumentWithStringDocumentId(): void
    {
        $stringDocumentId = 'myUniqueId';

        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments([['id' => $stringDocumentId]])->wait();

        $response = $index->getDocument($stringDocumentId);

        self::assertSame($stringDocumentId, $response['id']);
    }

    public function testGetMultipleDocumentsByIds(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $documentIds = [1, 2];
        $response = $index->getDocuments((new DocumentsQuery())->setIds($documentIds));

        $returnedIds = array_column($response->getResults(), 'id');
        foreach ($documentIds as $id) {
            self::assertContains($id, $returnedIds);
        }
    }

    public function testReplaceDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $replacement = [
            'id' => 2,
            'title' => 'The Red And The Black',
        ];

        $index->addDocuments([$replacement])->wait();

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

        $index->addDocuments(self::DOCUMENTS)->wait();

        $replacement = [
            'id' => 456,
            'title' => 'The Little Prince',
        ];
        $index->updateDocuments([$replacement])->wait();

        $response = $index->getDocument($replacement['id']);

        self::assertSame($replacement['id'], $response['id']);
        self::assertSame($replacement['title'], $response['title']);
        self::assertArrayHasKey('comment', $response);

        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS), $response);
    }

    public function testUpdateDocumentsWithCustomMetadata(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $task = $index->updateDocuments(self::DOCUMENTS, customMetadata: 'update-documents-metadata');

        self::assertSame('update-documents-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('update-documents-metadata', $completedTask->getCustomMetadata());
    }

    public function testUpdateDocumentsInBatches(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $replacements = [
            ['id' => 1, 'title' => 'Alice Outside Wonderland'],
            ['id' => 123, 'title' => 'Pride and Prejudice and Zombies'],
            ['id' => 1344, 'title' => 'The Rabbit'],
            ['id' => 2, 'title' => 'Le Rouge et le Chocolate Noir'],
            ['id' => 4, 'title' => 'Harry Potter and the Half-Blood Princess'],
            ['id' => 456, 'title' => 'The Little Prince'],
        ];
        $tasks = $index->updateDocumentsInBatches($replacements, 4);
        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            $task->wait();
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

    public function testUpdateDocumentsInBatchesWithCustomMetadata(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $tasks = $index->updateDocumentsInBatches(self::DOCUMENTS, 2, customMetadata: 'update-documents-in-batches-metadata');

        self::assertCount(4, $tasks);

        foreach ($tasks as $task) {
            self::assertSame('update-documents-in-batches-metadata', $task->getCustomMetadata());

            $completedTask = $task->wait();

            self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
            self::assertSame('update-documents-in-batches-metadata', $completedTask->getCustomMetadata());
        }
    }

    public function testUpdateDocumentsByFunction(): void
    {
        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['editDocumentsByFunction' => true]);

        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $function = '
            if doc.id % context.modulo == 0 {
                doc.title = `kefir would read ${doc.title}`;
            };
            doc.remove("comment");
            doc.remove("genre");
        ';
        $index->updateDocumentsByFunction($function, ['context' => ['modulo' => 3]])->wait();

        $documents = $index->getDocuments()->getResults();

        $replacements = [
            [
                'id' => 123,
                'title' => 'kefir would read Pride and Prejudice',
            ],
            [
                'id' => 456,
                'title' => 'kefir would read Le Petit Prince',
            ],
            [
                'id' => 2,
                'title' => 'Le Rouge et le Noir',
            ],
            [
                'id' => 1,
                'title' => 'Alice In Wonderland',
            ],
            [
                'id' => 1344,
                'title' => 'kefir would read The Hobbit',
            ],
            [
                'id' => 4,
                'title' => 'Harry Potter and the Half-Blood Prince',
            ],
            [
                'id' => 42,
                'title' => 'kefir would read The Hitchhiker\'s Guide to the Galaxy',
            ],
        ];
        self::assertSame($replacements, $documents);
    }

    public function testUpdateDocumentsByFunctionWithCustomMetadata(): void
    {
        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['editDocumentsByFunction' => true]);

        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS)->wait();

        $task = $index->updateDocumentsByFunction(
            'doc.title = `${context.prefix} ${doc.title}`;',
            ['context' => ['prefix' => 'edited']],
            customMetadata: 'update-documents-by-function-metadata',
        );

        self::assertSame('update-documents-by-function-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('update-documents-by-function-metadata', $completedTask->getCustomMetadata());
        self::assertSame('edited Pride and Prejudice', $index->getDocument(123)['title']);
    }

    public function testAddDocumentsCsvInBatches(): void
    {
        $index = $this->client->index('documentCsv');

        $fileCsv = fopen('./tests/datasets/songs.csv', 'r');
        $documentCsv = fread($fileCsv, filesize('./tests/datasets/songs.csv'));
        fclose($fileCsv);

        // Total number of lines excluding header
        $total = \count(preg_split("/\r\n|\n|\r/", trim($documentCsv))) - 1;

        $tasks = $index->addDocumentsCsvInBatches($documentCsv, 250);

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            $task->wait();
        }

        $response = $index->getDocuments();
        self::assertSame($total, $response->getTotal());
    }

    public function testAddDocumentsCsvInBatchesWithDelimiter(): void
    {
        $documentCsv = 'id;title'.PHP_EOL;
        $documentCsv .= '888221515;Young folks'.PHP_EOL;
        $documentCsv .= '235115704;Mister Klein'.PHP_EOL;

        $http = $this->createMock(Http::class);
        $http->expects(self::exactly(2))
            ->method('post')
            ->willReturnCallback(function (string $path, $documents, $query, $contentType): array {
                static $invocation = 0;
                switch (++$invocation) {
                    case 1:
                        self::assertSame("id;title\n888221515;Young folks", $documents);
                        self::assertSame(['primaryKey' => null, 'csvDelimiter' => ';', 'customMetadata' => null], $query);
                        break;
                    case 2:
                        self::assertSame("id;title\n235115704;Mister Klein", $documents);
                        self::assertSame(['primaryKey' => null, 'csvDelimiter' => ';', 'customMetadata' => null], $query);
                        break;
                }

                return ['taskUid' => 1, 'indexUid' => 'index', 'status' => 'enqueued', 'type' => 'documentAdditionOrUpdate', 'enqueuedAt' => '2021-08-11T09:25:53.000000Z'];
            });

        $index = new Index($http, 'index');
        $tasks = $index->addDocumentsCsvInBatches($documentCsv, 1, null, ';');
        self::assertCount(2, $tasks);
    }

    public function testAddDocumentsCsvInBatchesWithCustomMetadata(): void
    {
        $index = $this->client->index('documentCsvInBatchesWithCustomMetadata');

        $csv = file_get_contents('./tests/datasets/songs-custom-separator.csv');

        $tasks = $index->addDocumentsCsvInBatches($csv, 3, null, '|', 'add-documents-csv-in-batches-metadata');

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            self::assertSame('add-documents-csv-in-batches-metadata', $task->getCustomMetadata());

            $completedTask = $task->wait();

            self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
            self::assertSame('add-documents-csv-in-batches-metadata', $completedTask->getCustomMetadata());
        }
    }

    public function testAddDocumentsNdjsonInBatches(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $total = \count(preg_split("/\r\n|\n|\r/", trim($documentNdJson)));

        $tasks = $index->addDocumentsNdjsonInBatches($documentNdJson, 150);

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            $task->wait();
        }

        $response = $index->getDocuments();
        self::assertSame($total, $response->getTotal());
    }

    public function testAddDocumentsNdjsonInBatchesWithCustomMetadata(): void
    {
        $index = $this->client->index('documentNdJsonInBatchesWithCustomMetadata');

        $documentNdJson = file_get_contents('./tests/datasets/songs.ndjson');

        $tasks = $index->addDocumentsNdjsonInBatches($documentNdJson, 150, customMetadata: 'add-documents-ndjson-in-batches-metadata');

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            self::assertSame('add-documents-ndjson-in-batches-metadata', $task->getCustomMetadata());

            $completedTask = $task->wait();

            self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
            self::assertSame('add-documents-ndjson-in-batches-metadata', $completedTask->getCustomMetadata());
        }
    }

    public function testAddWithUpdateDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $document = [
            'id' => 9,
            'title' => '1984',
        ];

        $index->updateDocuments([$document])->wait();

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

        $index->addDocuments(self::DOCUMENTS)->wait();

        $documentId = 9;

        $index->deleteDocument($documentId)->wait();

        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS), $response);
        self::assertNull($this->findDocumentWithId($response, $documentId));
    }

    public function testDeleteSingleExistingDocumentWithDocumentIdAsInteger(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $documentId = 123;
        $index->deleteDocument($documentId)->wait();

        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS) - 1, $response);
        self::assertNull($this->findDocumentWithId($response, $documentId));
    }

    public function testDeleteSingleExistingDocumentWithDocumentIdAsString(): void
    {
        $stringDocumentId = 'myUniqueId';

        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments([['id' => $stringDocumentId]])->wait();

        $index->deleteDocument($stringDocumentId)->wait();

        $response = $index->getDocuments();

        self::assertCount(0, $response);
    }

    public function testDeleteDocumentWithCustomMetadata(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS)->wait();

        $task = $index->deleteDocument(123, customMetadata: 'delete-document-metadata');

        self::assertSame('delete-document-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('delete-document-metadata', $completedTask->getCustomMetadata());
        self::assertNull($this->findDocumentWithId($index->getDocuments(), 123));
    }

    public function testDeleteMultipleDocumentsWithDocumentIdAsInteger(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $documentIds = [1, 2];
        $index->deleteDocumentsByIds($documentIds)->wait();

        $response = $index->getDocuments();

        self::assertCount(\count(self::DOCUMENTS) - 2, $response);
        self::assertNull($this->findDocumentWithId($response, $documentIds[0]));
        self::assertNull($this->findDocumentWithId($response, $documentIds[1]));
    }

    public function testDeleteDocumentsByIdsWithCustomMetadata(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS)->wait();

        $task = $index->deleteDocumentsByIds([1, 2], customMetadata: 'delete-documents-by-ids-metadata');

        self::assertSame('delete-documents-by-ids-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('delete-documents-by-ids-metadata', $completedTask->getCustomMetadata());
        self::assertCount(\count(self::DOCUMENTS) - 2, $index->getDocuments());
    }

    public function testDeleteMultipleDocumentsWithFilter(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS);
        $index->updateFilterableAttributes(['id']);

        $index->deleteDocumentsByFilter(['id > 0'])->wait();

        $response = $index->getDocuments();

        self::assertCount(0, $response);
    }

    public function testDeleteDocumentsByFilterWithCustomMetadata(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS)->wait();
        $index->updateFilterableAttributes(['id'])->wait();
        self::assertCount(\count(self::DOCUMENTS), $index->getDocuments());

        $task = $index->deleteDocumentsByFilter(['id > 0'], customMetadata: 'delete-documents-by-filter-metadata');

        self::assertSame('delete-documents-by-filter-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('delete-documents-by-filter-metadata', $completedTask->getCustomMetadata());
        self::assertCount(0, $index->getDocuments());
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

            $indexMock = new Index($httpMock, 'uid');
            $indexMock->deleteDocumentsByFilter(['id > 0']);
        } catch (\Exception $ex) {
            $rethrowed = ApiException::rethrowWithHint($mockedException, 'deleteDocumentsByFilter');

            self::assertSame('Invalid response', $ex->getPrevious()->getMessage());
            self::assertSame($rethrowed->getMessage(), $ex->getMessage());
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

        $index->addDocuments($documents)->wait();

        $index->deleteDocumentsByIds(['myUniqueId1', 'myUniqueId3'])->wait();

        $response = $index->getDocuments();
        self::assertCount(1, $response);
        self::assertSame([['id' => 'myUniqueId2']], $response->getResults());
    }

    public function testDeleteAllDocuments(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->addDocuments(self::DOCUMENTS)->wait();

        $index->deleteAllDocuments()->wait();

        $response = $index->getDocuments();

        self::assertCount(0, $response);
    }

    public function testDeleteAllDocumentsWithCustomMetadata(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS)->wait();

        $task = $index->deleteAllDocuments(customMetadata: 'delete-all-documents-metadata');

        self::assertSame('delete-all-documents-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('delete-all-documents-metadata', $completedTask->getCustomMetadata());
        self::assertCount(0, $index->getDocuments());
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

        $index->addDocuments($documents, 'unique')->wait();

        self::assertSame('unique', $index->getPrimaryKey());
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
        $index->updateDocuments($documents, 'unique')->wait();

        self::assertSame('unique', $index->getPrimaryKey());
        self::assertCount(1, $index->getDocuments());
    }

    public function testGetDocumentsWithPagination(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->addDocuments(self::DOCUMENTS)->wait();

        $response = $index->getDocuments((new DocumentsQuery())->setLimit(3));

        self::assertCount(3, $response);
    }

    public function testGetDocumentsWithFilter(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->updateFilterableAttributes(['genre', 'id']);
        $index->addDocuments(self::DOCUMENTS)->wait();

        $response = $index->getDocuments((new DocumentsQuery())->setFilter(['id > 100']));

        self::assertCount(3, $response);
    }

    public function testGetDocumentsWithSort(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->updateSortableAttributes(['id', 'genre']);
        $index->updateFilterableAttributes(['id', 'genre']);
        $index->addDocuments(self::DOCUMENTS)->wait();

        $response = $index->getDocuments((new DocumentsQuery())->setSort(['genre:desc', 'id:asc']));
        self::assertSame(2, $response->getResults()[0]['id']);

        $response = $index->getDocuments((new DocumentsQuery())->setSort(['genre:desc', 'id:desc']));
        self::assertSame(1344, $response->getResults()[0]['id']);
    }

    public function testGetDocumentsWithFiltersFieldsAndSort(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));
        $index->updateSortableAttributes(['id', 'genre']);
        $index->updateFilterableAttributes(['id', 'genre']);
        $index->addDocuments(self::DOCUMENTS)->wait();

        $query = (new DocumentsQuery())
            ->setSort(['genre:desc', 'id:asc'])
            ->setFields(['id', 'title'])
            ->setFilter(['id > 2']);
        $response = $index->getDocuments($query);
        self::assertSame(123, $response->getResults()[0]['id']);
        self::assertSame(['id', 'title'], array_keys($response->getResults()[0]));
    }

    public function testGetDocumentsWithFilterCorrectFieldFormat(): void
    {
        $fields = ['the', 'clash'];

        $queryFields = (new DocumentsQuery())
            ->setFields($fields)
            ->setFilter(['id > 100'])
            ->toArray()['fields'];

        self::assertSame($fields, $queryFields);
    }

    public function testGetDocumentsWithVector(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('movies'));

        $index->updateEmbedders(['manual' => ['source' => 'userProvided', 'dimensions' => 3]])->wait();
        $index->updateDocuments(self::VECTOR_MOVIES)->wait();

        $response = $index->getDocuments(new DocumentsQuery());
        self::assertArrayNotHasKey('_vectors', $response->getResults()[0]);
        $query = (new DocumentsQuery())->setRetrieveVectors(true);
        $response = $index->getDocuments($query);
        self::assertArrayHasKey('_vectors', $response->getResults()[0]);

        self::assertCount(5, $response);
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

            $indexMock = new Index($httpMock, 'uid');
            $indexMock->getDocuments((new DocumentsQuery())->setFilter(['id > 1']));
        } catch (\Exception $ex) {
            $rethrowed = ApiException::rethrowWithHint($mockedException, 'getDocuments');

            self::assertSame('Invalid response', $ex->getPrevious()->getMessage());
            self::assertSame($rethrowed->getMessage(), $ex->getMessage());
        }
    }

    public function testUpdateDocumentsJson(): void
    {
        $index = $this->client->index('documentJson');

        $fileJson = fopen('./tests/datasets/small_movies.json', 'r');
        $documentJson = fread($fileJson, filesize('./tests/datasets/small_movies.json'));
        fclose($fileJson);

        $index->addDocumentsJson($documentJson)->wait();

        $replacement = [
            [
                'id' => 522681,
                'title' => 'No Escape Room',
            ],
        ];

        $index->updateDocumentsJson(json_encode($replacement))->wait();

        $response = $index->getDocument($replacement[0]['id']);

        self::assertSame($replacement[0]['id'], $response['id']);
        self::assertSame($replacement[0]['title'], $response['title']);

        $documents = $index->getDocuments();

        self::assertCount(20, $documents);
    }

    public function testUpdateDocumentsJsonWithCustomMetadata(): void
    {
        $index = $this->client->index('documentJsonUpdateWithCustomMetadata');

        $documentJson = file_get_contents('./tests/datasets/small_movies.json');

        $task = $index->updateDocumentsJson($documentJson, customMetadata: 'update-documents-json-metadata');

        self::assertSame('update-documents-json-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('update-documents-json-metadata', $completedTask->getCustomMetadata());
    }

    public function testUpdateDocumentsCsv(): void
    {
        $index = $this->client->index('documentCsv');

        $fileCsv = fopen('./tests/datasets/songs.csv', 'r');
        $documentCsv = fread($fileCsv, filesize('./tests/datasets/songs.csv'));
        fclose($fileCsv);

        $index->addDocumentsCsv($documentCsv)->wait();

        $replacement = 'id,title'.PHP_EOL;
        $replacement .= '888221515,Young folks'.PHP_EOL;

        $index->updateDocumentsCsv($replacement)->wait();

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

        $index->addDocumentsCsv($csv)->wait();

        $replacement = 'id|title'.PHP_EOL;
        $replacement .= '888221515|Young folks'.PHP_EOL;

        $index->updateDocumentsCsv($replacement, null, '|')->wait();

        $response = $index->getDocument(888221515);

        self::assertSame(888221515, (int) $response['id']);
        self::assertSame('Young folks', $response['title']);
    }

    public function testUpdateDocumentsCsvWithCustomMetadata(): void
    {
        $index = $this->client->index('documentCsvUpdateWithCustomMetadata');

        $csv = file_get_contents('./tests/datasets/songs-custom-separator.csv');

        $task = $index->updateDocumentsCsv($csv, null, '|', 'update-documents-csv-metadata');

        self::assertSame('update-documents-csv-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertInstanceOf(DocumentAdditionOrUpdateDetails::class, $details = $completedTask->getDetails());
        self::assertSame(6, $details->receivedDocuments);
        self::assertSame('update-documents-csv-metadata', $completedTask->getCustomMetadata());
    }

    public function testUpdateDocumentsNdjson(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $index->addDocumentsNdjson($documentNdJson)->wait();

        $replacement = json_encode(['id' => 412559401, 'title' => 'WASPTHOVEN']).PHP_EOL;
        $replacement .= json_encode(['id' => 70764404, 'artist' => 'Ailitp']).PHP_EOL;

        $index->updateDocumentsNdjson($replacement)->wait();

        $response = $index->getDocument(412559401);
        self::assertSame(412559401, (int) $response['id']);
        self::assertSame('WASPTHOVEN', $response['title']);

        $response = $index->getDocument(70764404);
        self::assertSame(70764404, (int) $response['id']);
        self::assertSame('Ailitp', $response['artist']);

        $documents = $index->getDocuments();

        self::assertSame(225, $documents->getTotal());
    }

    public function testUpdateDocumentsNdjsonWithCustomMetadata(): void
    {
        $index = $this->client->index('documentNdJsonUpdateWithCustomMetadata');

        $documentNdJson = file_get_contents('./tests/datasets/songs.ndjson');

        $task = $index->updateDocumentsNdjson($documentNdJson, customMetadata: 'update-documents-ndjson-metadata');

        self::assertSame('update-documents-ndjson-metadata', $task->getCustomMetadata());

        $completedTask = $task->wait();

        self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
        self::assertSame('update-documents-ndjson-metadata', $completedTask->getCustomMetadata());
    }

    public function testUpdateDocumentsCsvInBatches(): void
    {
        $index = $this->client->index('documentCsv');

        $documentCsv = file_get_contents('./tests/datasets/songs.csv', true);

        $index->addDocumentsCsv($documentCsv)->wait();

        $replacement = 'id,title'.PHP_EOL;
        $replacement .= '888221515,Young folks'.PHP_EOL;
        $replacement .= '235115704,Mister Klein'.PHP_EOL;

        $tasks = $index->updateDocumentsCsvInBatches($replacement, 1);
        self::assertCount(2, $tasks);
        foreach ($tasks as $task) {
            $task->wait();
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
        $replacement = 'id;title'.PHP_EOL;
        $replacement .= '888221515;Young folks'.PHP_EOL;
        $replacement .= '235115704;Mister Klein'.PHP_EOL;

        $http = $this->createMock(Http::class);
        $http->expects(self::exactly(2))
            ->method('put')
            ->willReturnCallback(function (string $path, $documents, $query, $contentType): array {
                static $invocation = 0;
                switch (++$invocation) {
                    case 1:
                        self::assertSame("id;title\n888221515;Young folks", $documents);
                        self::assertSame(['primaryKey' => null, 'csvDelimiter' => ';', 'customMetadata' => null], $query);
                        break;
                    case 2:
                        self::assertSame("id;title\n235115704;Mister Klein", $documents);
                        self::assertSame(['primaryKey' => null, 'csvDelimiter' => ';', 'customMetadata' => null], $query);
                        break;
                }

                return ['taskUid' => 1, 'indexUid' => 'index', 'status' => 'enqueued', 'type' => 'documentAdditionOrUpdate', 'enqueuedAt' => '2021-08-11T09:25:53.000000Z'];
            });

        $index = new Index($http, 'index');
        $tasks = $index->updateDocumentsCsvInBatches($replacement, 1, null, ';');
        self::assertCount(2, $tasks);
    }

    public function testUpdateDocumentsCsvInBatchesWithCustomMetadata(): void
    {
        $index = $this->client->index('documentCsvUpdateInBatchesWithCustomMetadata');

        $csv = file_get_contents('./tests/datasets/songs-custom-separator.csv');

        $tasks = $index->updateDocumentsCsvInBatches($csv, 3, null, '|', 'update-documents-csv-in-batches-metadata');

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            self::assertSame('update-documents-csv-in-batches-metadata', $task->getCustomMetadata());

            $completedTask = $task->wait();

            self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
            self::assertSame('update-documents-csv-in-batches-metadata', $completedTask->getCustomMetadata());
        }
    }

    public function testUpdateDocumentsNdjsonInBatches(): void
    {
        $index = $this->client->index('documentNdJson');

        $fileNdJson = fopen('./tests/datasets/songs.ndjson', 'r');
        $documentNdJson = fread($fileNdJson, filesize('./tests/datasets/songs.ndjson'));
        fclose($fileNdJson);

        $index->addDocumentsNdjson($documentNdJson)->wait();

        $replacement = json_encode(['id' => 412559401, 'title' => 'WASPTHOVEN']).PHP_EOL;
        $replacement .= json_encode(['id' => 70764404, 'artist' => 'Ailitp']).PHP_EOL;

        $tasks = $index->updateDocumentsNdjsonInBatches($replacement, 1);
        self::assertCount(2, $tasks);
        foreach ($tasks as $task) {
            $task->wait();
        }

        $response = $index->getDocument(412559401);
        self::assertSame(412559401, (int) $response['id']);
        self::assertSame('WASPTHOVEN', $response['title']);

        $response = $index->getDocument(70764404);
        self::assertSame(70764404, (int) $response['id']);
        self::assertSame('Ailitp', $response['artist']);
    }

    public function testUpdateDocumentsNdjsonInBatchesWithCustomMetadata(): void
    {
        $index = $this->client->index('documentNdJsonUpdateInBatchesWithCustomMetadata');

        $documentNdJson = file_get_contents('./tests/datasets/songs.ndjson');

        $tasks = $index->updateDocumentsNdjsonInBatches($documentNdJson, 150, customMetadata: 'update-documents-ndjson-in-batches-metadata');

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            self::assertSame('update-documents-ndjson-in-batches-metadata', $task->getCustomMetadata());

            $completedTask = $task->wait();

            self::assertSame(TaskStatus::Succeeded, $completedTask->getStatus());
            self::assertSame('update-documents-ndjson-in-batches-metadata', $completedTask->getCustomMetadata());
        }
    }

    private function findDocumentWithId($documents, $documentId): ?array
    {
        foreach ($documents as $document) {
            if ($document['id'] === $documentId) {
                return $document;
            }
        }

        return null;
    }
}
