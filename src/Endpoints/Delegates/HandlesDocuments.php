<?php

namespace MeiliSearch\Endpoints\Delegates;

use MeiliSearch\Contracts\Http;

/**
 * @property Http http
 */
trait HandlesDocuments
{
    public function getDocument(string $documentId)
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/documents/'.$documentId);
    }

    public function getDocuments(array $query = [])
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/documents', $query);
    }

    public function addDocuments(array $documents, string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function updateDocuments(array $documents, string $primaryKey = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function deleteAllDocuments(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents');
    }

    public function deleteDocument(string $documentId): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents/'.$documentId);
    }

    public function deleteDocuments(array $documents): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents/delete-batch', $documents);
    }
}
