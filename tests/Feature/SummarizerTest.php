<?php

namespace Tests\Feature;

use App\Http\Controllers\ArticleController;
use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Services\Sockets\Summarizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SummarizerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_summary_is_generated_and_DB_updated(): void
    {
        // seed the DB with dummy data
        $this->seed();

        // check if DB is seeded, 5 news websites and 10 articles
        $this->assertDatabaseCount('articles', 10);
        $this->assertDatabaseCount('news_websites', 5);

        #prepare the article
        $articleBody = "Dummy news article, pass this to summarizer and check if summary is generated and DB updated";
        $article = Article::factory(1)->create()->first();
        $this->assertModelExists($article);
        $this->assertInstanceOf(Article::class, $article);
        $this->assertNotNull($article->id);

        #handle the job
        $a = new SummarizeArticle($article, $articleBody, '', 1);
        $a->handle(new ArticleController(), new Summarizer());
        $this->assertNotEmpty($article->short_news);

        #check if Job updated the DB
        $this->assertDatabaseCount('articles', 11);
        $this->assertDatabaseHas('articles', [
            'short_news' => $article->short_news
        ]);
    }
}
