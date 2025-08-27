<?php

declare(strict_types=1);

namespace Tests\Endpoints;

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
        self::assertSame($networks['self'], $updateResp->getSelf());

        foreach ($networks['remotes'] as $key => $remote) {
            $respRemotes = $updateResp->getRemotes();
            self::assertArrayHasKey($key, $respRemotes);
            self::assertArrayHasKey('url', $respRemotes[$key]);
            self::assertArrayHasKey('searchApiKey', $respRemotes[$key]);
            self::assertArrayHasKey('writeApiKey', $respRemotes[$key]);
            self::assertSame($remote['url'], $respRemotes[$key]['url']);
            self::assertSame($remote['searchApiKey'], $respRemotes[$key]['searchApiKey']);
            self::assertSame($remote['writeApiKey'], $respRemotes[$key]['writeApiKey']);
        }

        $getResp = $this->client->getNetwork();
        self::assertSame($networks['self'], $getResp->getSelf());
        self::assertArrayHasKey($networks['self'], $getResp->getRemotes(), '"self" must be a key of remotes');
        foreach ($networks['remotes'] as $key => $remote) {
            $respRemotes = $getResp->getRemotes();
            self::assertArrayHasKey($key, $respRemotes);
            self::assertArrayHasKey('url', $respRemotes[$key]);
            self::assertArrayHasKey('searchApiKey', $respRemotes[$key]);
            self::assertArrayHasKey('writeApiKey', $respRemotes[$key]);
            self::assertSame($remote['url'], $respRemotes[$key]['url']);
            self::assertSame($remote['searchApiKey'], $respRemotes[$key]['searchApiKey']);
            self::assertSame($remote['writeApiKey'], $respRemotes[$key]['writeApiKey']);
        }
    }
}
