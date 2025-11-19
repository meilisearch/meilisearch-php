<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Exceptions\ApiException;
use Psr\Http\Message\StreamInterface;

interface Http
{
    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function get(string $path, array $query = []): mixed;

    /**
     * @param non-empty-string|null $contentType
     *
     * @throws ApiException
     * @throws \JsonException
     */
    public function post(string $path, mixed $body = null, array $query = [], ?string $contentType = null): mixed;

    /**
     * @param non-empty-string|null $contentType
     *
     * @throws ApiException
     * @throws \JsonException
     */
    public function put(string $path, mixed $body = null, array $query = [], ?string $contentType = null): mixed;

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function patch(string $path, mixed $body = null, array $query = []): mixed;

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function delete(string $path, array $query = []): mixed;

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function postStream(string $path, mixed $body = null, array $query = []): StreamInterface;
}
