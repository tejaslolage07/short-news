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
     * @dataProvider getMockedResponse
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
        $this->assertArrayHasKey('country', $parsedArticle);
        $this->assertArrayHasKey('language', $parsedArticle);
        $this->assertArrayHasKey('category', $parsedArticle);
        $this->assertArrayHasKey('keywords', $parsedArticle);
    }

    private function assertValidParsedArticleData(array $parsedArticle, array $mockedArticle): void
    {
        $author = $this->getAuthorFromMockedData($mockedArticle);
        $country = $this->getCountry($mockedArticle);
        $keywords = $this->getKeywords($mockedArticle);
        $categories = $this->getCategories($mockedArticle);
        $this->assertEquals($mockedArticle['title'], $parsedArticle['headline']);
        $this->assertEquals($mockedArticle['link'], $parsedArticle['article_url']);
        $this->assertEquals($mockedArticle['source_id'], $parsedArticle['news_website']);
        $this->assertEquals($mockedArticle['content'], $parsedArticle['content']);
        $this->assertEquals($mockedArticle['image_url'], $parsedArticle['image_url']);
        $this->assertEquals($author, $parsedArticle['author']);
        $this->assertEquals($country, $parsedArticle['country']);
        $this->assertEquals($mockedArticle['language'], $parsedArticle['language']);
        $this->assertEquals($categories, $parsedArticle['category']);
        $this->assertEquals($keywords, $parsedArticle['keywords']);
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

    private function getMockedResponse(): array
    {
        return [
            [
               [
                    'status' => 'success',
                    'totalResults' => 821,
                    'results' => [
                        [
                            'title' => 'Article 1',
                            'link' => 'https://example.com/article1',
                            'keywords' => [
                                'プロ野球',
                                'オールスター',
                            ],
                            'creator' => ['Example News'],
                            'video_url' => null,
                            'description' => 'Article 2 description',
                            'content' => 'Article 1 content',
                            'pubDate' => '2023-06-19 06:22:45',
                            'image_url' => 'https://example.com/image1.jpg',
                            'source_id' => 'full_count',
                            'category' => ['sports'],
                            'country' => ['japan'],
                            'language' => 'japanese',
                        ],
                    ],
                    'nextPage' => 'next_page_id_1',
                ],
                [
                    'results' => [
                        'title' => 'Article 2',
                        'link' => 'https://example.com/article2',
                        'keywords' => ['千葉ロッテマリーンズ'],
                        'creator' => null,
                        'video_url' => null,
                        'description' => 'Article 2 description',
                        'content' => 'Article 2 content',
                        'pubDate' => '2023-06-19 06:19:47',
                        'image_url' => 'https://example.com/image2.jpg',
                        'source_id' => 'full_count',
                        'category' => ['sports'],
                        'country' => ['japan'],
                        'language' => 'japanese',
                    ],
                    'nextPage' => 'next_page_id_2',
                ],
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
                ]
            ],
        ];
    }
}
    