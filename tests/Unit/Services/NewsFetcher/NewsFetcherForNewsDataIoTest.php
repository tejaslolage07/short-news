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
    
    public function testFetch(): void
    {
        $response = $this->getFakeResponse();
        $chunkFetcher = \Mockery::mock('overload:App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo');
        $chunkFetcher->shouldReceive('fetchChunk')
        ->andReturn($response)
        ;
        $chunkFetcher = new ChunkFetcherForNewsDataIo();
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        $oneDayAgo = now()->subDays(1)->format('Y-m-d H:i:s');
        $responses = $newsFetcher->fetch($oneDayAgo);
        $numberOfResponses = count($responses['results']);
        assertEquals($numberOfResponses, 3);
    }

    private function getFakeResponse(): array
    {
        $now = now()->format('Y-m-d H:i:s');
        $fiveHoursAgo = now()->subHours(5)->format('Y-m-d H:i:s');
        $oneDayAgo = now()->subDays(1)->format('Y-m-d H:i:s');
        $twoDaysAgo = now()->subDays(2)->format('Y-m-d H:i:s');

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
