<?php

namespace Tests\Unit\Services\NewsParser;

use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class NewsParserForNewsDataIoTest extends TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testGetParsedData(array $mockedResponse, array $expectedParsedResult): void
    {
        $parser = new NewsParserForNewsDataIo();
        $parsedData = $parser->getParsedData($mockedResponse);
        foreach ($parsedData as $index => $article) {
            $this->assertValidParsedArticle($article, $expectedParsedResult[$index]);
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
        $this->assertEquals($expectedParsedArticle['headline'], $parsedArticle['headline']);
        $this->assertEquals($expectedParsedArticle['article_url'], $parsedArticle['article_url']);
        $this->assertEquals($expectedParsedArticle['news_website'], $parsedArticle['news_website']);
        $this->assertEquals($expectedParsedArticle['content'], $parsedArticle['content']);
        $this->assertEquals($expectedParsedArticle['image_url'], $parsedArticle['image_url']);
        $this->assertEquals($expectedParsedArticle['author'], $parsedArticle['author']);
        $this->assertEquals($expectedParsedArticle['published_at'], $parsedArticle['published_at']);
        $this->assertNotNull($parsedArticle['fetched_at']);
    }

    private function responseProvider(): array
    {
        return [
            [
                [
                    'results' => [
                        [
                            'title' => 'Article 1',
                            'link' => 'https://example.com/article1',
                            'creator' => ['Example News'],
                            'content' => 'Article 1 content',
                            'pubDate' => '2023-01-01 00:00:00',
                            'image_url' => 'https://example.com/image1.jpg',
                            'source_id' => 'full_count',
                            'language' => 'japanese',
                            'country' => [
                                'japan',
                                'india',
                            ],
                            'category' => [
                                'general',
                                'business',
                            ],
                            'keywords' => [
                                'keyword1',
                                'keyword2',
                                'keyword3',
                                'keyword4',
                            ],
                        ],
                    ],
                ],
                [
                    [
                        'headline' => 'Article 1',
                        'article_url' => 'https://example.com/article1',
                        'author' => 'Example News',
                        'content' => 'Article 1 content',
                        'image_url' => 'https://example.com/image1.jpg',
                        'news_website' => 'full_count',
                        'published_at' => Carbon::parse('2023-01-01 00:00:00', 'UTC')->tz('Asia/Tokyo')->format('Y-m-d H:i:s'),
                        'fetched_at' => now()->format('Y-m-d H:i:s'),
                        'country' => '["japan","india"]',
                        'language' => 'japanese',
                        'category' => '["general","business"]',
                        'keywords' => '["keyword1","keyword2","keyword3","keyword4"]',
                    ],
                ],
            ],
            [
                [
                    'results' => [
                        [
                            'title' => 'Article 2',
                            'link' => 'https://example.com/article2',
                            'creator' => null,
                            'content' => 'Article 2 content',
                            'pubDate' => '2022-01-01 00:00:00',
                            'image_url' => 'https://example.com/image2.jpg',
                            'source_id' => 'full_count',
                            'country' => ["japan"],
                            'language' => 'japanese',
                            'category' => ["general"],
                            'keywords' => [
                                "keyword1"
                                ],
                        ],
                    ],
                ],
                [
                    [
                        'headline' => 'Article 2',
                        'article_url' => 'https://example.com/article2',
                        'author' => null,
                        'content' => 'Article 2 content',
                        'image_url' => 'https://example.com/image2.jpg',
                        'news_website' => 'full_count',
                        'published_at' => Carbon::parse('2022-01-01 00:00:00', 'UTC')->tz('Asia/Tokyo')->format('Y-m-d H:i:s'),
                        'fetched_at' => now()->format('Y-m-d H:i:s'),
                        'country' => ["japan"],
                        'language' => 'japanese',
                        'category' => ["general"],
                        'keywords' => ["keyword1"],
                    ],
                ],
            ],
            [
                [
                    'results' => [
                        [
                            'title' => 'Article 3',
                            'link' => 'https://example.com/article3',
                            'creator' => null,
                            'content' => 'Article 3 content',
                            'pubDate' => null,
                            'image_url' => null,
                            'source_id' => 'full_count',
                            'country' => ["japan"],
                            'language' => 'japanese',
                            'category' => null,
                            'keywords' => null,
                        ],
                    ],
                    'nextPage' => null,
                ],
                [
                    [
                        'headline' => 'Article 3',
                        'article_url' => 'https://example.com/article3',
                        'author' => null,
                        'content' => 'Article 3 content',
                        'image_url' => null,
                        'news_website' => 'full_count',
                        'published_at' => null,
                        'fetched_at' => now()->format('Y-m-d H:i:s'),
                        'country' => ["japan"],
                        'language' => 'japanese',
                        'category' => null,
                        'keywords' => null,
                    ],
                ],
            ],
        ];
    }
}
