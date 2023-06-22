<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\NewsFetcherService;
use App\Models\Article;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NewsFetcherServiceTest extends TestCase
{
    use DatabaseTransactions;
    public function testFetchAndStoreNewsFromBing()
    {
        $service = new NewsFetcherService();
        $initialCount = Article::count();
        $service->fetchAndStoreNewsFromBing();
        $finalCount = Article::count();
        $this->assertGreaterThan($initialCount, $finalCount);
    }


    
    public function testFetchAndStoreNewsFromNewsDataIo()
    {
        $service = new NewsFetcherService();
        $initialCount = Article::count();
        $service->fetchAndStoreNewsFromNewsDataIo();
        $finalCount = Article::count();
        $this->assertGreaterThan($initialCount, $finalCount);
    }
}
