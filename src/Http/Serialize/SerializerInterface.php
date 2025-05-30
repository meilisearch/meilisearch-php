<?php

declare(strict_types=1);

namespace Meilisearch\Http\Serialize;

interface SerializerInterface
{
    /**
     * Serialize data into string.
     *
     * @param string|int|float|bool|array<mixed>|null $data
     *
     * @return string|bool
     *
     * @throws \JsonException
     */
    public function serialize($data);

    /**
     * Unserialize the given string.
     *
     * @return string|int|float|bool|array<mixed>|null
     *
     * @throws \JsonException
     */
    public function unserialize(string $string);
}
