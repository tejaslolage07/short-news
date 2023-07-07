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
    private const FETCHED_AT = '2023-01-01 00:00:00';
    /**
     * @dataProvider responseProvider
     */
    public function testGetParsedData(array $mockedResponse, array $expectedParsedResult): void
    {
        $parser = new NewsParserForNewsDataIo();
        $parsedData = $parser->getParsedData($mockedResponse, self::FETCHED_AT);
        foreach ($parsedData as $index => $article) {
            $this->assertValidParsedArticle($article, $expectedParsedResult[$index]);
        }
    }

    private function assertValidParsedArticle(array $originalParsedArticle, array $expectedParsedArticle): void
    {
        $this->assertValidParsedArticleKeys($originalParsedArticle);
        $this->assertValidParsedArticleData($originalParsedArticle, $expectedParsedArticle);
    }

    private function assertValidParsedArticleKeys(array $originalParsedArticle): void
    {
        $this->assertArrayHasKey('headline', $originalParsedArticle);
        $this->assertArrayHasKey('article_url', $originalParsedArticle);
        $this->assertArrayHasKey('author', $originalParsedArticle);
        $this->assertArrayHasKey('content', $originalParsedArticle);
        $this->assertArrayHasKey('image_url', $originalParsedArticle);
        $this->assertArrayHasKey('news_website', $originalParsedArticle);
        $this->assertArrayHasKey('published_at', $originalParsedArticle);
        $this->assertArrayHasKey('fetched_at', $originalParsedArticle);
        $this->assertArrayHasKey('country', $originalParsedArticle);
        $this->assertArrayHasKey('language', $originalParsedArticle);
        $this->assertArrayHasKey('category', $originalParsedArticle);
        $this->assertArrayHasKey('keywords', $originalParsedArticle);
    }

    private function assertValidParsedArticleData(array $originalParsedArticle, array $expectedParsedArticle): void
    {
        $this->assertEquals($expectedParsedArticle['headline'], $originalParsedArticle['headline']);
        $this->assertEquals($expectedParsedArticle['article_url'], $originalParsedArticle['article_url']);
        $this->assertEquals($expectedParsedArticle['news_website'], $originalParsedArticle['news_website']);
        $this->assertEquals($expectedParsedArticle['content'], $originalParsedArticle['content']);
        $this->assertEquals($expectedParsedArticle['image_url'], $originalParsedArticle['image_url']);
        $this->assertEquals($expectedParsedArticle['author'], $originalParsedArticle['author']);
        $this->assertEquals($expectedParsedArticle['published_at'], $originalParsedArticle['published_at']);
        $this->assertEquals(self::FETCHED_AT, $originalParsedArticle['fetched_at']);
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
