<?php

namespace MeiliSearch\Endpoints\Delegates;

trait handlesDocuments
{
    // Documents

    public function getDocument($document_id)
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/documents/'.$document_id);
    }

    public function getDocuments($query = [])
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/documents', $query);
    }

    public function addDocuments(array $documents, $primaryKey = null)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function updateDocuments($documents, $primary_key = null)
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/documents', $documents, ['primaryKey' => $primary_key]);
    }

    public function deleteAllDocuments()
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents');
    }

    public function deleteDocument($document_id)
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/documents/'.$document_id);
    }

    public function deleteDocuments($documents)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/documents/delete-batch', $documents);
    }
}
