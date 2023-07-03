<?php

namespace Tests\Unit\Services\NewsParser;

use App\Services\NewsHandler\NewsParser\NewsParserForBing;
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
    public function testGetParsedData(array $mockedResponse, array $expectedParsedResult): void
    {
        $parser = new NewsParserForBing();
        $parsedData = $parser->getParsedData($mockedResponse);
        foreach ($parsedData as $index => $parsedArticle) {
            $this->assertValidParsedArticle($parsedArticle, $expectedParsedResult[$index]);
        }
    }

    private function assertValidParsedArticle(array $parsedArticle, array $expectedParsedArticle): void
    {
        $this->assertValidParsedArticleKeys($parsedArticle);
        $this->assertValidParsedArticleData($parsedArticle, $expectedParsedArticle);
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
    }

    private function assertValidParsedArticleData(array $parsedArticle, array $expectedParsedArticle): void
    {
        $this->assertEquals($expectedParsedArticle['headline'], $parsedArticle['headline']);
        $this->assertEquals($expectedParsedArticle['article_url'], $parsedArticle['article_url']);
        $this->assertEquals($expectedParsedArticle['author'], $parsedArticle['author']);
        $this->assertEquals($expectedParsedArticle['content'], $parsedArticle['content']);
        $this->assertEquals($expectedParsedArticle['image_url'], $parsedArticle['image_url']);
        $this->assertEquals($expectedParsedArticle['news_website'], $parsedArticle['news_website']);
        $this->assertEquals($expectedParsedArticle['published_at'], $parsedArticle['published_at']);
        $this->assertNotNull($parsedArticle['fetched_at']);
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
                            'name' => 'Article 2',
                            'url' => 'https://example.com/article2',
                            'image' => [
                                'thumbnail' => [
                                    'contentUrl' => 'https://example.com/image2.jpg',
                                ],
                            ],
                            'description' => 'Article 2 content',
                            'provider' => [
                                [
                                    'name' => 'Example News',
                                    'image' => [
                                        'thumbnail' => [
                                            'contentUrl' => 'https://example.com/article2',
                                        ],
                                    ],
                                ],
                            ],
                            'datePublished' => '2023-06-19T12:00:00.0000000Z',
                        ],
                    ],
                ],
                [
                    [
                        'headline' => 'Article 1',
                        'article_url' => 'https://example.com/article1',
                        'author' => null,
                        'content' => 'Article 1 content',
                        'image_url' => 'https://example.com/image1.jpg',
                        'news_website' => 'Example News',
                        'published_at' => '2023-06-19 17:00:00',
                        'fetched_at' => now()->tz('UTC')->format('Y-m-d H:i:s'),
                    ],
                    [
                        'headline' => 'Article 2',
                        'article_url' => 'https://example.com/article2',
                        'author' => null,
                        'content' => 'Article 2 content',
                        'image_url' => 'https://example.com/image2.jpg',
                        'news_website' => 'Example News',
                        'published_at' => '2023-06-19 21:00:00',
                        'fetched_at' => now()->tz('UTC')->format('Y-m-d H:i:s'),
                    ],
                ],
            ],
            [
                [
                    'value' => [
                        [
                            'name' => 'Article 3',
                            'url' => 'https://example.com/article3',
                            'description' => 'Article 3 content',
                            'provider' => [
                                [
                                    'name' => 'Example News',
                                ],
                            ],
                            'datePublished' => null,
                        ],
                    ],
                ],
                [
                    [
                        'headline' => 'Article 3',
                        'article_url' => 'https://example.com/article3',
                        'author' => null,
                        'content' => 'Article 3 content',
                        'image_url' => null,
                        'news_website' => 'Example News',
                        'published_at' => null,
                        'fetched_at' => now()->tz('UTC')->format('Y-m-d H:i:s'),
                    ],
                ],
            ],
        ];
    }
}
