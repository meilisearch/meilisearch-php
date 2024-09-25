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

    /**
     * @return $this
     */
    public function setSemanticRatio(float $ratio): self
    {
        $this->semanticRatio = $ratio;

        return $this;
    }

    /**
     * @param non-empty-string $embedder
     *
     * @return $this
     */
    public function setEmbedder(string $embedder): self
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
