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
        $nextPage = $parser->getNextPage($response);
        $date1 = $parser->getPublishedAt($response, 0);
        $date2 = $parser->getPublishedAt($response, 1);

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

        $this->assertEquals('next_page_id', $nextPage);

        $this->assertEquals('2023-06-19 06:22:45', $date1);
        $this->assertEquals('2023-06-19 06:19:47', $date2);
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
            "results"=> 
            [
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
            "nextPage"=> "next_page_id"
        ];

        return json_encode($response);
    }
}
