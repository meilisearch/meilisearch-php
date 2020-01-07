<?php

namespace MeiliSearch;

class Index extends HTTPRequest
{
    private $uid = null;

    public function __construct($uid, $url, $api_key = null)
    {
        $this->uid = $uid;
        parent::__construct($url, $api_key);
    }

    public function getName()
    {
        return $this->show()['name'];
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function show()
    {
        return $this->httpGet('/indexes/'.$this->uid);
    }

    public function updateName($name)
    {
        $body = [
            'name' => $name,
        ];

        return $this->httpPut('/indexes/'.$this->uid, $body);
    }

    public function delete()
    {
        return $this->httpDelete('/indexes/'.$this->uid);
    }

    // Documents

    public function getDocument($document_id)
    {
        return $this->httpGet('/indexes/'.$this->uid.'/documents/'.$document_id);
    }

    public function getDocuments($options = null)
    {
        return $this->httpGet('/indexes/'.$this->uid.'/documents', $options);
    }

    public function addOrReplaceDocuments($documents)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/documents', $documents);
    }

    public function addOrUpdateDocuments($documents)
    {
        return $this->httpPut('/indexes/'.$this->uid.'/documents', $documents);
    }

    public function deleteAllDocuments()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/documents');
    }

    public function deleteDocument($document_id)
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/documents/'.$document_id);
    }

    public function deleteDocuments($documents)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/documents/delete', $documents);
    }

    // Updates

    public function getUpdateStatus($update_id)
    {
        return $this->httpGet('/indexes/'.$this->uid.'/updates/'.$update_id);
    }

    public function getAllUpdateStatus()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/updates');
    }

    // Search

    public function search($query, $options = null)
    {
        if (isset($options)) {
            $paramters = array_merge(['q' => $query], $options);
        } else {
            $paramters = ['q' => $query];
        }

        return $this->httpGet('/indexes/'.$this->uid.'/search', $paramters);
    }
}
