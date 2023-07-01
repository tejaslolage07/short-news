<?php

namespace Tests\Unit\Services;

use App\Jobs\SummarizeArticle;
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
 */

/**
 * @internal
 *
 * @coversNothing
 */
class NewsHandlerTest extends TestCase
{
    use DatabaseTransactions;

    public function testFetchAndStoreNewsFromNewsDataIo()
    {
        $response = $this->getFakeResponse();

        $newsFetcher = \Mockery::mock('overload:App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo');
        $newsFetcher->shouldReceive('fetch')
            ->andReturn($response)
        ;
        $newsParser = new NewsParserForNewsDataIo();
        Queue::fake();

        $newsFetcher = new NewsFetcherForNewsDataIo(new ChunkFetcherForNewsDataIo());
        $service = new NewsHandler($newsFetcher, $newsParser);
        $service->fetchAndStoreNewsFromNewsDataIo();
        Queue::assertPushed(SummarizeArticle::class, 1);

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

    private function getFakeResponse(): array
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
                    'pubDate' => '2023-07-01 04:00:00',
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
        ];
    }
}
