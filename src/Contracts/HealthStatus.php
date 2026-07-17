<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

enum HealthStatus: string
{
    case Available = 'available';
    case Unknown = 'unknown';
}
