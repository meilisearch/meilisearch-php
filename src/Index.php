<?php

declare(strict_types=1);

namespace MeiliSearch;

use MeiliSearch\Exceptions\TimeOutException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Index extends HTTPRequest
{
    private $uid = null;

    public function __construct($uid, $url, $api_key = null, ClientInterface $httpClient = null, RequestFactoryInterface $requestFactory = null, StreamFactoryInterface $streamFactory = null)
    {
        $this->uid = $uid;
        parent::__construct($url, $api_key, $httpClient, $requestFactory, $streamFactory);
    }

    public function getPrimaryKey()
    {
        return $this->show()['primaryKey'];
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function show()
    {
        return $this->httpGet('/indexes/'.$this->uid);
    }

    public function update($body)
    {
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

    public function addDocuments($documents, $primary_key = null)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/documents', $documents, ['primaryKey' => $primary_key]);
    }

    public function updateDocuments($documents, $primary_key = null)
    {
        return $this->httpPut('/indexes/'.$this->uid.'/documents', $documents, ['primaryKey' => $primary_key]);
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
        return $this->httpPost('/indexes/'.$this->uid.'/documents/delete-batch', $documents);
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

    public function waitForPendingUpdate($update_id, $timeout_in_ms = 5000, $interval_in_ms = 50)
    {
        $timeout_temp = 0;
        while ($timeout_in_ms > $timeout_temp) {
            $res = $this->getUpdateStatus($update_id);
            if ('enqueued' != $res['status']) {
                return $res;
            }
            $timeout_temp += $interval_in_ms;
            usleep(1000 * $interval_in_ms);
        }
        throw new TimeOutException();
    }

    // Search

    public function search($query, array $options = [])
    {
        $parameters = array_merge(
            ['q' => $query],
            $this->parseOptions($options)
        );

        return $this->httpGet('/indexes/'.$this->uid.'/search', $parameters);
    }

    // Stats

    public function stats()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/stats');
    }

    // Settings - Global

    public function getSettings()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings');
    }

    public function updateSettings($settings)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings', $settings);
    }

    public function resetSettings()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/settings');
    }

    // Settings - Ranking rules

    public function getRankingRules()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings/ranking-rules');
    }

    public function updateRankingRules($ranking_rules)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings/ranking-rules', $ranking_rules);
    }

    public function resetRankingRules()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/settings/ranking-rules');
    }

    // Settings - Distinct attribute

    public function getDistinctAttribute()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings/distinct-attribute');
    }

    public function updateDistinctAttribute($distinct_attribute)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings/distinct-attribute', $distinct_attribute);
    }

    public function resetDistinctAttribute()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/settings/distinct-attribute');
    }

    // Settings - Searchable attributes

    public function getSearchableAttributes()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings/searchable-attributes');
    }

    public function updateSearchableAttributes($searchable_attributes)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings/searchable-attributes', $searchable_attributes);
    }

    public function resetSearchableAttributes()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/settings/searchable-attributes');
    }

    // Settings - Displayed attributes

    public function getDisplayedAttributes()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings/displayed-attributes');
    }

    public function updateDisplayedAttributes($displayed_attributes)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings/displayed-attributes', $displayed_attributes);
    }

    public function resetDisplayedAttributes()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/settings/displayed-attributes');
    }

    // Settings - Stop-words

    public function getStopWords()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings/stop-words');
    }

    public function updateStopWords($stop_words)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings/stop-words', $stop_words);
    }

    public function resetStopWords()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/settings/stop-words');
    }

    // Settings - Synonyms

    public function getSynonyms()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings/synonyms');
    }

    public function updateSynonyms($synonyms)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings/synonyms', $synonyms);
    }

    public function resetSynonyms()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/settings/synonyms');
    }

    // Settings - AcceptNewFields

    public function getAcceptNewFields()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings/accept-new-fields');
    }

    public function updateAcceptNewFields($accept_new_fields)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings/accept-new-fields', $accept_new_fields);
    }

    // Settings - Attributes for faceting

    public function getAttributesForFaceting()
    {
        return $this->httpGet('/indexes/'.$this->uid.'/settings/attributes-for-faceting');
    }

    public function updateAttributesForFaceting($attributes_for_faceting)
    {
        return $this->httpPost('/indexes/'.$this->uid.'/settings/attributes-for-faceting', $attributes_for_faceting);
    }

    public function resetAttributesForFaceting()
    {
        return $this->httpDelete('/indexes/'.$this->uid.'/settings/attributes-for-faceting');
    }

    // PRIVATE

    private function parseOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if ('facetsDistribution' === $key || 'facetFilters' === $key) {
                $options[$key] = json_encode($value);
            } elseif (\is_array($value)) {
                $options[$key] = implode(',', $value);
            }
        }

        return $options;
    }
}
