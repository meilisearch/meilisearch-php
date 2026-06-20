<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class PersonalizeOptions
{
    /**
     * @var non-empty-string|null
     */
    private ?string $userContext = null;

    /**
     * @param non-empty-string $userContext
     *
     * @return $this
     */
    public function setUserContext(string $userContext): self
    {
        $this->userContext = $userContext;

        return $this;
    }

    /**
     * @return array{
     *     userContext?: non-empty-string
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'userContext' => $this->userContext,
        ], static function ($item) { return null !== $item; });
    }
}
