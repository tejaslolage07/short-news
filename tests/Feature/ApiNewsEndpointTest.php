<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ApiNewsEndpointTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexReturnsDataInValidFormat(): void
    {
        Article::factory()
            ->count(5)
            ->create(['short_news' => 'Not Empty'])
        ;

        $response = $this->get('/api/v1/news');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'short_news',
                    'headline',
                    'author',
                    'article_url',
                    'image_url',
                    'published_at',
                    'news_website' => [
                        'id',
                        'website',
                    ],
                ],
            ],
            'next_page_url',
            'prev_page_url',
            'per_page',
            'path',
        ]);
    }

    public function testIndexReturnsValidAttributes(): void
    {
        Article::factory()->
        count(100)->
        state(new Sequence(
            ['short_news' => 'something', 'news_website_id' => null],
            ['short_news' => ''],
            ['news_website_id' => null],
            ['short_news' => 'something']
        ))
            ->create()
        ;

        $response = $this->get('/api/v1/news');
        $response->assertStatus(200);
        $this->assertNull($response['prev_page_url']);
        $this->assertNull($response['next_page_url']);
        $this->assertEquals(100, $response['per_page']);

        $response = $this->get('/api/v1/news?count=10');
        $response->assertStatus(200);
        $this->assertNull($response['prev_page_url']);
        $this->assertNotNull($response['next_page_url']);
        $this->assertEquals(10, $response['per_page']);
    }

    public function testIndexReturnsValidArticles(): void
    {
        Article::factory()->
        count(100)->
        state(new Sequence(
            ['short_news' => 'something', 'news_website_id' => null],
            ['short_news' => null],
            ['news_website_id' => null],
            ['short_news' => 'something']
        ))
            ->create()
        ;

        $response = $this->get('/api/v1/news?count=400');
        $response->assertStatus(200);
        foreach ($response['data'] as $article) {
            $this->assertNotNull($article['short_news']);
            $this->assertNotNull($article['news_website']['id']);
        }
    }

    public function testIndexReturnsValidPaginatedDataOrderedByFetchedAt(): void
    {
        $time1 = NOW();
        $time2 = NOW()->addMinutes(1);
        $time3 = NOW()->addMinutes(2);
        $time4 = NOW()->addMinutes(3);
        Article::factory()->
        count(20)->
        state(new Sequence(
            ['short_news' => 'a', 'fetched_at' => $time1],
            ['short_news' => 'a', 'fetched_at' => $time2],
            ['short_news' => 'a', 'fetched_at' => $time3],
            ['short_news' => 'a', 'fetched_at' => $time4],
        ))
            ->create()
        ;
        $url = '/api/v1/news?count=5';
        $response = $this->get($url);
        $response->assertStatus(200);
        $articlesFetched = $response['data'];
        $next_page_url = $response['next_page_url'];

        for ($x = 0; $x < 3; ++$x) {
            $response = $this->fetchPage($next_page_url.'&count=5');
            $next_page_url = $response[0];
            $articlesFetched = array_merge($articlesFetched, $response[1]);
        }

        $this->assertArticlesAreProperlyOrderedBy($articlesFetched);
    }

    public function testIndexReturnsValidPaginatedDataOrderedByPublishedDateIfFetchedAtIsSame(): void
    {
        $now = NOW();
        Article::factory()->
        count(20)->
        state(new Sequence(
            ['short_news' => 'a', 'published_at' => '2020-06-20 00:00:00', 'fetched_at' => $now],
            ['short_news' => 'a', 'published_at' => '2021-06-20 00:00:00', 'fetched_at' => $now],
            ['short_news' => 'a', 'published_at' => '2022-06-20 00:00:00', 'fetched_at' => $now],
            ['short_news' => 'a', 'published_at' => '2023-06-20 00:00:00', 'fetched_at' => $now],
        ))
            ->create()
        ;
        $url = '/api/v1/news?count=5';
        $response = $this->get($url);
        $response->assertStatus(200);
        $articlesFetched = $response['data'];
        $next_page_url = $response['next_page_url'];

        for ($x = 0; $x < 3; ++$x) {
            $response = $this->fetchPage($next_page_url.'&count=5');
            $next_page_url = $response[0];
            $articlesFetched = array_merge($articlesFetched, $response[1]);
        }

        $this->assertArticlesAreProperlyOrderedBy($articlesFetched);
    }

    public function testIndexReturnsValidPaginatedDataOrderedByIdIfPublishedAtAndFetchedAtAreSame(): void
    {
        $publishedAt = NOW();
        $fetchedAt = NOW();

        Article::factory()->
        count(10)->
        create(['short_news' => 'a', 'published_at' => $publishedAt, 'fetched_at' => $fetchedAt]);
        $url = '/api/v1/news?count=5';
        $response = $this->get($url);
        $response->assertStatus(200);
        $articlesFetched = $response['data'];

        $url = $response['next_page_url'].'&count=5';
        $response = $this->get($url);
        $response->assertStatus(200);
        $articlesFetched = array_merge($articlesFetched, $response['data']);
        $this->assertArticlesAreProperlyOrderedBy($articlesFetched);
    }

    private function fetchPage(string $page_url): array
    {
        $response = $this->get($page_url);
        $response->assertStatus(200);

        return [$response['next_page_url'], $response['data']];
    }

    private function assertArticlesAreProperlyOrderedBy($articles): void
    {
        for ($x = 0; $x < count($articles) - 1; ++$x) {
            $currentArticle = $articles[$x];
            $nextArticle = $articles[$x + 1];
            $this->assertLessThanOrEqual($currentArticle['fetched_at'], $nextArticle['fetched_at']);
            if ($currentArticle['fetched_at'] === $nextArticle['fetched_at']) {
                $this->assertLessThanOrEqual($currentArticle['published_at'], $nextArticle['published_at']);
                if ($currentArticle['published_at'] === $nextArticle['published_at']) {
                    $this->assertGreaterThan($currentArticle['id'], $nextArticle['id']);
                }
            }
        }
    }
}
