<?php

namespace Tests\Unit\Services\NewsFetcher;

use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class NewsFetcherForNewsDataIoTest extends TestCase
{
    public function testFetch(): void
    {
        Http::fake([
            'https://newsdata.io/*' => Http::response(['data' => 'mocked data'], 200),
        ]);
        $newsFetcher = new NewsFetcherForNewsDataIo();
        $response = $newsFetcher->fetch();
        $this->testRequest();
        $this->assertEquals(['data' => 'mocked data'], $response);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFetchReturnsValidResponse(array $newsData): void
    {
        Http::fake([
            'https://newsdata.io/*' => Http::response($newsData, 200),
        ]);

        $newsFetcher = new NewsFetcherForNewsDataIo();
        $response = $newsFetcher->fetch();
        $this->assertEquals($newsData, $response);
        $this->assertArrayHasKey('articles', $response);
        $this->assertNotEmpty($response['articles']);
    }

    public function testFetchThrowsExceptionOnError(): void
    {
        Http::fake([
            'https://newsdata.io/*' => Http::response(['error' => 'NewsDataIO API returned an error: mocked error'], 500),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('NewsDataIO API returned an error: mocked error');

        $newsFetcher = new NewsFetcherForNewsDataIo();
        $newsFetcher->fetch();
    }

    private function testRequest(): void
    {
        Http::assertSent(function (Request $request) {
            return $request->hasHeader('X-ACCESS-KEY', config('services.newsdataio.key'))
                && 'https://newsdata.io/api/1/news?language=jp&country=jp' == $request->url()
                && 'jp' == $request['language']
                && 'jp' == $request['country'];
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
