<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class FederationOptions
{
    private ?float $weight = null;

    public function setWeight(float $weight): FederationOptions
    {
        $this->weight = $weight;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'weight' => $this->weight,
        ], static function ($item) { return null !== $item; });
    }
}
