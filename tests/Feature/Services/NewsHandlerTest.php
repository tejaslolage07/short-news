<?php

namespace Tests\Feature\Services;

use App\Jobs\SummarizeArticle;
use App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsHandler;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
use App\Services\S3StorageService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
class NewsHandlerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @dataProvider getFakeResponseWhenDatePassed
     *
     * @param mixed $fakedResponse
     * @param mixed $expectedResponse
     */
    public function testFetchAndStoreNewsFromNewsDataIoWhenDatePassed($fakedResponse, $expectedResponse)
    {
        $chunkFetcher = \Mockery::mock('overload:App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo');
        $chunkFetcher->shouldReceive('fetchChunk')
            ->andReturn($fakedResponse)
        ;
        $chunkFetcher = new ChunkFetcherForNewsDataIo();
        $S3StorageService = \Mockery::mock('overload:App\Services\S3StorageService');
        $S3StorageService->shouldReceive('writeToS3Bucket')
            ->andReturn('test1')
        ;
        $S3StorageService = new S3StorageService();
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $newsParser = new NewsParserForNewsDataIo();
        Queue::fake();
        $service = new NewsHandler($newsFetcher, $newsParser, $S3StorageService);
        $service->fetchAndStoreNewsFromNewsDataIo('2020-01-01 00:00:00');
        Queue::assertPushed(SummarizeArticle::class, 1);

        foreach ($expectedResponse as $index => $expectedResponseArticle) {
            $this->assertDatabaseHas('articles', [
                'headline' => $expectedResponseArticle['headline'],
                'article_url' => $expectedResponseArticle['article_url'],
                'author' => $expectedResponseArticle['author'],
                'image_url' => $expectedResponseArticle['image_url'],
                'published_at' => $expectedResponseArticle['published_at'],
                'article_s3_filename' => 'test'.$index + 1,
                'short_news' => null,
            ]);
        }
    }

    /**
     * @dataProvider getFakeResponseWhenDateNotPassed
     *
     * @param mixed $fakedResponse
     * @param mixed $expectedResponse
     */
    public function testFetchAndStoreNewsFromNewsDataIoWhenDateNotPassed($fakedResponse, $expectedResponse)
    {
        $chunkFetcher = \Mockery::mock('overload:App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo');
        $chunkFetcher->shouldReceive('fetchChunk')
            ->andReturn($fakedResponse)
        ;
        $chunkFetcher = new ChunkFetcherForNewsDataIo();
        $S3StorageService = \Mockery::mock('overload:App\Services\S3StorageService');
        $S3StorageService->shouldReceive('writeToS3Bucket')
            ->andReturn('test1', 'test2')
        ;
        $S3StorageService = new S3StorageService();
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $newsParser = new NewsParserForNewsDataIo();
        Queue::fake();
        $service = new NewsHandler($newsFetcher, $newsParser, $S3StorageService);
        $service->fetchAndStoreNewsFromNewsDataIo();
        Queue::assertPushed(SummarizeArticle::class, 2);

        foreach ($expectedResponse as $index => $expectedResponseArticle) {
            $this->assertDatabaseHas('articles', [
                'headline' => $expectedResponseArticle['headline'],
                'article_url' => $expectedResponseArticle['article_url'],
                'author' => $expectedResponseArticle['author'],
                'image_url' => $expectedResponseArticle['image_url'],
                'published_at' => $expectedResponseArticle['published_at'],
                'article_s3_filename' => 'test'.$index + 1,
                'short_news' => null,
            ]);
        }
    }

    private function getFakeResponseWhenDatePassed(): array
    {
        return [
            [
                [
                    'results' => [
                        [
                            'title' => 'Headline 1',
                            'link' => 'https://example.com/1',
                            'keywords' => [
                                'Keyword 1',
                            ],
                            'creator' => [
                                'Author 1',
                            ],
                            'content' => 'Content 1',
                            'pubDate' => Carbon::parse('2021-01-01 00:00:00', 'Asia/Tokyo')->tz('UTC')->format('Y-m-d H:i:s'),
                            'image_url' => 'https://example.com/image1.jpg',
                            'source_id' => 'NewsWebsite 1',
                            'category' => [
                                'top',
                            ],
                            'country' => [
                                'japan',
                            ],
                            'language' => 'japanese',
                        ],
                        [
                            'title' => 'Headline 1',
                            'link' => 'https://example.com/1',
                            'keywords' => [
                                'Keyword 1',
                            ],
                            'creator' => [
                                'Author 1',
                            ],
                            'content' => 'Content 1',
                            'pubDate' => Carbon::parse('2021-01-01 00:00:00', 'Asia/Tokyo')->tz('UTC')->format('Y-m-d H:i:s'),
                            'image_url' => 'https://example.com/image2.jpg',
                            'source_id' => 'NewsWebsite 1',
                            'category' => [
                                'top',
                            ],
                            'country' => [
                                'japan',
                            ],
                            'language' => 'japanese',
                        ],
                        [
                            'title' => 'Headline 3',
                            'link' => 'https://example.com/3',
                            'keywords' => [
                                'Keyword 3',
                            ],
                            'creator' => [
                                'Author 3',
                            ],
                            'content' => 'Content 3',
                            'pubDate' => Carbon::parse('2019-12-31 23:59:59', 'Asia/Tokyo')->tz('UTC')->format('Y-m-d H:i:s'),
                            'image_url' => 'https://example.com/image3.jpg',
                            'source_id' => 'NewsWebsite 3',
                            'category' => [
                                'top',
                            ],
                            'country' => [
                                'japan',
                            ],
                            'language' => 'japanese',
                        ],
                    ],
                    'nextPage' => 'nextPageString',
                ],
                [
                    [
                        'headline' => 'Headline 1',
                        'article_url' => 'https://example.com/1',
                        'keywords' => [
                            'Keyword 1',
                        ],
                        'author' => 'Author 1',
                        'content' => 'Content 1',
                        'published_at' => Carbon::parse('2021-01-01 00:00:00', 'Asia/Tokyo')->tz('Asia/Tokyo')->format('Y-m-d H:i:s'),
                        'image_url' => 'https://example.com/image1.jpg',
                        'NewsWebsite' => 'NewsWebsite 1',
                        'category' => [
                            'top',
                        ],
                        'country' => [
                            'japan',
                        ],
                        'language' => 'ja',
                        'short-news' => null,
                    ],
                ],
            ],
        ];
    }

    private function getFakeResponseWhenDateNotPassed(): array
    {
        $UTCnow = now('UTC')->format('Y-m-d H:i:s');
        $UTCnowSub5Hours = now('UTC')->subHours(5)->format('Y-m-d H:i:s');
        $UTCnowSub7Hours = now('UTC')->subHours(7)->format('Y-m-d H:i:s');
        return [
            [
                [
                    'results' => [
                        [
                            'title' => 'Headline 1',
                            'link' => 'https://example.com/1',
                            'keywords' => [
                                'Keyword 1',
                            ],
                            'creator' => [
                                'Author 1',
                            ],
                            'content' => 'Content 1',
                            'pubDate' => $UTCnow,
                            'image_url' => 'https://example.com/image1.jpg',
                            'source_id' => 'NewsWebsite 1',
                            'category' => [
                                'top',
                            ],
                            'country' => [
                                'japan',
                            ],
                            'language' => 'japanese',
                        ],
                        [
                            'title' => 'Headline 1',
                            'link' => 'https://example.com/1',
                            'keywords' => [
                                'Keyword 1',
                            ],
                            'creator' => [
                                'Author 1',
                            ],
                            'content' => 'Content 1',
                            'pubDate' => $UTCnow,
                            'image_url' => 'https://example.com/image1.jpg',
                            'source_id' => 'NewsWebsite 1',
                            'category' => [
                                'top',
                            ],
                            'country' => [
                                'japan',
                            ],
                            'language' => 'japanese',
                        ],
                        [
                            'title' => 'Headline 2',
                            'link' => 'https://example.com/2',
                            'keywords' => [
                                'Keyword 2',
                            ],
                            'creator' => [
                                'Author 2',
                            ],
                            'content' => 'Content 2',
                            'pubDate' => $UTCnowSub5Hours,
                            'image_url' => 'https://example.com/image2.jpg',
                            'source_id' => 'NewsWebsite 2',
                            'category' => [
                                'top',
                            ],
                            'country' => [
                                'japan',
                            ],
                            'language' => 'japanese',
                        ],
                        [
                            'title' => 'Headline 3',
                            'link' => 'https://example.com/3',
                            'keywords' => [
                                'Keyword 3',
                            ],
                            'creator' => [
                                'Author 3',
                            ],
                            'content' => 'Content 3',
                            'pubDate' => $UTCnowSub7Hours,
                            'image_url' => 'https://example.com/image3.jpg',
                            'source_id' => 'NewsWebsite 3',
                            'category' => [
                                'top',
                            ],
                            'country' => [
                                'japan',
                            ],
                            'language' => 'japanese',
                        ],
                    ],
                    'nextPage' => 'NextPageString',
                ],
                [
                    [
                        'headline' => 'Headline 1',
                        'article_url' => 'https://example.com/1',
                        'keywords' => [
                            'Keyword 1',
                        ],
                        'author' => 'Author 1',
                        'content' => 'Content 1',
                        'published_at' => Carbon::parse($UTCnow, 'UTC')->tz('Asia/Tokyo')->format('Y-m-d H:i:s'),
                        'image_url' => 'https://example.com/image1.jpg',
                        'NewsWebsite' => 'NewsWebsite 1',
                        'category' => [
                            'top',
                        ],
                        'country' => [
                            'japan',
                        ],
                        'language' => 'ja',
                        'short-news' => null,
                    ],
                    [
                        'headline' => 'Headline 2',
                        'article_url' => 'https://example.com/2',
                        'keywords' => [
                            'Keyword 2',
                        ],
                        'author' => 'Author 2',
                        'content' => 'Content 2',
                        'published_at' => Carbon::parse($UTCnowSub5Hours, 'UTC')->tz('Asia/Tokyo')->format('Y-m-d H:i:s'),
                        'image_url' => 'https://example.com/image2.jpg',
                        'NewsWebsite' => 'NewsWebsite 2',
                        'category' => [
                            'top',
                        ],
                        'country' => [
                            'japan',
                        ],
                        'language' => 'ja',
                        'short-news' => null,
                    ],
                ],
            ],
        ];
    }
}
