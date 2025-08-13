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
        self::assertSame($this->workspaceSettings['source'], $response['source']);
        self::assertSame($this->workspaceSettings['orgId'], $response['orgId']);
        self::assertSame($this->workspaceSettings['projectId'], $response['projectId']);
        self::assertSame($this->workspaceSettings['apiVersion'], $response['apiVersion']);
        self::assertSame($this->workspaceSettings['deploymentId'], $response['deploymentId']);
        self::assertSame($this->workspaceSettings['baseUrl'], $response['baseUrl']);
        self::assertSame($this->workspaceSettings['prompts']['system'], $response['prompts']['system']);
        // Meilisearch will mask the API key in the response
        self::assertSame('XXX...', $response['apiKey']);
    }

    public function testGetWorkspaceSettings(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);

        $response = $this->client->chats->workspace('myWorkspace')->getSettings();
        self::assertSame($this->workspaceSettings['source'], $response['source']);
        self::assertSame($this->workspaceSettings['orgId'], $response['orgId']);
        self::assertSame($this->workspaceSettings['projectId'], $response['projectId']);
        self::assertSame($this->workspaceSettings['apiVersion'], $response['apiVersion']);
        self::assertSame($this->workspaceSettings['deploymentId'], $response['deploymentId']);
        self::assertSame($this->workspaceSettings['baseUrl'], $response['baseUrl']);
        self::assertSame($this->workspaceSettings['prompts']['system'], $response['prompts']['system']);
        // Meilisearch will mask the API key in the response
        self::assertSame('XXX...', $response['apiKey']);
    }

    public function testListWorkspaces(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);
        $response = $this->client->chats->listWorkspaces();
        self::assertSame([
          ['uid' => 'myWorkspace'],
        ], $response['results']);
    }

    public function testDeleteWorkspaceSettings(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);
        $this->client->chats->workspace('myWorkspace')->resetSettings();
        $settingsResponse = $this->client->chats->workspace('myWorkspace')->getSettings();
        self::assertSame('openAi', $settingsResponse['source']);
        self::assertNull($settingsResponse['orgId']);
        self::assertNull($settingsResponse['projectId']);
        self::assertNull($settingsResponse['apiVersion']);
        self::assertNull($settingsResponse['deploymentId']);
        self::assertNull($settingsResponse['baseUrl']);
        self::assertNull($settingsResponse['apiKey']);

        $listResponse = $this->client->chats->listWorkspaces();
        self::assertSame([
          ['uid' => 'myWorkspace'],
        ], $listResponse['results']);
    }
}
