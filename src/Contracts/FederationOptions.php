<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class FederationOptions
{
    private ?float $weight = null;

    /**
     * @return $this
     */
    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return array{
     *     weight?: float,
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'weight' => $this->weight,
        ], static function ($item) { return null !== $item; });
    }
}
