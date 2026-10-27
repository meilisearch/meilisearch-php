<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\MultiSearchFederation;
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
            'filter' => ['values' => ['genre' => 'fantasy']],
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
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $metadata['queryUid'],
        );
        self::assertSame('id', $metadata['primaryKey']);
        self::assertSame(
            ['missingWord' => ['word' => 'wizard']],
            $metadata['inlineRuleConditionOutcomes']['satisfiesQueryWordsCondition'],
        );
        self::assertSame(
            ['notEnoughFields' => ['fieldCountInFilter' => 0, 'fieldCountInCondition' => 1]],
            $metadata['inlineRuleConditionOutcomes']['satisfiesFilterCondition'],
        );
        self::assertSame($metadata, $result->toArray()['metadata']);
    }

    public function testInlinePinRuleReordersHits(): void
    {
        $baseline = $this->index->search('prince');
        $pinnedId = $baseline->getHit(1)['id'];

        $result = $this->index->search(
            (new SearchQuery())->setQuery('prince')->setInlineRule([
                'uid' => 'pin-second',
                'conditions' => ['query' => ['words' => 'prince']],
                'actions' => ['pin' => [['id' => (string) $pinnedId, 'position' => 0]]],
            ]),
            includeMetadata: true,
        );

        self::assertSame($pinnedId, $result->getHit(0)['id']);
        self::assertNotSame($baseline->getHit(0)['id'], $result->getHit(0)['id']);
        self::assertSame(
            'satisfied',
            $result->getMetadata()['inlineRuleConditionOutcomes']['satisfiesQueryWordsCondition'],
        );
    }

    public function testEmptyInlineRuleObjectsAreAccepted(): void
    {
        $result = $this->index->search(
            (new SearchQuery())->setQuery('prince')->setInlineRule([
                'uid' => 'empty-maps',
                'conditions' => [],
                'actions' => [],
            ]),
            includeMetadata: true,
        );

        self::assertSame(
            ['satisfiesActiveCondition' => true],
            $result->getMetadata()['inlineRuleConditionOutcomes'],
        );
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
        self::assertSame($response['results'][0]['hits'], $response['results'][1]['hits']);
        self::assertArrayNotHasKey('inlineRuleConditionOutcomes', $response['results'][0]['metadata']);
        self::assertSame(
            ['missingWord' => ['word' => 'wizard']],
            $response['results'][1]['metadata']['inlineRuleConditionOutcomes']['satisfiesQueryWordsCondition'],
        );
        self::assertSame(
            ['notEnoughFields' => ['fieldCountInFilter' => 0, 'fieldCountInCondition' => 1]],
            $response['results'][1]['metadata']['inlineRuleConditionOutcomes']['satisfiesFilterCondition'],
        );
    }

    public function testFederatedMultiSearchReturnsMetadataPerQuery(): void
    {
        $response = $this->client->multiSearch([
            (new MultiSearchQuery())->setIndexUid($this->index->getUid())->setQuery('prince'),
            (new MultiSearchQuery())->setIndexUid($this->index->getUid())->setQuery('prince')->setInlineRule(self::INLINE_RULE),
        ], new MultiSearchFederation(), includeMetadata: true);

        self::assertArrayNotHasKey('results', $response);
        self::assertCount(2, $response['metadata']);
        self::assertArrayNotHasKey('inlineRuleConditionOutcomes', $response['metadata'][0]);
        self::assertSame(
            ['missingWord' => ['word' => 'wizard']],
            $response['metadata'][1]['inlineRuleConditionOutcomes']['satisfiesQueryWordsCondition'],
        );
    }
}
