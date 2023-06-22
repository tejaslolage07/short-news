<?php

namespace Tests\Unit\Services\NewsFetcher;

use App\Services\NewsFetcher\ParserForBing;
use DateTime;
use Tests\TestCase;

class ParserForBingTest extends TestCase
{
    public function testGetParsedData()
    {
        $parser = new ParserForBing();

        $response = $this->getMockedResponse();

        $parsedData = $parser->getParsedData($response);

        $this->assertCount(2, $parsedData);

        $firstArticle = $parsedData[0];
        $this->assertArrayHasKey('headline', $firstArticle);
        $this->assertArrayHasKey('article_url', $firstArticle);
        $this->assertArrayHasKey('author', $firstArticle);
        $this->assertArrayHasKey('content', $firstArticle);
        $this->assertArrayHasKey('image_url', $firstArticle);
        $this->assertArrayHasKey('news_website', $firstArticle);
        $this->assertArrayHasKey('published_at', $firstArticle);
        $this->assertArrayHasKey('fetched_at', $firstArticle);

        $this->assertEquals('Article 1', $firstArticle['headline']);
        $this->assertEquals('https://example.com/article1', $firstArticle['article_url']);
        $this->assertNull($firstArticle['author']);
        $this->assertEquals('Article 1 content', $firstArticle['content']);
        $this->assertEquals('https://example.com/image1.jpg', $firstArticle['image_url']);
        $this->assertEquals('Example News', $firstArticle['news_website']);
        $this->assertDateTimeFormat($firstArticle['published_at']);
        $this->assertDateTimeFormat($firstArticle['fetched_at']);

        $secondArticle = $parsedData[1];
        $this->assertEquals('Article 2', $secondArticle['headline']);
        $this->assertEquals('https://example.com/article2', $secondArticle['article_url']);
        $this->assertNull($secondArticle['author']);
        $this->assertEquals('Article 2 content', $secondArticle['content']);
        $this->assertEquals('https://example.com/image2.jpg', $secondArticle['image_url']);
        $this->assertEquals('Example News', $secondArticle['news_website']);
        $this->assertDateTimeFormat($secondArticle['published_at']);
        $this->assertDateTimeFormat($secondArticle['fetched_at']);
    }

    private function assertDateTimeFormat($dateTimeString)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertEquals($dateTimeString, $dateTime->format('Y-m-d H:i:s'));
    }

    private function getMockedResponse()
    {
        return [
            'value' => [
                [
                  "name"=> "Article 1",
                  "url"=> "https://example.com/article1",
                  "image"=> [
                    "thumbnail"=> [
                      "contentUrl"=> "https://example.com/image1.jpg",
                      "width"=> 157,
                      "height"=> 118
                    ]
                ],
                  "description"=> "Article 1 content",
                  "provider"=> [
                    [
                      "_type"=> "Organization",
                      "name"=> "Example News",
                      "image"=> [
                        "thumbnail"=> [
                          "contentUrl"=> "https://example.com/article1"
                        ]
                      ]
                    ]
                  ],
                    "datePublished"=> "2023-06-19T08:00:00.0000000Z",
                    "category"=> "World",
                    "headline"=> true,
                ],
                [
                    "name"=> "Article 2",
                    "url"=> "https://example.com/article2",
                    "image"=> [
                        "thumbnail"=> [
                            "contentUrl"=> "https://example.com/image2.jpg",
                            "width"=> 157,
                            "height"=> 118
                            ]
                        ],
                        "description"=> "Article 2 content",
                        "provider"=> [
                            [
                                "_type"=> "Organization",
                                "name"=> "Example News",
                                "image"=> [
                                    "thumbnail"=> [
                                        "contentUrl"=> "https://www.bing.com/th?id=ODF.50m4seCpaIwmEWPnQh7pfg&pid=news"
                                    ]
                                ]
                            ]
                        ],
                    "datePublished"=> "2023-06-19T08:00:00.0000000Z",
                    "category"=> "World",
                    "headline"=> true,
                ],
            ]
        ];
    }
}
