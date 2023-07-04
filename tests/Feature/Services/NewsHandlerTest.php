<?php

namespace Tests\Feature\Services;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsHandler;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
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
 * @preserveGlobalState disabled
 */
class NewsHandlerTest extends TestCase
{
    use DatabaseTransactions;

    public function testFetchAndStoreNewsFromNewsDataIoWhenDatePassed()
    {
        $response = $this->getFakeResponseWhenDatePassed();

        $chunkFetcher = \Mockery::mock('overload:App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo');
        $chunkFetcher->shouldReceive('fetchChunk')
        ->andReturn($response)
        ;
        $chunkFetcher = new ChunkFetcherForNewsDataIo();
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $newsParser = new NewsParserForNewsDataIo();
        Queue::fake();

        $initialQueueSize = Queue::size();
        $service = new NewsHandler($newsFetcher, $newsParser);
        $service->fetchAndStoreNewsFromNewsDataIo(now()->format('Y-m-d H:i:s'));
        Queue::assertPushed(SummarizeArticle::class, 1);
        $finalQueueSize = Queue::size();
        $this->assertEquals($initialQueueSize + 1, $finalQueueSize);

        $this->assertDatabaseHas('articles', [
            'headline' => $response['results'][0]['title'],
            'article_url' => $response['results'][0]['link'],
            'author' => $response['results'][0]['creator'][0],
            'image_url' => $response['results'][0]['image_url'],
            'published_at' => Carbon::parse($response['results'][0]['pubDate'], 'UTC')->tz('Asia/Tokyo')->format('Y-m-d H:i:s'),
            'article_s3_filename' => null,
            'short_news' => null,
        ]);
    }

    public function testFetchAndStoreNewsFromNewsDataIoWhenDateNotPassed()
    {
        $response = $this->getFakeResponseWhenDateNotPassed();

        $chunkFetcher = \Mockery::mock('overload:App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo');
        $chunkFetcher->shouldReceive('fetchChunk')
        ->andReturn($response)
        ;
        $chunkFetcher = new ChunkFetcherForNewsDataIo();
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $newsParser = new NewsParserForNewsDataIo();
        Article::factory()->create(['published_at' => now()->addHour()->tz('Asia/Tokyo'),]);
        Queue::fake();

        $initialQueueSize = Queue::size();
        $service = new NewsHandler($newsFetcher, $newsParser);
        $service->fetchAndStoreNewsFromNewsDataIo();
        Queue::assertPushed(SummarizeArticle::class, 2);
        $finalQueueSize = Queue::size();
        $this->assertEquals($initialQueueSize + 2, $finalQueueSize);

        $this->assertDatabaseHas('articles', [
            'headline' => $response['results'][0]['title'],
            'article_url' => $response['results'][0]['link'],
            'author' => $response['results'][0]['creator'][0],
            'image_url' => $response['results'][0]['image_url'],
            'published_at' => Carbon::parse($response['results'][0]['pubDate'], 'UTC')->tz('Asia/Tokyo')->format('Y-m-d H:i:s'),
            'article_s3_filename' => null,
            'short_news' => null,
        ]);
    }

    private function getFakeResponseWhenDatePassed(): array
    {
        return [
            'results' => [
                [
                    'title' => 'ASUSからクリエイター向けB760マザーボードが発売',
                    'link' => 'https://ascii.jp/elem/000/004/143/4143473/?rss',
                    'keywords' => [
                        'マザーボード',
                    ],
                    'creator' => [
                        '東中野ミツル',
                    ],
                    'content' => 'Some content',
                    'pubDate' => now('UTC')->addHour()->format('Y-m-d H:i:s'),
                    'image_url' => 'https://example.com/image.jpg',
                    'source_id' => 'ascii',
                    'category' => [
                        'top',
                    ],
                    'country' => [
                        'japan',
                    ],
                    'language' => 'japanese',
                ],
                [
                    'title' => 'ASUSからクリエイター向けB760マザーボードが発売',
                    'link' => 'https://ascii.jp/elem/000/004/143/4143473/?rss',
                    'keywords' => [
                        'マザーボード',
                    ],
                    'creator' => [
                        '東中野ミツル',
                    ],
                    'content' => 'Some content',
                    'pubDate' => now('UTC')->addHour()->format('Y-m-d H:i:s'),
                    'image_url' => 'https://example.com/image.jpg',
                    'source_id' => 'ascii',
                    'category' => [
                        'top',
                    ],
                    'country' => [
                        'japan',
                    ],
                    'language' => 'japanese',
                ],
                [
                    'title' => 'ASUSからクリエイター向けB760マザーボードが発売',
                    'link' => 'https://ascii.jp/elem/000/004/143/4143473/?rss123',
                    'keywords' => [
                        'マザーボード',
                    ],
                    'creator' => [
                        '東中野ミツル',
                    ],
                    'content' => 'Some content',
                    'pubDate' => now('UTC')->subHour()->format('Y-m-d H:i:s'),
                    'image_url' => 'https://example.com/image.jpg',
                    'source_id' => 'ascii',
                    'category' => [
                        'top',
                    ],
                    'country' => [
                        'japan',
                    ],
                    'language' => 'japanese',
                ],
            ],
            'nextPage' => 'NextPageString'
        ];
    }

    private function getFakeResponseWhenDateNotPassed(): array
    {
        return [
            'results' => [
                [
                    'title' => 'Headline 1',
                    'link' => 'https://example.com/1',
                    'keywords' => [
                        'Keyword 1',
                    ],
                    'creator' => [
                        'Author name',
                    ],
                    'content' => 'Some content',
                    'pubDate' => now('UTC')->addHours(2)->format('Y-m-d H:i:s'),
                    'image_url' => 'https://example.com/image.jpg',
                    'source_id' => 'ascii',
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
                        'Keyword 1',
                    ],
                    'creator' => [
                        'Author 1',
                    ],
                    'content' => 'Some content',
                    'pubDate' => now('UTC')->addHour()->format('Y-m-d H:i:s'),
                    'image_url' => 'https://example.com/image.jpg',
                    'source_id' => 'ascii',
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
                        'Keyword 1',
                    ],
                    'creator' => [
                        'Author 1',
                    ],
                    'content' => 'Some content',
                    'pubDate' => now('UTC')->format('Y-m-d H:i:s'),
                    'image_url' => 'https://example.com/image.jpg',
                    'source_id' => 'ascii',
                    'category' => [
                        'top',
                    ],
                    'country' => [
                        'japan',
                    ],
                    'language' => 'japanese',
                ],
            ],
            'nextPage' => 'NextPageString'
        ];
    }
}
