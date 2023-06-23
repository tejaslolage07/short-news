<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\NewsHandler\NewsHandler;
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

    public function testFetchAndStoreNewsFromBing()
    {
        Queue::fake();
        $initialQueueSize = Queue::size();
        $initialDatabaseCount = Article::count();
        $service = new NewsHandler();
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
        $service = new NewsHandler();
        $service->fetchAndStoreNewsFromNewsDataIo();
        $finalQueueSize = Queue::size();
        $finalDatabaseCount = Article::count();
        $this->assertGreaterThan($initialQueueSize, $finalQueueSize);
        $this->assertGreaterThan($initialDatabaseCount, $finalDatabaseCount);
    }
}
