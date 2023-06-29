<?php

namespace Tests\Unit\Services\NewsParser;

use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
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
        $this->assertEquals($parsedArticle['headline'], $expectedParsedArticle['headline']);
        $this->assertEquals($parsedArticle['article_url'], $expectedParsedArticle['article_url']);
        $this->assertEquals($parsedArticle['news_website'], $expectedParsedArticle['news_website']);
        $this->assertEquals($parsedArticle['content'], $expectedParsedArticle['content']);
        $this->assertEquals($parsedArticle['image_url'], $expectedParsedArticle['image_url']);
        $this->assertEquals($parsedArticle['author'], $expectedParsedArticle['author']);
        $this->assertEquals($parsedArticle['published_at'], $expectedParsedArticle['published_at']);
        $this->assertEquals($parsedArticle['fetched_at'], $expectedParsedArticle['fetched_at']);
    }

    private function getKeywords(array $mockedArticle): ?string
    {
        return isset($mockedArticle['keywords']) ? json_encode($mockedArticle['keywords']) : null;
    }

    private function getCategories(array $mockedArticle): ?string
    {
        return isset($mockedArticle['category']) ? json_encode($mockedArticle['category']) : null;
    }

    private function getCountry(array $mockedArticle): ?string
    {
        return isset($mockedArticle['country']) ? json_encode($mockedArticle['country']) : null;
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
                            'pubDate' => '2023-06-19 06:22:45',
                            'image_url' => 'https://example.com/image1.jpg',
                            'source_id' => 'full_count',
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
                        'published_at' => '2023-06-19 15:22:45',
                        'fetched_at' => date('Y-m-d H:i:s'),
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
                            'pubDate' => '2023-06-19 19:19:47',
                            'image_url' => 'https://example.com/image2.jpg',
                            'source_id' => 'full_count',
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
                        'published_at' => '2023-06-20 04:19:47',
                        'fetched_at' => date('Y-m-d H:i:s'),
                    ],
                ],
            ],
            [
                [
                    'results' => [
                        'title' => 'Article 3',
                        'link' => 'https://example.com/article3',
                        'keywords' => null,
                        'creator' => null,
                        'video_url' => null,
                        'description' => 'Article 3 description',
                        'content' => 'Article 3 content',
                        'pubDate' => null,
                        'image_url' => 'https://example.com/image3.jpg',
                        'source_id' => 'full_count',
                        'category' => ['sports'],
                        'country' => ['japan'],
                        'language' => 'japanese',
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
                        'fetched_at' => date('Y-m-d H:i:s'),
                    ],
                ],
            ],
        ];
    }
}
