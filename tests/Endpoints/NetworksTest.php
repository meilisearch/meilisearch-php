<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\NetworkResults;
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

    public function testUpdateNetworks(): void
    {
        $networks = [
            'self' => 'ms-00',
            'remotes' => [
                'ms-00' => [
                    'url' => 'http://INSTANCE_URL',
                    'searchApiKey' => 'INSTANCE_API_KEY',
                    'writeApiKey' => 'INSTANCE_WRITE_API_KEY',
                ],
                'ms-01' => [
                    'url' => 'http://ANOTHER_INSTANCE_URL',
                    'searchApiKey' => 'ANOTHER_INSTANCE_API_KEY',
                    'writeApiKey' => 'ANOTHER_INSTANCE_WRITE_API_KEY',
                ],
            ],
        ];

        $updateResp = $this->client->updateNetwork($networks);
        $this->assertNetworkResponse($networks, $updateResp);

        $getResp = $this->client->getNetwork();
        $this->assertNetworkResponse($networks, $getResp);
    }

    private function assertNetworkResponse(array $expected, NetworkResults $response): void
    {
        self::assertSame($expected['self'], $response->getSelf());
        $respRemotes = $response->getRemotes();
        foreach ($expected['remotes'] as $key => $remote) {
            self::assertArrayHasKey($key, $respRemotes);
            self::assertSame($remote['url'], $respRemotes[$key]['url']);
            self::assertSame($remote['searchApiKey'], $respRemotes[$key]['searchApiKey']);
            self::assertSame($remote['writeApiKey'], $respRemotes[$key]['writeApiKey']);
        }
    }
}
