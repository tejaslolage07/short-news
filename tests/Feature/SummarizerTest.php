<?php

namespace Tests\Feature;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Services\Sockets\Summarizer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class SummarizerTest extends TestCase
{
    use DatabaseTransactions;

    public function testIfSummaryIsGeneratedAndDBUpdated(): void
    {
        $this->seed();
        $articlesCount = Article::count();

        $articleBody = 'Dummy news article, pass this to summarizer and check if summary is generated and DB updated';
        $article = Article::factory()->count(1)->create()->first();
        $this->assertModelExists($article);
        $this->assertNotNull($article->id);

        $a = new SummarizeArticle($article, $articleBody, '', 1);
        $a->handle(new ArticleController(), new Summarizer());
        $this->assertNotEmpty($article->short_news);

        $this->assertDatabaseCount('articles', $articlesCount + 1);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'short_news' => $article->short_news,
        ]);
    }
}
