<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\MultiSearchQuery;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Endpoints\Index;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class InlineRuleSearchTest extends TestCase
{
    private const INLINE_RULE = [
        'uid' => 'fantasy-boost',
        'conditions' => [
            'query' => ['words' => 'wizard'],
        ],
        'actions' => [
            'scale' => [
                ['filter' => 'genre = fantasy', 'weight' => 1.2],
            ],
        ],
    ];

    private Index $index;

    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['dynamicSearchRules' => true]);

        $this->index = $this->createEmptyIndex($this->safeIndexName('books'));
        $this->index->updateFilterableAttributes(['genre'])->wait();
        $this->index->addDocuments(self::DOCUMENTS)->wait();
    }

    public function testSearchWithInlineRuleReturnsConditionOutcomes(): void
    {
        $result = $this->index->search(
            (new SearchQuery())->setQuery('prince')->setInlineRule(self::INLINE_RULE),
            includeMetadata: true,
        );

        self::assertNotSame(0, $result->getHitsCount());
        $metadata = $result->getMetadata();
        self::assertNotNull($metadata);
        self::assertSame($this->index->getUid(), $metadata['indexUid']);
        self::assertSame(
            ['missingWord' => ['word' => 'wizard']],
            $metadata['inlineRuleConditionOutcomes']['satisfiesQueryWordsCondition'],
        );
        self::assertSame($metadata, $result->toArray()['metadata']);
    }

    public function testSearchWithoutIncludeMetadataHasNoMetadata(): void
    {
        $result = $this->index->search(
            (new SearchQuery())->setQuery('prince')->setInlineRule(self::INLINE_RULE),
        );

        self::assertNull($result->getMetadata());
        self::assertArrayNotHasKey('metadata', $result->toArray());
    }

    public function testRawSearchWithInlineRuleReturnsConditionOutcomes(): void
    {
        $result = $this->index->rawSearch('prince', ['inlineRule' => self::INLINE_RULE], includeMetadata: true);

        self::assertArrayHasKey('inlineRuleConditionOutcomes', $result['metadata']);
    }

    public function testMultiSearchWithInlineRuleReturnsConditionOutcomes(): void
    {
        $response = $this->client->multiSearch([
            (new MultiSearchQuery())->setIndexUid($this->index->getUid())->setQuery('prince'),
            (new MultiSearchQuery())->setIndexUid($this->index->getUid())->setQuery('prince')->setInlineRule(self::INLINE_RULE),
        ], includeMetadata: true);

        self::assertCount(2, $response['results']);
        self::assertArrayNotHasKey('inlineRuleConditionOutcomes', $response['results'][0]['metadata']);
        self::assertSame(
            ['missingWord' => ['word' => 'wizard']],
            $response['results'][1]['metadata']['inlineRuleConditionOutcomes']['satisfiesQueryWordsCondition'],
        );
    }
}
