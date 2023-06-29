<?php

namespace Tests\Unit\Services\NewsParser;

use App\Services\NewsHandler\NewsParser\NewsParserForBing;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class NewsParserForBingTest extends TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testGetParsedData(array $response): void
    {
        $parser = new NewsParserForBing();
        $parsedData = $parser->getParsedData($response);
        foreach ($parsedData as $index => $parsedArticle) {
            $this->assertValidParsedArticle($parsedArticle, $response['value'][$index]);
        }
    }

    private function assertValidParsedArticle(array $parsedArticle, array $mockedArticle): void
    {
        $this->assertValidParsedArticleKeys($parsedArticle);
        $this->assertValidParsedArticleData($parsedArticle, $mockedArticle);
        $this->assertValidParsedArticleDateTimeFormats($parsedArticle);
    }

    private function assertValidParsedArticleKeys(array $parsedArticle): void
    {
        $this->assertArrayHasKey('headline', $parsedArticle);
        $this->assertArrayHasKey('article_url', $parsedArticle);
        $this->assertArrayHasKey('author', $parsedArticle);
        $this->assertArrayHasKey('content', $parsedArticle);
        $this->assertArrayHasKey('image_url', $parsedArticle);
        $this->assertArrayHasKey('news_website', $parsedArticle);
        $this->assertArrayHasKey('published_at', $parsedArticle);
        $this->assertArrayHasKey('fetched_at', $parsedArticle);
        $this->assertArrayHasKey('country', $parsedArticle);
        $this->assertArrayHasKey('language', $parsedArticle);
        $this->assertArrayHasKey('category', $parsedArticle);
        $this->assertArrayHasKey('keywords', $parsedArticle);
    }

    private function assertValidParsedArticleData(array $parsedArticle, array $mockedArticle): void
    {
        $imageUrl = $mockedArticle['image']['thumbnail']['contentUrl'] ?? null;

        $this->assertEquals($mockedArticle['name'], $parsedArticle['headline']);
        $this->assertEquals($mockedArticle['url'], $parsedArticle['article_url']);
        $this->assertNull($parsedArticle['author']);
        $this->assertEquals($mockedArticle['description'], $parsedArticle['content']);
        $this->assertEquals($imageUrl, $parsedArticle['image_url']);
        $this->assertEquals($mockedArticle['provider'][0]['name'], $parsedArticle['news_website']);
    }

    private function assertValidParsedArticleDateTimeFormats(array $parsedArticle): void // CHANGE NAME OF FUNCTION
    {
        $this->assertValidDateTimeFormat($parsedArticle['published_at']);
        $this->assertValidDateTimeFormat($parsedArticle['fetched_at']);
    }

    private function assertValidDateTimeFormat(?string $dateTimeString): void
    {
        if (!$dateTimeString) {
            return;
        }
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $this->assertInstanceOf(\DateTime::class, $dateTime);
        $this->assertEquals($dateTimeString, $dateTime->format('Y-m-d H:i:s'));
    }

    private function getKeywords($mockedArticle): ?string
    {
        return isset($mockedArticle['about']) ? json_encode($mockedArticle['about'][0]['name']) : null;
    }

    private function responseProvider(): array
    {
        return [
            [
                [
                    'value' => [
                        [
                            'name' => 'Article 1',
                            'url' => 'https://example.com/article1',
                            'image' => [
                                'thumbnail' => [
                                    'contentUrl' => 'https://example.com/image1.jpg',
                                ],
                            ],
                            'description' => 'Article 1 content',
                            'provider' => [
                                [
                                    'name' => 'Example News',
                                    'image' => [
                                        'thumbnail' => [
                                            'contentUrl' => 'https://example.com/article1',
                                        ],
                                    ],
                                ],
                            ],
                            'datePublished' => '2023-06-19T08:00:00.0000000Z',
                        ],
                        [
                            'name' => 'Article 1',
                            'url' => 'https://example.com/article1',
                            'image' => [
                                'thumbnail' => [
                                    'contentUrl' => 'https://example.com/image2.jpg',
                                ],
                            ],
                            'description' => 'Article 1 content',
                            'provider' => [
                                [
                                    'name' => 'Example News',
                                    'image' => [
                                        'thumbnail' => [
                                            'contentUrl' => 'https://example.com/article1',
                                        ],
                                    ],
                                ],
                            ],
                            'datePublished' => '2023-06-19T12:00:00.0000000Z',
                        ],
                    ],
                ],
            ],
            [
                [
                    'value' => [
                        [
                            'name' => 'Article 2',
                            'url' => 'https://example.com/article2',
                            'description' => 'Article 2 content',
                            'provider' => [
                                [
                                    'name' => 'Example News',
                                ],
                            ],
                            'datePublished' => null,
                        ],
                    ]
                ]
            ],
        ];
    }
}
