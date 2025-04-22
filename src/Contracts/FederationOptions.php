<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class FederationOptions
{
    private ?float $weight = null;
    /**
     * @var non-empty-string|null
     */
    private ?string $remote = null;

    /**
     * @return $this
     */
    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @param non-empty-string $remote
     *
     * @return $this
     */
    public function setRemote(string $remote): self
    {
        $this->remote = $remote;

        return $this;
    }

    /**
     * @return array{
     *     weight?: float,
     *     remote?: non-empty-string,
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'weight' => $this->weight,
            'remote' => $this->remote,
        ], static function ($item) { return null !== $item; });
    }
}
