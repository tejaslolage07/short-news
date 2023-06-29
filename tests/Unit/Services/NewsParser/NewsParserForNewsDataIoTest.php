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
    public function testGetParsedData(array $response): void
    {
        $parser = new NewsParserForNewsDataIo();
        $parsedData = $parser->getParsedData($response);
        foreach ($parsedData as $index => $article) {
            $this->assertValidParsedArticle(parsedArticle: $article, mockedArticle: $response['results'][$index]);
        }
    }

    private function assertValidParsedArticle(array $parsedArticle, array $mockedArticle): void
    {
        $this->assertValidParsedArticleKeys($parsedArticle);
        $this->assertValidParsedArticleData(parsedArticle: $parsedArticle, mockedArticle: $mockedArticle);
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
    }

    private function assertValidParsedArticleData(array $parsedArticle, array $mockedArticle): void
    {
        $this->assertEquals($mockedArticle['title'], $parsedArticle['headline']);
        $this->assertEquals($mockedArticle['link'], $parsedArticle['article_url']);
        $this->assertEquals($mockedArticle['source_id'], $parsedArticle['news_website']);
        $this->assertEquals($mockedArticle['content'], $parsedArticle['content']);
        $this->assertEquals($mockedArticle['image_url'], $parsedArticle['image_url']);
        $this->assertEquals($this->getAuthorFromMockedData($mockedArticle), $parsedArticle['author']);
    }

    private function assertValidParsedArticleDateTimeFormats(array $parsedArticle): void
    {
        $this->assertDateTimeFormat($parsedArticle['published_at']);
        $this->assertDateTimeFormat($parsedArticle['fetched_at']);
    }

    private function getAuthorFromMockedData(array $mockedArticle): ?string
    {
        return $mockedArticle['creator'][0] ?? null;
    }

    private function assertDateTimeFormat(?string $dateTimeString): void
    {
        if (!$dateTimeString) {
            return;
        }
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $this->assertInstanceOf(\DateTime::class, $dateTime);
        $this->assertEquals($dateTimeString, $dateTime->format('Y-m-d H:i:s'));
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
                    'results' => [
                        [
                            'title' => 'Article 3',
                            'link' => 'https://example.com/article3',
                            'creator' => null,
                            'content' => 'Article 3 content',
                            'pubDate' => null,
                            'image_url' => null,
                            'source_id' => 'full_count',
                        ],
                    ],
                    'nextPage' => null,
                ]
            ],
        ];
    }
}
    