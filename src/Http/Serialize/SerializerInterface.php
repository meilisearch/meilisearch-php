<?php

declare(strict_types=1);

namespace MeiliSearch\Http\Serialize;

use MeiliSearch\Exceptions\JsonDecodingException;
use MeiliSearch\Exceptions\JsonEncodingException;

interface SerializerInterface
{
    /**
     * Serialize data into string.
     *
     * @param string|int|float|bool|array|null $data
     *
     * @return string|bool
     *
     * @throws JsonEncodingException
     */
    public function serialize($data);

    /**
     * Unserialize the given string.
     *
     * @return string|int|float|bool|array|null
     *
     * @throws JsonDecodingException
     */
    public function unserialize(string $string);
}
