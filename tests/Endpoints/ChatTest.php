<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Http\Client;
use Tests\TestCase;

final class ChatTest extends TestCase
{
    private array $workspaceSettings = [
      "source" => "openAi",
      "orgId" => "some-org-id",
      "projectId" => "some-project-id",
      "apiVersion" => "some-api-version",
      "deploymentId" => "some-deployment-id",
      "baseUrl" => "https://baseurl.com",
      "apiKey" => "sk-abc...",
      "prompts" => [
        "system" => "You are a helpful assistant that answers questions based on the provided context.",
      ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['chatCompletions' => true]);
    }

    public function testUpdateWorkspacesSettings(): void
    {
        $response = $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);
        self::assertSame($this->workspaceSettings['source'], $response->getSource());
        self::assertSame($this->workspaceSettings['orgId'], $response->getOrgId());
        self::assertSame($this->workspaceSettings['projectId'], $response->getProjectId());
        self::assertSame($this->workspaceSettings['apiVersion'], $response->getApiVersion());
        self::assertSame($this->workspaceSettings['deploymentId'], $response->getDeploymentId());
        self::assertSame($this->workspaceSettings['baseUrl'], $response->getBaseUrl());
        self::assertSame($this->workspaceSettings['prompts']['system'], $response->getPrompts()['system']);
        // Meilisearch will mask the API key in the response
        self::assertSame('XXX...', $response->getApiKey());
    }

    public function testGetWorkspaceSettings(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);

        $response = $this->client->chats->workspace('myWorkspace')->getSettings();
        self::assertSame($this->workspaceSettings['source'], $response->getSource());
        self::assertSame($this->workspaceSettings['orgId'], $response->getOrgId());
        self::assertSame($this->workspaceSettings['projectId'], $response->getProjectId());
        self::assertSame($this->workspaceSettings['apiVersion'], $response->getApiVersion());
        self::assertSame($this->workspaceSettings['deploymentId'], $response->getDeploymentId());
        self::assertSame($this->workspaceSettings['baseUrl'], $response->getBaseUrl());
        self::assertSame($this->workspaceSettings['prompts']['system'], $response->getPrompts()['system']);
        // Meilisearch will mask the API key in the response
        self::assertSame('XXX...', $response->getApiKey());
    }

    public function testListWorkspaces(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);
        $response = $this->client->chats->listWorkspaces();
        self::assertSame([
          ['uid' => 'myWorkspace'],
        ], $response->getResults());
    }

    public function testDeleteWorkspaceSettings(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);
        $this->client->chats->workspace('myWorkspace')->resetSettings();
        $settingsResponse = $this->client->chats->workspace('myWorkspace')->getSettings();
        self::assertSame('openAi', $settingsResponse->getSource());
        self::assertNull($settingsResponse->getOrgId());
        self::assertNull($settingsResponse->getProjectId());
        self::assertNull($settingsResponse->getApiVersion());
        self::assertNull($settingsResponse->getDeploymentId());
        self::assertNull($settingsResponse->getBaseUrl());
        self::assertNull($settingsResponse->getApiKey());

        $listResponse = $this->client->chats->listWorkspaces();
        self::assertSame([
          ['uid' => 'myWorkspace'],
        ], $listResponse->getResults());
    }
}
