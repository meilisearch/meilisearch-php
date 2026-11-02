<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\Http;
use Meilisearch\Endpoints\Index;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DocumentsEndpointTest extends TestCase
{
    /**
     * @param 'post'|'put'|'delete'  $httpMethod
     * @param \Closure(Index): mixed $call
     */
    #[DataProvider('provideDocumentWritesWithoutCustomMetadata')]
    public function testCustomMetadataIsOmittedFromQueryWhenNotProvided(string $httpMethod, int $queryArgumentPosition, \Closure $call): void
    {
        $http = $this->createMock(Http::class);
        $http->expects(self::once())
            ->method($httpMethod)
            ->willReturnCallback(static function (mixed ...$arguments) use ($queryArgumentPosition): array {
                self::assertArrayNotHasKey('customMetadata', $arguments[$queryArgumentPosition] ?? []);

                return ['taskUid' => 1, 'indexUid' => 'index', 'status' => 'enqueued', 'type' => 'documentAdditionOrUpdate', 'enqueuedAt' => '2021-08-11T09:25:53.000000Z'];
            });

        $call(new Index($http, 'index'));
    }

    /**
     * @return iterable<string, array{'post'|'put'|'delete', int, \Closure(Index): mixed}>
     */
    public static function provideDocumentWritesWithoutCustomMetadata(): iterable
    {
        yield 'addDocuments' => ['post', 2, static fn (Index $index) => $index->addDocuments([['id' => 1]])];
        yield 'addDocumentsJson' => ['post', 2, static fn (Index $index) => $index->addDocumentsJson('[{"id":1}]')];
        yield 'addDocumentsCsv' => ['post', 2, static fn (Index $index) => $index->addDocumentsCsv("id\n1")];
        yield 'addDocumentsNdjson' => ['post', 2, static fn (Index $index) => $index->addDocumentsNdjson('{"id":1}')];
        yield 'updateDocuments' => ['put', 2, static fn (Index $index) => $index->updateDocuments([['id' => 1]])];
        yield 'updateDocumentsJson' => ['put', 2, static fn (Index $index) => $index->updateDocumentsJson('[{"id":1}]')];
        yield 'updateDocumentsCsv' => ['put', 2, static fn (Index $index) => $index->updateDocumentsCsv("id\n1")];
        yield 'updateDocumentsNdjson' => ['put', 2, static fn (Index $index) => $index->updateDocumentsNdjson('{"id":1}')];
        yield 'updateDocumentsByFunction' => ['post', 2, static fn (Index $index) => $index->updateDocumentsByFunction('doc')];
        yield 'deleteDocument' => ['delete', 1, static fn (Index $index) => $index->deleteDocument(1)];
        yield 'deleteDocumentsByIds' => ['post', 2, static fn (Index $index) => $index->deleteDocumentsByIds([1])];
        yield 'deleteDocumentsByFilter' => ['post', 2, static fn (Index $index) => $index->deleteDocumentsByFilter('id = 1')];
        yield 'deleteAllDocuments' => ['delete', 1, static fn (Index $index) => $index->deleteAllDocuments()];
    }
}
