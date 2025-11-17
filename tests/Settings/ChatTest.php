<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class ChatTest extends TestCase
{
    private Indexes $index;

    private const DEFAULT_CHAT_SETTINGS = [
        'description' => '',
        'documentTemplate' => '{% for field in fields %}{% if field.is_searchable and field.value != nil %}{{ field.name }}: {{ field.value }}
{% endif %}{% endfor %}',
        'documentTemplateMaxBytes' => 400,
        'searchParameters' => [],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $apiKey = getenv('MEILISEARCH_API_KEY');
        if (!$apiKey) {
            throw new \Exception('Missing `MEILISEARCH_API_KEY` environment variable');
        }

        $http = new Client($this->host, $apiKey);
        $http->patch('/experimental-features', ['chatCompletions' => true]);
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetChatDefaultSettings(): void
    {
        $settings = $this->index->getChat();
        self::assertSame(self::DEFAULT_CHAT_SETTINGS['description'], $settings['description']);
        self::assertSame(self::DEFAULT_CHAT_SETTINGS['documentTemplate'], $settings['documentTemplate']);
        self::assertSame(self::DEFAULT_CHAT_SETTINGS['documentTemplateMaxBytes'], $settings['documentTemplateMaxBytes']);
        self::assertSame(self::DEFAULT_CHAT_SETTINGS['searchParameters'], $settings['searchParameters']);
    }

    public function testUpdateChatSettings(): void
    {
        $newSettings = [
            'description' => 'New description',
            'documentTemplate' => 'New document template',
            'documentTemplateMaxBytes' => 500,
            'searchParameters' => [
                'limit' => 10,
            ],
        ];

        $this->index->updateChat($newSettings)->wait();

        $settings = $this->index->getChat();
        self::assertSame($newSettings['description'], $settings['description']);
        self::assertSame($newSettings['documentTemplate'], $settings['documentTemplate']);
        self::assertSame($newSettings['documentTemplateMaxBytes'], $settings['documentTemplateMaxBytes']);
        self::assertSame($newSettings['searchParameters'], $settings['searchParameters']);
    }
}
