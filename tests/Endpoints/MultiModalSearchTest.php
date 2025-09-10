<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Endpoints\Indexes;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class MultiModalSearchTest extends TestCase
{
    private Indexes $index;
    private array $documents;
    private ?string $voyageApiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['multimodal' => true]);

        $apiKey = getenv('VOYAGE_API_KEY');
        if (false === $apiKey || '' === $apiKey) {
            $this->voyageApiKey = null;

            return; // This test case is skipped if the Voyage API key is not set
        } else {
            $this->voyageApiKey = $apiKey;
        }

        $this->index = $this->createEmptyIndex($this->safeIndexName());
        $updateSettingsPromise = $this->index->updateSettings([
            'searchableAttributes' => ['title', 'overview'],
            'embedders' => [
                'multimodal' => self::getEmbedderConfig($this->voyageApiKey),
            ],
        ]);
        $this->index->waitForTask($updateSettingsPromise['taskUid']);

        // Load the movies.json dataset
        $documentsJson = file_get_contents('./tests/datasets/movies.json');
        $this->documents = json_decode($documentsJson, true, 512, JSON_THROW_ON_ERROR);
        $addDocumentsPromise = $this->index->addDocuments($this->documents);
        $this->index->waitForTask($addDocumentsPromise['taskUid']);
    }

    public function testTextOnlySearch(): void
    {
        $this->skipIfVoyageApiKeyIsMissing();

        $query = 'A movie with lightsabers in space';
        $response = $this->index->search($query, [
            'media' => [
                'text' => ['text' => $query],
            ],
            'hybrid' => [
                'embedder' => 'multimodal',
                'semanticRatio' => 1,
            ],
        ]);
        self::assertSame('Star Wars', $response->getHits()[0]['title']);
    }

    public function testImageOnlySearch(): void
    {
        $this->skipIfVoyageApiKeyIsMissing();

        $theFifthElementPoster = $this->documents[3]['poster'];
        $response = $this->index->search(null, [
            'media' => [
                'poster' => [
                    'poster' => $theFifthElementPoster,
                ],
            ],
            'hybrid' => [
                'embedder' => 'multimodal',
                'semanticRatio' => 1,
            ],
        ]);
        self::assertSame('The Fifth Element', $response->getHits()[0]['title']);
    }

    public function testTextAndImageSearch(): void
    {
        $this->skipIfVoyageApiKeyIsMissing();

        $query = 'a futuristic movie';
        $masterYodaBase64 = base64_encode(file_get_contents('./tests/assets/master-yoda.jpeg'));
        $response = $this->index->search(null, [
            'media' => [
                'textAndPoster' => [
                    'text' => $query,
                    'image' => [
                        'mime' => 'image/jpeg',
                        'data' => $masterYodaBase64,
                    ],
                ],
            ],
            'hybrid' => [
                'embedder' => 'multimodal',
                'semanticRatio' => 1,
            ],
        ]);
        self::assertSame('Star Wars', $response->getHits()[0]['title']);
    }

    private function skipIfVoyageApiKeyIsMissing(): void
    {
        if (null === $this->voyageApiKey) {
            self::markTestSkipped('Missing `VOYAGE_API_KEY` environment variable');
        }
    }

    private static function getEmbedderConfig(string $voyageApiKey): array
    {
        return [
            'source' => 'rest',
            'url' => 'https://api.voyageai.com/v1/multimodalembeddings',
            'apiKey' => $voyageApiKey,
            'dimensions' => 1024,
            'indexingFragments' => [
                'textAndPoster' => [
                    // the shape of the data here depends on the model used
                    'value' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'A movie titled {{doc.title}} whose description starts with {{doc.overview|truncatewords:20}}.',
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => '{{doc.poster}}',
                            ],
                        ],
                    ],
                ],
                'text' => [
                    'value' => [
                        // The shape of the data here depends on the model used
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'A movie titled {{doc.title}} whose description starts with {{doc.overview|truncatewords:20}}.',
                            ],
                        ],
                    ],
                ],
                'poster' => [
                    'value' => [
                        // The shape of the data here depends on the model used
                        'content' => [
                            [
                                'type' => 'image_url',
                                'image_url' => '{{doc.poster}}',
                            ],
                        ],
                    ],
                ],
            ],
            'searchFragments' => [
                'textAndPoster' => [
                    'value' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => '{{media.textAndPoster.text}}',
                            ],
                            [
                                'type' => 'image_base64',
                                'image_base64' => 'data:{{media.textAndPoster.image.mime}};base64,{{media.textAndPoster.image.data}}',
                            ],
                        ],
                    ],
                ],
                'text' => [
                    'value' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => '{{media.text.text}}',
                            ],
                        ],
                    ],
                ],
                'poster' => [
                    'value' => [
                        'content' => [
                            [
                                'type' => 'image_url',
                                'image_url' => '{{media.poster.poster}}',
                            ],
                        ],
                    ],
                ],
            ],
            'request' => [
                // This request object matches the Voyage API request object
                'inputs' => ['{{fragment}}', '{{..}}'],
                'model' => 'voyage-multimodal-3',
            ],
            'response' => [
                // This response object matches the Voyage API response object
                'data' => [
                    [
                        'embedding' => '{{embedding}}',
                    ],
                    '{{..}}',
                ],
            ],
        ];
    }
}
