<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\Http;

class ChatWorkspaces extends Endpoint
{
    protected const PATH = '/chats';

    private ?string $workspaceName;

    public function __construct(Http $http, ?string $workspaceName = null)
    {
        $this->workspaceName = $workspaceName;
        parent::__construct($http);
    }

    public function workspace(string $workspaceName): self
    {
        return new self($this->http, $workspaceName);
    }

    public function updateSettings(array $settings): array
    {
        return $this->http->patch(self::PATH.'/'.$this->workspaceName.'/settings', $settings);
    }
}
