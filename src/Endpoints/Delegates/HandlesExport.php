<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\ExportQuery;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Export;

trait HandlesExport
{
    protected Export $export;

    /**
     * Sends documents and settings of the matching indexes to another Meilisearch instance.
     *
     * @since Meilisearch v1.16.0
     * @see https://www.meilisearch.com/docs/reference/api/export/export-to-a-remote-meilisearch
     */
    public function export(ExportQuery $options): Task
    {
        return $this->export->create($options);
    }
}
