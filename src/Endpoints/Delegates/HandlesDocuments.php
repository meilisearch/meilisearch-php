<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints\Delegates;

use Generator;
use MeiliSearch\Contracts\DocumentsQuery;
use MeiliSearch\Contracts\DocumentsResults;
use MeiliSearch\Exceptions\InvalidArgumentException;

trait HandlesDocuments
{
    public function getDocument($documentId, array $fields = null)
    {
        $this->assertValidDocumentId($documentId);
        $query = isset($fields) ? ['fields' => implode(',', $fields)] : [];

        return $this->http->get(self::PATH.'/'.$this->uid.'/documents/'.$documentId, $query);
    }

    public function getDocuments(DocumentsQuery $options = null): DocumentsResults
    {
        $query = isset($options) ? $options->toArray() : [];
        $response = $this->http->get(self::PATH.'/'.$this->uid.'/documents', $query);

        return new DocumentsResults($response);
    }

    public function addDocuments(array $documents, ?string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function addDocumentsInBatches(array $documents, ?int $batchSize = 1000, ?string $primaryKey = null)
    {
        $promises = [];
        foreach (self::batch($documents, $batchSize) as $batch) {
            $promises[] = $this->addDocuments($batch, $primaryKey);
        }

        return $promises;
    }

    public function addDocumentsJson(string $documents, ?string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/json');
    }

    public function addDocumentsNdjson(string $documents, ?string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'application/x-ndjson');
    }

    public function addDocumentsCsv(string $documents, ?string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey], 'text/csv');
    }

    public function updateDocuments(array $documents, ?string $primaryKey = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function updateDocumentsInBatches(array $documents, ?int $batchSize = 1000, ?string $primaryKey = null)
    {
        $promises = [];
        foreach (self::batch($documents, $batchSize) as $batch) {
            $promises[] = $this->updateDocuments($documents, $primaryKey);
        }

        return $promises;
    }

    public function deleteAllDocuments(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents');
    }

    public function deleteDocument($documentId): array
    {
        $this->assertValidDocumentId($documentId);

        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents/'.$documentId);
    }

    public function deleteDocuments(array $documents): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents/delete-batch', $documents);
    }

    private function assertValidDocumentId($documentId): void
    {
        if (!\is_string($documentId) && !\is_int($documentId)) {
            throw InvalidArgumentException::invalidType('documentId', ['string', 'int']);
        }

        if (\is_string($documentId) && '' === trim($documentId)) {
            throw InvalidArgumentException::emptyArgument('documentId');
        }
    }

    private static function batch(array $documents, int $batchSize): Generator
    {
        $batches = array_chunk($documents, $batchSize);
        foreach ($batches as $batch) {
            yield $batch;
        }
    }
}
