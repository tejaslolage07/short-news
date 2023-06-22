<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\NewsFetcherService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class NewsFetcherServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function testFetchAndStoreNewsFromBing()
    {
        Queue::fake();
        $initialQueueSize = Queue::size();
        $initialDatabaseCount = Article::count();
        $service = new NewsFetcherService();
        $service->fetchAndStoreNewsFromBing();
        $finalQueueSize = Queue::size();
        $finalDatabaseCount = Article::count();
        $this->assertGreaterThan($initialQueueSize, $finalQueueSize);
        $this->assertGreaterThan($initialDatabaseCount, $finalDatabaseCount);
    }

    public function testFetchAndStoreNewsFromNewsDataIo()
    {
        Queue::fake();
        $initialQueueSize = Queue::size();
        $initialDatabaseCount = Article::count();
        $service = new NewsFetcherService();
        $service->fetchAndStoreNewsFromNewsDataIo();
        $finalQueueSize = Queue::size();
        $finalDatabaseCount = Article::count();
        $this->assertGreaterThan($initialQueueSize, $finalQueueSize);
        $this->assertGreaterThan($initialDatabaseCount, $finalDatabaseCount);
    }
}
