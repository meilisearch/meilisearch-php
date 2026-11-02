<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\ExportQuery;
use Meilisearch\Contracts\Task;

use function Meilisearch\partial;
use function Meilisearch\waitForTask;

class Export extends Endpoint
{
    protected const PATH = '/export';

    /**
     * @since Meilisearch v1.16.0
     * @see https://www.meilisearch.com/docs/reference/api/export/export-to-a-remote-meilisearch
     */
    public function create(ExportQuery $options): Task
    {
        return Task::fromArray($this->http->post(self::PATH, $options), partial(waitForTask(...), $this->http));
    }
}
