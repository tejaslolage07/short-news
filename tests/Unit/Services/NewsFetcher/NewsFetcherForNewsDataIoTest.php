<?php

use App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Carbon\Carbon;

use function PHPUnit\Framework\assertEquals;

/**
 * @internal
 *
 * @coversNothing
 *
 * @runTestsInSeparateProcesses
 *
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
        $responses = $newsFetcher->fetch('2020-01-01 00:00:00');
        $numberOfResponses = count($responses['results']);
        assertEquals($numberOfResponses, 3);
    }

    private function getFakeResponse(): array
    {
        return [
            'results' => [
                [
                    'pubDate' => Carbon::parse('2021-01-01 00:00:00', 'Asia/Tokyo')->tz('UTC')->format('Y-m-d H:i:s'),
                    'title' => 'Mocked Article 1',
                    'content' => 'Lorem ipsum dolor sit amet',
                ],
                [
                    'pubDate' => Carbon::parse('2020-01-01 00:00:01', 'Asia/Tokyo')->tz('UTC')->format('Y-m-d H:i:s'),
                    'title' => 'Mocked Article 2',
                    'content' => 'Lorem ipsum dolor sit amet',
                ],
                [
                    'pubDate' => Carbon::parse('2020-01-01 00:00:00', 'Asia/Tokyo')->tz('UTC')->format('Y-m-d H:i:s'),
                    'title' => 'Mocked Article 3',
                    'content' => 'Lorem ipsum dolor sit amet',
                ],
                [
                    'pubDate' => Carbon::parse('2019-12-31 23:59:59', 'Asia/Tokyo')->tz('UTC')->format('Y-m-d H:i:s'),
                    'title' => 'Mocked Article 4',
                    'content' => 'Lorem ipsum dolor sit amet',
                ],
            ],
        ];
    }
}
