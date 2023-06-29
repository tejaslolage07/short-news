<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ApiNewsEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsDataInValidFormat(): void
    {
        $this->seed(class: 'Database\\Seeders\\TestSeeders\\DatabaseSeederForApiTest');
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
        $this->assertCount(100, $response['data']);
    }

    public function testIndexReturnsCorrectPaginatedData(): void
    {
        $this->seed(class: 'Database\\Seeders\\TestSeeders\\DatabaseSeederForPaginationTest');

        // get the first article
        $response = $this->get('/api/v1/news?count=1');
        $response->assertStatus(200);
        $this->assertCount(1, $response['data']);
        $this->assertEquals(1, $response['per_page']);
        $next_page_url = $response['next_page_url'];
        $firstPageArticles = $response['data'];

        // get the next article
        $response = $this->get($next_page_url.'&count=1');
        $response->assertStatus(200);
        $this->assertCount(1, $response['data']);
        $this->assertEquals(1, $response['per_page']);

        // since it was seeded in such a way that the top 2 articles had the same published_at date
        // we need to make sure that the next article is not the same as the first article
        // and that the next article is not missed.
        foreach ($response['data'] as $article) {
            foreach ($firstPageArticles as $firstPageArticle) {
                $this->assertNotEquals($article['id'], $firstPageArticle['id']);
                $this->assertEquals($firstPageArticle['published_at'], $article['published_at']);
            }
        }

        // get the first page
        $response = $this->get('/api/v1/news?count=2');
        $response->assertStatus(200);
        $this->assertCount(2, $response['data']);
        $this->assertEquals(2, $response['per_page']);
        $next_page_url = $response['next_page_url'];
        $firstPageArticles = $response['data'];

        // get the next 2 articles after the last article on the first page
        $response = $this->get($next_page_url.'&count=2');
        $response->assertStatus(200);
        $next_page_url = $response['next_page_url'];
        $this->assertCount(2, $response['data']);
        $this->assertEquals(2, $response['per_page']);

        // check that the articles are not the same as the first page articles, and that the pagination works correctly.
        foreach ($response['data'] as $article) {
            foreach ($firstPageArticles as $firstPageArticle) {
                $this->assertNotEquals($article['id'], $firstPageArticle['id']);
                $this->assertLessThanOrEqual($firstPageArticle['published_at'], $article['published_at']);
            }
        }
        $firstPageArticles = $response['data'];
        $response = $this->get($next_page_url.'&count=2');
        $response->assertStatus(200);
        $this->assertCount(2, $response['data']);
        foreach ($response['data'] as $article) {
            foreach ($firstPageArticles as $firstPageArticle) {
                $this->assertNotEquals($article['id'], $firstPageArticle['id']);
                $this->assertLessThanOrEqual($firstPageArticle['published_at'], $article['published_at']);
            }
        }
    }

    public function testIndexDoesNotReturnArticlesWithEmptyShortNews(): void
    {
        $this->seed(class: 'Database\\Seeders\\TestSeeders\\DatabaseSeederForApiTest');

        $response = $this->get('/api/v1/news?count=400');
        $response->assertStatus(200);
        $this->assertCount(200, $response['data']);
        foreach ($response['data'] as $article) {
            $this->assertNotEmpty($article['short_news']);
        }
    }

    // test index doesnt return artiles that have no news_website_id
    public function testIndexDoesNotReturnArticlesWithNoNewsWebsiteId(): void
    {
        $this->seed(class: 'Database\\Seeders\\TestSeeders\\DatabaseSeederForApiTest');

        $response = $this->get('/api/v1/news?count=300');
        $response->assertStatus(200);
        $this->assertCount(200, $response['data']);
        foreach ($response['data'] as $article) {
            $this->assertNotNull($article['news_website']['id']);
        }
    }
}
