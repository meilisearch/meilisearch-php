<?php

namespace MeiliSearch;

use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Exceptions\TimeOutException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Index extends Endpoint
{
    const PATH = '/indexes';

    private $uid;

    /**
     * @var Http
     */
    private $http;

    public function __construct(Http $http, $uid = null)
    {
        $this->uid = $uid;
        $this->http = $http;
    }

    /**
     * @param string $uid
     * @param array $options
     * @return $this
     * @throws Exceptions\HTTPRequestException
     */
    public function create(string $uid, $options = [])
    {
        $options['uid'] = $uid;

        $response = $this->http->post(self::PATH, $options);

        return new self($this->http, $response['uid']);
    }

    public function all()
    {
        $indexes = [];

        foreach ($this->http->get(self::PATH) as $index) {
            $indexes[] = new self($this->http, $index['uid']);
        }

        return $indexes;
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
        return $this->http->get(self::PATH . '/' . $this->uid);
    }

    public function update($body)
    {
        return  $this->http->put(self::PATH. '/' . $this->uid, $body);
    }

    public function delete()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid);
    }

    // Documents

    public function getDocument($document_id)
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/documents/'.$document_id);
    }

    public function getDocuments($query = [])
    {
        return $this->http->get(self::PATH . '/' . $this->uid . '/documents', $query);
    }

    public function addDocuments(array $documents, $primaryKey = null)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/documents', $documents, ['primaryKey' => $primaryKey]);
    }

    public function updateDocuments($documents, $primary_key = null)
    {
        return $this->http->put(self::PATH . '/' .$this->uid.'/documents', $documents, ['primaryKey' => $primary_key]);
    }

    public function deleteAllDocuments()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/documents');
    }

    public function deleteDocument($document_id)
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/documents/'.$document_id);
    }

    public function deleteDocuments($documents)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/documents/delete-batch', $documents);
    }

    // Updates

    public function getUpdateStatus($update_id)
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/updates/'.$update_id);
    }

    public function getAllUpdateStatus()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/updates');
    }

    /**
     * @param $update_id
     * @param int $timeout_in_ms
     * @param int $interval_in_ms
     * @return mixed
     * @throws TimeOutException
     */
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

        return $this->http->get(self::PATH . '/' .$this->uid.'/search', $parameters);
    }

    // Stats

    public function stats()
    {
        return $this->http->get(self::PATH . '/' . $this->uid . '/stats');
    }

    // Settings - Global

    public function getSettings()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings');
    }

    public function updateSettings($settings)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings', $settings);
    }

    public function resetSettings()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/settings');
    }

    // Settings - Ranking rules

    public function getRankingRules()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings/ranking-rules');
    }

    public function updateRankingRules($ranking_rules)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings/ranking-rules', $ranking_rules);
    }

    public function resetRankingRules()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/settings/ranking-rules');
    }

    // Settings - Distinct attribute

    public function getDistinctAttribute()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings/distinct-attribute');
    }

    public function updateDistinctAttribute($distinct_attribute)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings/distinct-attribute', $distinct_attribute);
    }

    public function resetDistinctAttribute()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/settings/distinct-attribute');
    }

    // Settings - Searchable attributes

    public function getSearchableAttributes()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings/searchable-attributes');
    }

    public function updateSearchableAttributes($searchable_attributes)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings/searchable-attributes', $searchable_attributes);
    }

    public function resetSearchableAttributes()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/settings/searchable-attributes');
    }

    // Settings - Displayed attributes

    public function getDisplayedAttributes()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings/displayed-attributes');
    }

    public function updateDisplayedAttributes($displayed_attributes)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings/displayed-attributes', $displayed_attributes);
    }

    public function resetDisplayedAttributes()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/settings/displayed-attributes');
    }

    // Settings - Stop-words

    public function getStopWords()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings/stop-words');
    }

    public function updateStopWords($stop_words)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings/stop-words', $stop_words);
    }

    public function resetStopWords()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/settings/stop-words');
    }

    // Settings - Synonyms

    public function getSynonyms()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings/synonyms');
    }

    public function updateSynonyms($synonyms)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings/synonyms', $synonyms);
    }

    public function resetSynonyms()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/settings/synonyms');
    }

    // Settings - AcceptNewFields

    public function getAcceptNewFields()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings/accept-new-fields');
    }

    public function updateAcceptNewFields($accept_new_fields)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings/accept-new-fields', $accept_new_fields);
    }

    // Settings - Attributes for faceting

    public function getAttributesForFaceting()
    {
        return $this->http->get(self::PATH . '/' .$this->uid.'/settings/attributes-for-faceting');
    }

    public function updateAttributesForFaceting($attributes_for_faceting)
    {
        return $this->http->post(self::PATH . '/' .$this->uid.'/settings/attributes-for-faceting', $attributes_for_faceting);
    }

    public function resetAttributesForFaceting()
    {
        return $this->http->delete(self::PATH . '/' .$this->uid.'/settings/attributes-for-faceting');
    }

    // PRIVATE

    private function parseOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if ('facetsDistribution' === $key || 'facetFilters' === $key) {
                $options[$key] = json_encode($value);
            } elseif (is_array($value)) {
                $options[$key] = implode(',', $value);
            }
        }

        return $options;
    }
}
