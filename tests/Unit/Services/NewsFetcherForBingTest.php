<?php

namespace Tests\Unit\Services\NewsFetcher;

use App\Services\NewsFetcher\NewsFetcherForBing;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class NewsFetcherForBingTest extends TestCase
{
    public function testFetch(): void
    {
        Http::fake([
            'https://api.bing.microsoft.com/*' => Http::response(['data' => 'mocked data'], 200),
        ]);
        $newsFetcher = new NewsFetcherForBing();
        $response = $newsFetcher->fetch('', 10);
        $this->testRequest();
        $this->assertEquals(['data' => 'mocked data'], $response);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFetchReturnsValidResponse(array $newsData): void
    {
        Http::fake([
            'https://api.bing.microsoft.com/*' => Http::response($newsData, 200),
        ]);

        $newsFetcher = new NewsFetcherForBing();
        $response = $newsFetcher->fetch('', 10);

        $this->assertEquals($newsData, $response);
        $this->assertArrayHasKey('articles', $response);
        $this->assertNotEmpty($response['articles']);
    }

    public function testFetchThrowsExceptionOnError(): void
    {
        Http::fake([
            'https://api.bing.microsoft.com/*' => Http::response(['error' => 'Bing API returned an error: mocked error'], 500),
        ]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bing API returned an error: mocked error');
        $newsFetcher = new NewsFetcherForBing();
        $newsFetcher->fetch('', 10);
    }

    private function testRequest(): void
    {
        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Ocp-Apim-Subscription-Key', config('services.bing.key'))
                && $request->hasHeader('mkt', 'ja-JP')
                && 'https://api.bing.microsoft.com/v7.0/news/search?q=&count=10&setLang=jp&freshness=Day&safeSearch=Off' == $request->url()
                && '' == $request['q']
                && '10' == $request['count']
                && 'jp' == $request['setLang']
                && 'Day' == $request['freshness']
                && 'Off' == $request['safeSearch'];
        });
    }

    private function dataProvider(): array
    {
        return [
            [['articles' => [
                ['title' => 'Sample News 1', 'link' => 'Sample news link 1'],
                ['title' => 'Sample News 2', 'link' => 'Sample news link 2'],
            ]]],
            [['articles' => [
                ['title' => 'Sample News 1', 'link' => 'Sample news link 1'],
                ['title' => 'Sample News 2', 'link' => 'Sample news link 2'],
            ]]],
        ];
    }
}
