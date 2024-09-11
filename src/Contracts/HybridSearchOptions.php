<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class HybridSearchOptions
{
    private ?float $semanticRatio = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $embedder = null;

    public function setSemanticRatio(float $ratio): HybridSearchOptions
    {
        $this->semanticRatio = $ratio;

        return $this;
    }

    /**
     * @param non-empty-string $embedder
     */
    public function setEmbedder(string $embedder): HybridSearchOptions
    {
        $this->embedder = $embedder;

        return $this;
    }

    /**
     * @return array{
     *     semanticRatio?: float,
     *     embedder?: non-empty-string
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'semanticRatio' => $this->semanticRatio,
            'embedder' => $this->embedder,
        ], static function ($item) { return null !== $item; });
    }
}
