<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class HybridSearchOptions
{
    private ?float $semanticRatio = null;
    private ?string $embedder = null;

    public function setSemanticRatio(float $ratio): HybridSearchOptions
    {
        $this->semanticRatio = $ratio;

        return $this;
    }

    public function setEmbedder(string $embedder): HybridSearchOptions
    {
        $this->embedder = $embedder;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'semanticRatio' => $this->semanticRatio,
            'embedder' => $this->embedder,
        ], static function ($item) { return null !== $item; });
    }
}
