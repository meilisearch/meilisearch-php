<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\Index;

use Meilisearch\Contracts\Data;
use Meilisearch\Contracts\ReadOnlyArrayAccess;

class Faceting extends Data implements \ArrayAccess, \JsonSerializable
{
    use ReadOnlyArrayAccess;

    public function jsonSerialize(): object
    {
        $data = $this->getIterator()->getArrayCopy();

        if (\array_key_exists('sortFacetValuesBy', $data) && \is_array($data['sortFacetValuesBy'])) {
            $data['sortFacetValuesBy'] = (object) $data['sortFacetValuesBy'];
        }

        return (object) $data;
    }
}
