<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskNetwork;

use Meilisearch\Contracts\TaskNetwork;

/**
 * @phpstan-import-type RawTaskImportData from TaskImportData
 * @phpstan-import-type RawTaskOrigin from TaskOrigin
 *
 * @phpstan-type RawImportNetwork array{
 *     import_from: RawTaskImportData,
 *     network_change: RawTaskOrigin
 * }
 *
 * @implements TaskNetwork<RawImportNetwork>
 */
final class ImportNetwork implements TaskNetwork
{
    public function __construct(
        public readonly TaskImportData $importFrom,
        public readonly TaskOrigin $networkChange,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            importFrom: TaskImportData::fromArray($data['import_from']),
            networkChange: TaskOrigin::fromArray($data['network_change']),
        );
    }
}
