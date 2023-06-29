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
     * @dataProvider getMockedResponse
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
        $this->assertArrayHasKey('country', $parsedArticle);
        $this->assertArrayHasKey('language', $parsedArticle);
        $this->assertArrayHasKey('category', $parsedArticle);
        $this->assertArrayHasKey('keywords', $parsedArticle);
    }

    private function assertValidParsedArticleData(array $parsedArticle, array $expectedParsedArticle): void
    {
        $this->assertEquals($parsedArticle['headline'], $expectedParsedArticle['headline']);
        $this->assertEquals($parsedArticle['article_url'], $expectedParsedArticle['article_url']);
        $this->assertEquals($parsedArticle['author'], $expectedParsedArticle['author']);
        $this->assertEquals($parsedArticle['content'], $expectedParsedArticle['content']);
        $this->assertEquals($parsedArticle['image_url'], $expectedParsedArticle['image_url']);
        $this->assertEquals($parsedArticle['news_website'], $expectedParsedArticle['news_website']);
        $this->assertEquals($parsedArticle['published_at'], $expectedParsedArticle['published_at']);
        $this->assertEquals($parsedArticle['fetched_at'], $expectedParsedArticle['fetched_at']);
    }

    private function getKeywords($mockedArticle): ?string
    {
        return isset($mockedArticle['about']) ? json_encode($mockedArticle['about'][0]['name']) : null;
    }

    private function getMockedResponse(): array
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
                                    'width' => 157,
                                    'height' => 118,
                                ],
                            ],
                            'description' => 'Article 1 content',
                            'provider' => [
                                [
                                    '_type' => 'Organization',
                                    'name' => 'Example News',
                                    'image' => [
                                        'thumbnail' => [
                                            'contentUrl' => 'https://example.com/article1',
                                        ],
                                    ],
                                ],
                            ],
                            'datePublished' => '2023-06-19T08:00:00.0000000Z',
                            'category' => 'World',
                            'headline' => true,
                        ],
                        [
                            'name' => 'Article 2',
                            'url' => 'https://example.com/article2',
                            'image' => [
                                'thumbnail' => [
                                    'contentUrl' => 'https://example.com/image2.jpg',
                                    'width' => 157,
                                    'height' => 118,
                                ],
                            ],
                            'description' => 'Article 2 content',
                            'provider' => [
                                [
                                    '_type' => 'Organization',
                                    'name' => 'Example News',
                                    'image' => [
                                        'thumbnail' => [
                                            'contentUrl' => 'https://example.com/article2',
                                        ],
                                    ],
                                ],
                            ],
                            'datePublished' => '2023-06-19T12:00:00.0000000Z',
                            'category' => 'World',
                            'headline' => true,
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
                        'fetched_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'headline' => 'Article 2',
                        'article_url' => 'https://example.com/article2',
                        'author' => null,
                        'content' => 'Article 2 content',
                        'image_url' => 'https://example.com/image2.jpg',
                        'news_website' => 'Example News',
                        'published_at' => '2023-06-19 21:00:00',
                        'fetched_at' => date('Y-m-d H:i:s'),
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
                                    '_type' => 'Organization',
                                    'name' => 'Example News',
                                ],
                            ],
                            'datePublished' => null,
                            'category' => 'World',
                            'headline' => true,
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
                        'fetched_at' => date('Y-m-d H:i:s'),
                    ],
                ],
            ],
        ];
    }
}
