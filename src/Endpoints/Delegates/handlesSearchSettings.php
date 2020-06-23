<?php

namespace MeiliSearch\Endpoints\Delegates;

trait handlesSearchSettings
{
    // Settings - Ranking rules

    public function getRankingRules()
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/ranking-rules');
    }

    public function updateRankingRules($ranking_rules)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/ranking-rules', $ranking_rules);
    }

    public function resetRankingRules()
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/ranking-rules');
    }

    // Settings - Distinct attribute

    public function getDistinctAttribute()
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/distinct-attribute');
    }

    public function updateDistinctAttribute($distinct_attribute)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/distinct-attribute', $distinct_attribute);
    }

    public function resetDistinctAttribute()
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/distinct-attribute');
    }

    // Settings - Searchable attributes

    public function getSearchableAttributes()
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/searchable-attributes');
    }

    public function updateSearchableAttributes($searchable_attributes)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/searchable-attributes', $searchable_attributes);
    }

    public function resetSearchableAttributes()
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/searchable-attributes');
    }

    // Settings - Displayed attributes

    public function getDisplayedAttributes()
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/displayed-attributes');
    }

    public function updateDisplayedAttributes($displayed_attributes)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/displayed-attributes', $displayed_attributes);
    }

    public function resetDisplayedAttributes()
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/displayed-attributes');
    }

    // Settings - Stop-words

    public function getStopWords()
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/stop-words');
    }

    public function updateStopWords($stop_words)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/stop-words', $stop_words);
    }

    public function resetStopWords()
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/stop-words');
    }

    // Settings - Synonyms

    public function getSynonyms()
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/synonyms');
    }

    public function updateSynonyms($synonyms)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/synonyms', $synonyms);
    }

    public function resetSynonyms()
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/synonyms');
    }

    // Settings - AcceptNewFields

    public function getAcceptNewFields()
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/accept-new-fields');
    }

    public function updateAcceptNewFields($accept_new_fields)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/accept-new-fields', $accept_new_fields);
    }

    // Settings - Attributes for faceting

    public function getAttributesForFaceting()
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/attributes-for-faceting');
    }

    public function updateAttributesForFaceting($attributes_for_faceting)
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/attributes-for-faceting', $attributes_for_faceting);
    }

    public function resetAttributesForFaceting()
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/attributes-for-faceting');
    }
}
