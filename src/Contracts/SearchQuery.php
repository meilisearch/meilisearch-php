<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-import-type RawBaseSearchQuery from AbstractSearchQuery
 *
 * @phpstan-type RawSearchQuery RawBaseSearchQuery
 */
class SearchQuery extends AbstractSearchQuery
{
    /**
     * @param RawSearchQuery $data
     */
    public static function fromArray(array $data): self
    {
        return (new self())->hydrateFromArray($data);
    }

    /**
     * @return RawSearchQuery
     */
    public function toArray(): array
    {
        return $this->baseArray();
    }
}
