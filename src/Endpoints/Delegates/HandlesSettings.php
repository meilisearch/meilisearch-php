<?php

namespace MeiliSearch\Endpoints\Delegates;

trait HandlesSettings
{
    // Settings - Ranking rules

    public function getRankingRules(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/ranking-rules');
    }

    public function updateRankingRules(array $rankingRules): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/ranking-rules', $rankingRules);
    }

    public function resetRankingRules(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/ranking-rules');
    }

    // Settings - Distinct attribute

    public function getDistinctAttribute(): ?string
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/distinct-attribute');
    }

    public function updateDistinctAttribute(string $distinctAttribute): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/distinct-attribute', $distinctAttribute);
    }

    public function resetDistinctAttribute(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/distinct-attribute');
    }

    // Settings - Searchable attributes

    public function getSearchableAttributes(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/searchable-attributes');
    }

    public function updateSearchableAttributes($searchable_attributes): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/searchable-attributes', $searchable_attributes);
    }

    public function resetSearchableAttributes(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/searchable-attributes');
    }

    // Settings - Displayed attributes

    public function getDisplayedAttributes(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/displayed-attributes');
    }

    public function updateDisplayedAttributes($displayedAttributes): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/displayed-attributes', $displayedAttributes);
    }

    public function resetDisplayedAttributes(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/displayed-attributes');
    }

    // Settings - Stop-words

    public function getStopWords(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/stop-words');
    }

    public function updateStopWords($stopWords): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/stop-words', $stopWords);
    }

    public function resetStopWords(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/stop-words');
    }

    // Settings - Synonyms

    public function getSynonyms(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/synonyms');
    }

    public function updateSynonyms($synonyms): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/synonyms', $synonyms);
    }

    public function resetSynonyms(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/synonyms');
    }

    // Settings - AcceptNewFields

    public function getAcceptNewFields(): bool
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/accept-new-fields');
    }

    public function updateAcceptNewFields($acceptNewFields): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/accept-new-fields', $acceptNewFields);
    }

    // Settings - Attributes for faceting

    public function getAttributesForFaceting(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/attributes-for-faceting');
    }

    public function updateAttributesForFaceting(array $attributesForFaceting): array
    {
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings/attributes-for-faceting', $attributesForFaceting);
    }

    public function resetAttributesForFaceting(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/attributes-for-faceting');
    }
}
