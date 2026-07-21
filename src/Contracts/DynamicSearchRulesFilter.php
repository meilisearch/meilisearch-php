<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class DynamicSearchRulesFilter
{
    private ?string $query = null;

    private ?bool $active = null;

    /**
     * @return $this
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return array{
     *     query?: string,
     *     active?: bool
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'query' => $this->query,
            'active' => $this->active,
        ], static function ($item) { return null !== $item; });
    }
}
