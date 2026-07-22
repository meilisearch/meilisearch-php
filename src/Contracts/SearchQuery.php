<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-import-type BaseSearchQueryArray from AbstractSearchQuery
 *
 * @phpstan-type SearchQueryArray BaseSearchQueryArray
 */
class SearchQuery extends AbstractSearchQuery
{
    /**
     * @param SearchQueryArray $data
     */
    public static function fromArray(array $data): self
    {
        return (new self())->hydrateFromArray($data);
    }

    /**
     * @return SearchQueryArray
     */
    public function toArray(): array
    {
        return $this->baseArray();
    }
}
