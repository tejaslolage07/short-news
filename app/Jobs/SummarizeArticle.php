<?php

namespace App\Jobs;

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

    public $tries = 3;

    public $timeout = 90;

    public function __construct(public Article $article, public string $articleBody = '', public string $prompt = '', public int $maxInputTokens = 1024)
    {
        assert(('' != $articleBody) || ('' != $prompt));
        assert($maxInputTokens > 0);
        if ('' == $prompt) {
            $this->prompt = 'Summarize the news article below that is delimited by triple quotes. Respond in Japanese and in no more than 60 words. Article: ```'.$articleBody.'```';
        }
    }

    public function backoff(): array
    {
        return [5, 10, 20];
    }

    public function handle(Summarizer $summarizer): void
    {
        try {
            $summary = $summarizer->summarizeOverSocket($this->prompt, $this->maxInputTokens);
            $this->article->short_news = $summary;
            $this->article->save();

            return;
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
}
