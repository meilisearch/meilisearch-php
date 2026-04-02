<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class UpdateKeyQuery
{
    /**
     * @param non-empty-string      $keyOrUid    the uid or key field of an existing API key
     * @param non-empty-string|null $name        A new human-readable name for the API key. Pass null to remove the
     *                                           existing name. Use this to identify keys by purpose, such as
     *                                           "Production Search Key" or "CI/CD Indexing Key".
     * @param non-empty-string|null $description A new description for the API key. Pass null to remove the existing
     *                                           description. Useful for documenting the purpose or usage of the key.
     */
    public function __construct(
        public readonly string $keyOrUid,
        private readonly ?string $name = null,
        private readonly ?string $description = null,
    ) {
    }

    /**
     * @return array{
     *     name: non-empty-string|null,
     *     description: non-empty-string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
