<?php

// namespace Tests\Feature;

// use App\Jobs\SummarizeArticle;
// use App\Models\Article;
// use App\Services\Sockets\Summarizer;
// use Illuminate\Foundation\Testing\DatabaseTransactions;
// use Tests\TestCase;

// /**
//  * @internal
//  *
//  * @coversNothing
//  */
// class SummarizerTest extends TestCase
// {
//     use DatabaseTransactions;

//     public function testIfSummaryIsGeneratedAndDBUpdated(): void
//     {
//         $article = Article::factory()->create();
//         $articleBody = 'Dummy news article, pass this to summarizer and check if summary is generated and DB updated';

//         $a = new SummarizeArticle($article, $articleBody, '', 1);
//         $a->handle(new Summarizer());
//         $this->assertNotNull($article->short_news);

//         $this->assertDatabaseHas('articles', [
//             'id' => $article->id,
//             'short_news' => $article->short_news,
//         ]);
//     }
// }
