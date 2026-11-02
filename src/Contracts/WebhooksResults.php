<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @since Meilisearch v1.17.0
 */
final class WebhooksResults extends Data
{
    /**
     * @param array{results: list<Webhook>} $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params['results']);
    }

    /**
     * @return list<Webhook>
     */
    public function getResults(): array
    {
        return $this->data;
    }

    /**
     * @return array{
     *     results: list<array{
     *         uuid: non-empty-string,
     *         url: non-empty-string,
     *         headers: array<string, string>,
     *         isEditable: bool
     *     }>
     * }
     */
    public function toArray(): array
    {
        return [
            'results' => array_map(static fn (Webhook $webhook): array => $webhook->toArray(), $this->data),
        ];
    }
}
