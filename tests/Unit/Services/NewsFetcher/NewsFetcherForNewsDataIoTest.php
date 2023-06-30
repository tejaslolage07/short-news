<?php
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Article;

class NewsFetcherForNewsDataIoTest extends TestCase
{
    public function testFetchReturnsArray()
    {
        $newsFetcher = new NewsFetcherForNewsDataIo(new ChunkFetcherForNewsDataIo);
        $responses = $newsFetcher->fetch();
        $this->assertIsArray($responses);
    }

    public function testGetLatestUrlFromDBReturnsString()
    {
        $newsFetcher = new NewsFetcherForNewsDataIo(new ChunkFetcherForNewsDataIo);
        $latestUrl = $this->invokeMethod($newsFetcher, 'getLatestUrlFromDB');
        $this->assertIsString($latestUrl);
    }

    public function testGetDaysCap()
    {
        $newsFetcher = new NewsFetcherForNewsDataIo(new ChunkFetcherForNewsDataIo);
        $daysCap = $this->invokeMethod($newsFetcher, 'getDaysCap', [1]);
        $this->assertIsString($daysCap);
        $this->assertEquals(Carbon::now()->subDays(1)->tz('UTC')->format('Y-m-d H:i:s'), $daysCap);
    }

    public function testIsNewArticle()
    {
        $newsFetcher = new NewsFetcherForNewsDataIo(new ChunkFetcherForNewsDataIo);
        $isNewArticle = $this->invokeMethod($newsFetcher, 'isNewArticle', ['existing_url', '2023-06-30 12:00:00', 'article_url', '2023-06-30 13:00:00']);
        $this->assertIsBool($isNewArticle);
        $this->assertEquals(true, $isNewArticle);
    }

    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}

