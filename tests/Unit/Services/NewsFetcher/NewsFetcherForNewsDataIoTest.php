<?php

use App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use Carbon\Carbon;
use Database\Factories\ArticleFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

/**
 * @internal
 *
 * @coversNothing
 */
class NewsFetcherForNewsDataIoTest extends TestCase
{
    use DatabaseTransactions;

    public function testFetchWhenDBEmpty(): void
    {
        $response = $this->getFakeResponse();
        $chunkFetcher = $this->mock(ChunkFetcherForNewsDataIo::class);
        $chunkFetcher->shouldReceive('chunkFetch')
            ->andReturn($response)
        ;
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $responses = $newsFetcher->fetch();
        $numberOfResponses = count($responses['results']);
        assertEquals($numberOfResponses, 3);
    }

    public function testFetchWhenDBNotEmpty(): void
    {
        $response = $this->getFakeResponse();
        $fiveHoursAgo = Carbon::now()->subHours(5)->tz('UTC')->format('Y-m-d H:i:s');
        ArticleFactory::new()->create(['published_at' => $fiveHoursAgo]);
        $chunkFetcher = $this->mock(ChunkFetcherForNewsDataIo::class);
        $chunkFetcher->shouldReceive('chunkFetch')
            ->andReturn($response)
        ;
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $responses = $newsFetcher->fetch();
        $numberOfResponses = count($responses['results']);
        assertEquals($numberOfResponses, 2);
    }

    private function getFakeResponse(): array
    {
        $now = Carbon::now()->tz('UTC')->format('Y-m-d H:i:s');
        $fiveHoursAgo = Carbon::now()->subHours(5)->tz('UTC')->format('Y-m-d H:i:s');
        $oneDayAgo = Carbon::now()->subDays(1)->tz('UTC')->format('Y-m-d H:i:s');
        $twoDaysAgo = Carbon::now()->subDays(2)->tz('UTC')->format('Y-m-d H:i:s');

        return [
            'results' => [
                [
                    'pubDate' => $now,
                    'title' => 'Mocked Article 1',
                    'content' => 'Lorem ipsum dolor sit amet',
                ],
                [
                    'pubDate' => $fiveHoursAgo,
                    'title' => 'Mocked Article 2',
                    'content' => 'Lorem ipsum dolor sit amet',
                ],
                [
                    'pubDate' => $oneDayAgo,
                    'title' => 'Mocked Article 3',
                    'content' => 'Lorem ipsum dolor sit amet',
                ],
                [
                    'pubDate' => $twoDaysAgo,
                    'title' => 'Mocked Article 4',
                    'content' => 'Lorem ipsum dolor sit amet',
                ],
            ],
        ];
    }
}
