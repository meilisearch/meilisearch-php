<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

enum TaskStatus: string
{
    case Canceled = 'canceled';
    case Enqueued = 'enqueued';
    case Failed = 'failed';
    case Succeeded = 'succeeded';
    case Processing = 'processing';
}
