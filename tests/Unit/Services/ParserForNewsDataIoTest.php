<?php

namespace Tests\Unit\Services\NewsFetcher;

use App\Services\NewsFetcher\ParserForNewsDataIo;
use DateTime;
use Tests\TestCase;

class ParserForNewsDataIoTest extends TestCase
{
    public function testGetParsedData()
    {
        $parser = new ParserForNewsDataIo();

        $response = $this->getMockedResponse();

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

        $this->assertEquals('Article 2', $firstArticle['headline']);    // Article 2 will come first because we are using array_unshift in the ParserForNewsDataIo class. (Needed for writing latest news to database at last)
        $this->assertEquals('https://example.com/article2', $firstArticle['url']);
        $this->assertNull($firstArticle['author']);
        $this->assertEquals('Article 2 content', $firstArticle['content']);
        $this->assertEquals('https://example.com/image2.jpg', $firstArticle['imageURL']);
        $this->assertEquals('Example News', $firstArticle['sourceWebsite']);
        $this->assertDateTimeFormat($firstArticle['publishedAt']);
        $this->assertDateTimeFormat($firstArticle['fetchedAt']);

        $secondArticle = $parsedData[1];
        $this->assertEquals('Article 1', $secondArticle['headline']);
        $this->assertEquals('https://example.com/article1', $secondArticle['url']);
        $this->assertNull($secondArticle['author']);
        $this->assertEquals('Article 1 content', $secondArticle['content']);
        $this->assertEquals('https://example.com/image1.jpg', $secondArticle['imageURL']);
        $this->assertEquals('Example News', $secondArticle['sourceWebsite']);
        $this->assertDateTimeFormat($secondArticle['publishedAt']);
        $this->assertDateTimeFormat($secondArticle['fetchedAt']);
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
            "status" => "success",
            "totalResults"=> 821,
            "results"=> [
                            [
                                "title"=> "Article 1",
                                "link"=> "https://example.com/article1",
                                "keywords"=> [
                                    "プロ野球",
                                    "オールスター"
                                ],
                                "creator"=> "Example News",
                                "video_url"=> null,
                                "description"=> "Article 2 description",
                                "content"=> "Article 1 content",
                                "pubDate"=> "2023-06-19 06:22:45",
                                "image_url"=> "https://example.com/image1.jpg",
                                "source_id"=> "full_count",
                                "category"=> ["sports"],
                                "country"=> ["japan"],
                                "language"=> "japanese"
                            ],
                            [
                                "title"=> "Article 2",
                                "link"=> "https://example.com/article2",
                                "keywords"=> ["千葉ロッテマリーンズ"],
                                "creator"=> "Example News",
                                "video_url"=> null,
                                "description"=> "Article 2 description",
                                "content"=> "Article 2 content",
                                "pubDate"=> "2023-06-19 06:19:47",
                                "image_url"=> "https://example.com/image2.jpg",
                                "source_id"=> "full_count",
                                "category"=> ["sports"],
                                "country"=> ["japan"],
                                "language"=> "japanese"
                            ]
                        ],
                        "nextPage"=> "16871454007e049d1b98aefab703130569eb04393c"
        ];

        return json_encode($response);
    }
}
