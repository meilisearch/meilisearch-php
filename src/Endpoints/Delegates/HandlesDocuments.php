<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints\Delegates;

use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\InvalidArgumentException;

/**
 * @property Http http
 */
trait HandlesDocuments
{
    public function getDocument($documentId)
    {
        $this->assertValidDocumentId($documentId);

        return $this->http->get(self::PATH.'/'.$this->uid.'/documents/'.$documentId);
    }

    public function getDocuments(array $query = [])
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/documents', $query);
    }

    public function addDocuments(array $documents, ?string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function updateDocuments(array $documents, ?string $primaryKey = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
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
}
