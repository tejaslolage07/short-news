<?php

namespace Tests\Unit\Services\NewsFetcher;

use App\Services\NewsFetcher\NewsFetcherForBing;
use Exception;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsFetcherForBingTest extends TestCase
{
    use RefreshDatabase;
    public function testFetch()
    {
        Http::fake([
            'https://api.bing.microsoft.com/*' => Http::response(['data' => 'mocked data'], 200)
        ]);
        $newsFetcher = new NewsFetcherForBing();
        $response = $newsFetcher->fetch('search query', 10);
        // $this->assertTrue($response->successful());
        $response->assertOk();
        $this->assertEquals(['data' => 'mocked data'], $response->json());
    }

    public function testFetchReturnsValidResponse()
    {
        $newsData = [
            'articles' => [
                [
                    'title' => 'Sample News 1',
                    'link' => 'Sample news link 1',
                ],
                [
                    'title' => 'Sample News 2',
                    'link' => 'Sample news link 2',
                ],
            ]
        ];

        Http::fake([
            'https://api.bing.microsoft.com/*' => Http::response($newsData, 200)
        ]);

        $newsFetcher = new NewsFetcherForBing();
        $response = $newsFetcher->fetch('search query', 10);
        $this->assertTrue($response->successful());
        $this->assertEquals($newsData, $response->json());
        $this->assertArrayHasKey('articles', $response->json());
        $this->assertNotEmpty($response->json()['articles']);
    }


    public function testFetchThrowsExceptionOnError()
    {
        Http::fake([
            'https://api.bing.microsoft.com/*' => Http::response(['error' => 'Bing API returned an error: mocked error'], 500)
        ]);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Bing API returned an error: mocked error');
        $newsFetcher = new NewsFetcherForBing();
        $newsFetcher->fetch('search query', 10);
    }
}
