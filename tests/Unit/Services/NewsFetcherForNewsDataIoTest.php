<?php

namespace Tests\Unit\Services\NewsFetcher;

use App\Services\NewsFetcher\NewsFetcherForNewsDataIo;
use Exception;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsFetcherForNewsDataIoTest extends TestCase
{
    public function testFetch()
    {
        Http::fake([
            'https://newsdata.io/*' => Http::response(['data' => 'mocked data'], 200)
        ]);
        $newsFetcher = new NewsFetcherForNewsDataIo();
        $response = $newsFetcher->fetch();
        $this->assertTrue($response->successful());
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
            'https://newsdata.io/*' => Http::response($newsData, 200)
        ]);
        
        $newsFetcher = new NewsFetcherForNewsDataIo();
        $response = $newsFetcher->fetch();
        $this->assertTrue($response->successful());
        $this->assertEquals($newsData, $response->json());
        $this->assertArrayHasKey('articles', $response->json());
        $this->assertNotEmpty($response->json()['articles']);
    }

    public function testFetchThrowsExceptionOnError()
    {
        Http::fake([
            'https://newsdata.io/*' => Http::response(['error' => 'NewsDataIO API returned an error: mocked error'], 500)
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('NewsDataIO API returned an error: mocked error');

        $newsFetcher = new NewsFetcherForNewsDataIo();
        $newsFetcher->fetch();
    }
}
