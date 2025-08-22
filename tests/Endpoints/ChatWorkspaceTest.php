<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Exceptions\ApiException;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class ChatWorkspaceTest extends TestCase
{
    private array $workspaceSettings = [
        'source' => 'mistral',
        'orgId' => 'some-org-id',
        'projectId' => 'some-project-id',
        'apiVersion' => 'some-api-version',
        'deploymentId' => 'some-deployment-id',
        'baseUrl' => 'https://baseurl.com',
        'apiKey' => 'sk-abc...',
        'prompts' => [
            'system' => 'You are a helpful assistant that answers questions based on the provided context.',
            'searchDescription' => 'You are a helpful assistant that answers questions based on the provided context.',
            'searchQParam' => 'q',
            'searchIndexUidParam' => 'indexUid',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['chatCompletions' => true]);

        // List workspaces and
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

    public function testExceptionWhenWorkspaceDoesNotExist(): void
    {
        self::expectException(ApiException::class);
        self::expectExceptionCode(404);
        $this->client->chats->workspace('non-existent-workspace')->getSettings();
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
        // Meilisearch will mask the API key in the response
        self::assertSame('XXX...', $response->getApiKey());

        self::assertSame($this->workspaceSettings['prompts']['system'], $response->getPrompts()->getSystem());
        self::assertSame($this->workspaceSettings['prompts']['searchDescription'], $response->getPrompts()->getSearchDescription());
        self::assertSame($this->workspaceSettings['prompts']['searchQParam'], $response->getPrompts()->getSearchQParam());
        self::assertSame($this->workspaceSettings['prompts']['searchIndexUidParam'], $response->getPrompts()->getSearchIndexUidParam());
    }

    public function testListWorkspaces(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);
        $response = $this->client->chats->listWorkspaces();
        self::assertSame([
            ['uid' => 'myWorkspace'],
        ], $response->getResults());
    }

    public function testResetWorkspaceSettings(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);
        $this->client->chats->workspace('myWorkspace')->resetSettings();
        $settingsResponse = $this->client->chats->workspace('myWorkspace')->getSettings();
        self::assertSame('openAi', $settingsResponse->getSource()); // Source is reset to openAi for some reason
        self::assertNull($settingsResponse->getOrgId());
        self::assertNull($settingsResponse->getProjectId());
        self::assertNull($settingsResponse->getApiVersion());
        self::assertNull($settingsResponse->getDeploymentId());
        self::assertNull($settingsResponse->getBaseUrl());
        self::assertNull($settingsResponse->getApiKey());
        // Prompts are reset to their original values
        self::assertNotEmpty($settingsResponse->getPrompts()->getSystem());
        self::assertNotEmpty($settingsResponse->getPrompts()->getSearchDescription());
        self::assertNotEmpty($settingsResponse->getPrompts()->getSearchQParam());
        self::assertNotEmpty($settingsResponse->getPrompts()->getSearchIndexUidParam());

        // Workspace still appears when listing workspaces
        $listResponse = $this->client->chats->listWorkspaces();
        self::assertSame([
            ['uid' => 'myWorkspace'],
        ], $listResponse->getResults());
    }

    public function testCompletionStreaming(): void
    {
        $this->client->chats->workspace('myWorkspace')->updateSettings($this->workspaceSettings);

        $stream = $this->client->chats->workspace('myWorkspace')->streamCompletion([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello, how are you?',
                ],
            ],
            'stream' => true,
        ]);

        $receivedData = '';
        $chunkCount = 0;
        $maxChunks = 1000; // Safety limit

        try {
            while (!$stream->eof() && $chunkCount < $maxChunks) {
                $chunk = $stream->read(8192);
                if ('' === $chunk) {
                    // Small backoff to avoid tight loop on empty reads
                    usleep(10_000);
                    continue;
                }
                $receivedData .= $chunk;
                ++$chunkCount;
            }

            if ($chunkCount >= $maxChunks) {
                self::fail('Test exceeded maximum chunk limit of '.$maxChunks);
            }

            self::assertGreaterThan(0, \strlen($receivedData));
        } finally {
            // Ensure we release network resources
            $stream->close();
        }
    }
}
