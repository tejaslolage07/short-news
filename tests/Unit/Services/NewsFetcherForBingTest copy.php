<?php

namespace Tests\Unit\Services\NewsFetcher;

use App\Services\NewsFetcher\NewsFetcherForBing;
use Illuminate\Http\Client\Request;
use Exception;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsFetcherForBingTest extends TestCase
{
    public function testFetch()
    {
        Http::fake([
            'https://api.bing.microsoft.com/*' => Http::response(['data' => 'mocked data'], 200)
        ]);
        $newsFetcher = new NewsFetcherForBing();
        $response = $newsFetcher->fetch('search query', 10);

        // Http::assertSent(function (Request $request) {
        //     $url = 'https://api.bing.microsoft.com/v7.0/news/search';
        //     $headers = [
        //         'Ocp-Apim-Subscription-Key' => config('services.bing.key'),
        //         'mkt' => 'ja-JP'
        //     ];
        //     $params = [
        //         'q' => 'search query',
        //         'count' => 10,
        //         'setLang' => 'jp',
        //         'freshness' => 'Day',
        //         'safeSearch' => 'Off'
        //     ];
        //     return $request->url() === $url &&
        //         $request->headers() === $headers &&
        //         $request->data() === $params;
        // });

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
