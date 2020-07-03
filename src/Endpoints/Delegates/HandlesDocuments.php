<?php

namespace MeiliSearch\Endpoints\Delegates;

trait HandlesDocuments
{
    public function getDocument(string $document_id)
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/documents/'.$document_id);
    }

    public function getDocuments(array $query = [])
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/documents', $query);
    }

    public function addDocuments(array $documents, string $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function updateDocuments(array $documents, string $primary_key = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primary_key]);
    }

    public function deleteAllDocuments(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents');
    }

    public function deleteDocument(string $document_id): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents/'.$document_id);
    }

    public function deleteDocuments(array $documents): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents/delete-batch', $documents);
    }
}
