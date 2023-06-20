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

        $response = $this->getMockedResponse(); // Create a mocked response for testing

        $parsedData = $parser->getParsedData($response);

        $this->assertCount(2, $parsedData);

        $firstArticle = $parsedData[0];
        $this->assertArrayHasKey('headline', $firstArticle);
        $this->assertArrayHasKey('url', $firstArticle);
        $this->assertArrayHasKey('author', $firstArticle);
        $this->assertArrayHasKey('content', $firstArticle);
        $this->assertArrayHasKey('imageURL', $firstArticle);
        $this->assertArrayHasKey('sourceWebsite', $firstArticle);
        $this->assertArrayHasKey('publishedAt', $firstArticle);
        $this->assertArrayHasKey('fetchedAt', $firstArticle);

        $this->assertEquals('Article 1', $firstArticle['headline']);
        $this->assertEquals('https://example.com/article1', $firstArticle['url']);
        $this->assertNull($firstArticle['author']);
        $this->assertEquals('Article 1 content', $firstArticle['content']);
        $this->assertEquals('https://example.com/image1.jpg', $firstArticle['imageURL']);
        $this->assertEquals('Example News', $firstArticle['sourceWebsite']);
        $this->assertDateTimeFormat($firstArticle['publishedAt']);
        $this->assertDateTimeFormat($firstArticle['fetchedAt']);

        $secondArticle = $parsedData[1];
        $this->assertEquals('Article 2', $secondArticle['headline']);
        $this->assertEquals('https://example.com/article2', $secondArticle['url']);
        $this->assertNull($secondArticle['author']);
        $this->assertEquals('Article 2 content', $secondArticle['content']);
        $this->assertEquals('https://example.com/image2.jpg', $secondArticle['imageURL']);
        $this->assertEquals('Example News', $secondArticle['sourceWebsite']);
        $this->assertDateTimeFormat($firstArticle['publishedAt']);
        $this->assertDateTimeFormat($firstArticle['fetchedAt']);
    }

    private function assertDateTimeFormat($dateTimeString)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertEquals($dateTimeString, $dateTime->format('Y-m-d H:i:s'));
    }

    private function getMockedResponse()
    {
        // Create a mocked response for testing
        $response = [
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

        return json_encode($response);
    }
}
