<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\NewsHandler\NewsHandler;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

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
        $newsParser = new NewsParserForNewsDataIo;
        $chunkFetcher = new ChunkFetcherForNewsDataIo;
        $newsFetcher = new NewsFetcherForNewsDataIo($chunkFetcher);
        Queue::fake();
        $initialQueueSize = Queue::size();
        $initialDatabaseCount = Article::count();
        $service = new NewsHandler($newsFetcher, $newsParser, $chunkFetcher);
        $service->fetchAndStoreNewsFromNewsDataIo();
        $finalQueueSize = Queue::size();
        $finalDatabaseCount = Article::count();
        $this->assertGreaterThan($initialQueueSize, $finalQueueSize);
        $this->assertGreaterThan($initialDatabaseCount, $finalDatabaseCount);
    }
}
