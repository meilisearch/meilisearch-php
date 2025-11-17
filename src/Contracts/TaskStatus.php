<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

enum TaskStatus: string
{
    case Canceled = 'canceled';
    case Enqueued = 'enqueued';
    case Failed = 'failed';
    case Succeeded = 'succeeded';
    case Processing = 'processing';
    case Unknown = 'unknown';
}
