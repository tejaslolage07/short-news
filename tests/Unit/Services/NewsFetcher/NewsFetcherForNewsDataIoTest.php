<?php

use App\Models\Article;
use App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use Carbon\Carbon;
use Database\Factories\ArticleFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

/**
 * @internal
 *
 * @coversNothing
 * 
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class NewsFetcherForNewsDataIoTest extends TestCase
{
    use DatabaseTransactions;
    private const INITIAL_LIMIT_DAYS = 1;   // Update in class to be tested as well.

    public function testFetchWhenDBEmpty(): void
    {
        $response = $this->getFakeResponse();
        $chunkFetcher = \Mockery::mock('overload:App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo');
        $chunkFetcher->shouldReceive('fetchChunk')
            ->andReturn($response)
        ;
        $chunkFetcher = new ChunkFetcherForNewsDataIo();
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $responses = $newsFetcher->fetch();
        $numberOfResponses = count($responses['results']);
        assertEquals($numberOfResponses, 3);
    }
    
    public function testFetchWhenDBNotEmpty(): void
    {
        $response = $this->getFakeResponse();
        $fiveHoursAgo = now()->subDays(self::INITIAL_LIMIT_DAYS-1)->subHours(5)->tz('UTC')->format('Y-m-d H:i:s');
        ArticleFactory::new()->create(['published_at' => $fiveHoursAgo]);
        $chunkFetcher = \Mockery::mock('overload:App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo');
        $chunkFetcher->shouldReceive('fetchChunk')
            ->andReturn($response)
        ;
        $chunkFetcher = new ChunkFetcherForNewsDataIo();
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $responses = $newsFetcher->fetch();
        $numberOfResponses = count($responses['results']);
        assertEquals($numberOfResponses, 2);
    }

    private function getFakeResponse(): array
    {
        $now = now()->subDays(self::INITIAL_LIMIT_DAYS-1)->tz('UTC')->format('Y-m-d H:i:s');
        $fiveHoursAgo = now()->subDays(self::INITIAL_LIMIT_DAYS-1)->subHours(5)->tz('UTC')->format('Y-m-d H:i:s');
        $oneDayAgo = now()->subDays(self::INITIAL_LIMIT_DAYS)->tz('UTC')->format('Y-m-d H:i:s');
        $twoDaysAgo = now()->subDays(self::INITIAL_LIMIT_DAYS+1)->tz('UTC')->format('Y-m-d H:i:s');

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
