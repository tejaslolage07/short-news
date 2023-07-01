<?php

namespace App\Services\NewsHandler\NewsFetcher;

use App\Models\Article;
use Carbon\Carbon;

class NewsFetcherForNewsDataIo
{
    private const INITIAL_LIMIT_DAYS = 1;
    private ChunkFetcherForNewsDataIo $chunkFetcherForNewsDataIo;

    public function __construct(ChunkFetcherForNewsDataIo $chunkFetcherForNewsDataIo)
    {
        $this->chunkFetcherForNewsDataIo = $chunkFetcherForNewsDataIo;
    }

    public function fetch(): array
    {
        try {
            return $this->getResponses($this->chunkFetcherForNewsDataIo);
        } catch (\Exception $e) {
            report('An error occurred: '.$e);
        }
    }

    private function getResponses(ChunkFetcherForNewsDataIo $chunkFetcher): array
    {
        $fetchUntilDateTime = $this->getFetchUntilDateTime();
        $page = '';
        $creditsUsed = 0;
        $articles = collect();



        while (true) {
            $fetchedNews = $chunkFetcher->fetchChunk(page: $page);
            $fetchedArticles = collect($fetchedNews['results']);
            $ogCount = $fetchedArticles->count();
            ++$creditsUsed;

            $filteredArticles = $fetchedArticles->reject(function ($fetchedArticle) use ($fetchUntilDateTime) {
                return $fetchedArticle['pubDate'] < $fetchUntilDateTime;
            });
            $filteredCount = $filteredArticles->count();
            $articles = $articles->merge($filteredArticles);
            if ($ogCount !== $filteredCount) {
                break;
            }
            $page = $fetchedNews['nextPage'];
        }
        info(now()->tz('Asia/Tokyo')->format('Y-m-d H:i:s')."\tTotal credits used in this session: ".$creditsUsed."\n");

        return ['results' => $articles];
    }

    private function getFetchUntilDateTime(): string
    {
        $latestArticle = Article::orderBy('published_at', 'desc')->first();

        return $latestArticle->published_at ?? $this->getInitialLimitDaysDateTime();
    }

    private function getInitialLimitDaysDateTime(): string
    {
        return now()->subDays(self::INITIAL_LIMIT_DAYS)->tz('UTC')->format('Y-m-d H:i:s');
    }
}
