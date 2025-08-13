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

    public function listWorkspaces(): array
    {
        return $this->http->get(self::PATH);
    }

    public function workspace(string $workspaceName): self
    {
        return new self($this->http, $workspaceName);
    }

    public function getSettings(): array
    {
        return $this->http->get(self::PATH.'/'.$this->workspaceName.'/settings');
    }

    public function updateSettings(array $settings): array
    {
        return $this->http->patch(self::PATH.'/'.$this->workspaceName.'/settings', $settings);
    }

    public function resetSettings(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->workspaceName.'/settings');
    }
}
