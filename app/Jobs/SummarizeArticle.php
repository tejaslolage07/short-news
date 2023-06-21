<?php

namespace App\Jobs;

use App\Http\Controllers\ArticleController;
use App\Models\Article;
use App\Services\Sockets\Summarizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SummarizeArticle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     * Default is 60 sec.
     */
    public $timeout = 90;

    /**
     * Create a new job instance.
     */
    public function __construct(public Article $article, public string $articleBody, public string $prompt = '', public int $maxInputTokens = 1024)
    {
        if ('' == $prompt) {
            $this->prompt = 'Summarize the news article below that is delimited by triple quotes. Respond in Japanese and in no more than 60 words. Article: ```'.$articleBody.'```';
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 10, 20];
    }

    /**
     * Execute the job.
     */
    public function handle(ArticleController $articleController, Summarizer $summarizer): void
    {
        // Summarize the article
        try {
            $summary = $summarizer->summarizeOverSocket($this->prompt, $this->maxInputTokens);
            $articleController->update($this->article, ['short_news' => $summary]);
            print('Summary: '.$summary."\n");
            return;
        } catch (\Exception $e) {
            // fail job
            $this->fail($e);
        }
    }
}
