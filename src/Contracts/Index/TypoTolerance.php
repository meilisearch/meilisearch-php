<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\Index;

use Meilisearch\Contracts\Data;

/**
 * @final since 1.3.0
 */
class TypoTolerance extends Data implements \JsonSerializable
{
    public function jsonSerialize(): object
    {
        return (object) $this->getIterator()->getArrayCopy();
    }
}
