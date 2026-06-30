<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\TemplateRenderQuery;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class TemplatesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['renderRoute' => true]);
    }

    protected function tearDown(): void
    {
        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['renderRoute' => false]);

        parent::tearDown();
    }

    public function testCanRenderInlineTemplate(): void
    {
        $query = (new TemplateRenderQuery())
            ->setTemplate('inlineDocumentTemplate', '{{ doc.breed }} called {{ doc.name }}')
            ->setInput('inlineDocument', ['breed' => 'Jack Russell', 'name' => 'Iko']);

        $response = $this->client->renderTemplate($query);

        self::assertSame('{{ doc.breed }} called {{ doc.name }}', $response->getTemplate());
        self::assertSame('Jack Russell called Iko', $response->getRendered());
    }
}
