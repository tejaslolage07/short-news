<?php

namespace Tests\Unit\Services\NewsFetcher;

use App\Services\NewsFetcher\ParserForBing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ParserForBingTest extends TestCase
{
    /**
     * @dataProvider getMockedResponse
     */
    public function testGetParsedData(array $response): void
    {
        $parser = new ParserForBing();
        $parsedData = $parser->getParsedData($response);
        $this->assertCount(2, $parsedData);
        foreach ($parsedData as $index => $parsedArticle) {
            $this->testSingleArticle(parsedArticle: $parsedArticle, index: $index, mockedArticle: $response['value'][$index]);
        }
    }

    private function testSingleArticle(array $parsedArticle, int $index, array $mockedArticle): void
    {
        $this->testKeys($parsedArticle);
        $this->testDataFiltering(parsedArticle: $parsedArticle, mockedArticle: $mockedArticle);
        $this->testDateTimeFormat($parsedArticle);
    }

    private function testKeys(array $parsedArticle): void
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

    private function testDataFiltering(array $parsedArticle, array $mockedArticle): void
    {
        $imageUrl = $mockedArticle['image']['thumbnail']['contentUrl'] ?? null;

        $this->assertEquals($mockedArticle['name'], $parsedArticle['headline']);
        $this->assertEquals($mockedArticle['url'], $parsedArticle['article_url']);
        $this->assertNull($parsedArticle['author']);
        $this->assertEquals($mockedArticle['description'], $parsedArticle['content']);
        $this->assertEquals($imageUrl, $parsedArticle['image_url']);
        $this->assertEquals($mockedArticle['provider'][0]['name'], $parsedArticle['news_website']);
    }

    private function testDateTimeFormat(array $parsedArticle): void
    {
        $this->assertDateTimeFormat($parsedArticle['published_at']);
        $this->assertDateTimeFormat($parsedArticle['fetched_at']);
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

    private function getMockedResponse(): array
    {
        return [[[
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
                    'description' => 'Article 2 content',
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
        ]]];
    }
}
