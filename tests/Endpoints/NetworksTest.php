<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\TaskType;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class NetworksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['network' => true]);
    }

    public function testInitializeNetwork(): void
    {
        $apiKey = getenv('MEILISEARCH_API_KEY');
        $instanceName = 'ms-00';

        $options = [
            'self' => $instanceName,
            'remotes' => [
                $instanceName => [
                    'url' => $this->host,
                    'searchApiKey' => $apiKey,
                    'writeApiKey' => $apiKey,
                ],
            ],
        ];

        $task = $this->client->initializeNetwork($options);
        self::assertSame(TaskType::NetworkTopologyChange, $task->getType());

        $finishedTask = $task->wait();
        self::assertTrue($finishedTask->isFinished());

        $network = $this->client->getNetwork();
        self::assertSame($instanceName, $network->getSelf());
        self::assertSame($instanceName, $network->getLeader());

        $remotes = $network->getRemotes();
        self::assertArrayHasKey($instanceName, $remotes);
        self::assertNotNull($remotes[$instanceName]);
        self::assertSame($this->host, $remotes[$instanceName]['url']);
        self::assertSame($apiKey, $remotes[$instanceName]['searchApiKey']);
        self::assertSame($apiKey, $remotes[$instanceName]['writeApiKey']);
    }
}
