<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class DynamicSearchRulesFilter
{
    /**
     * @var list<non-empty-string>|null
     */
    private ?array $attributePatterns = null;

    private ?bool $active = null;

    /**
     * @param list<non-empty-string> $patterns
     *
     * @return $this
     */
    public function setAttributePatterns(array $patterns): self
    {
        $this->attributePatterns = $patterns;

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
     *     attributePatterns?: list<non-empty-string>,
     *     active?: bool
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'attributePatterns' => $this->attributePatterns,
            'active' => $this->active,
        ], static function ($item) { return null !== $item; });
    }
}
