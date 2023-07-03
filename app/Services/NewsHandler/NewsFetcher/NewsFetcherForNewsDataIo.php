<?php

namespace App\Services\NewsHandler\NewsFetcher;

use App\Models\Article;
use Carbon\Carbon;

class NewsFetcherForNewsDataIo
{
    private ChunkFetcherForNewsDataIo $chunkFetcherForNewsDataIo;

    public function __construct(ChunkFetcherForNewsDataIo $chunkFetcherForNewsDataIo)
    {
        $this->chunkFetcherForNewsDataIo = $chunkFetcherForNewsDataIo;
    }

    public function fetch(): array
    {
        try {
            $fetchUntilDateTime = $this->getFetchUntilDateTime();
            return $this->getResponses($this->chunkFetcherForNewsDataIo, $fetchUntilDateTime);
        } catch (\Exception $e) {
            report('An error occurred: '.$e);
        }
    }
    
    public function fetchWhenDBEmpty(int $initialLimitDays): array
    {
        try {
            $fetchUntilDateTime = $this->getFetchUntilDateTimeWhenDBEmpty($initialLimitDays);
            return $this->getResponses($this->chunkFetcherForNewsDataIo, $fetchUntilDateTime);
        } catch (\Exception $e) {
            report('An error occurred: '.$e);
        }
    }

    private function getFetchUntilDateTimeWhenDBEmpty(int $initialLimitDays): string
    {
        return now()->subDays($initialLimitDays)->tz('UTC')->format('Y-m-d H:i:s');
    }

    private function getResponses(ChunkFetcherForNewsDataIo $chunkFetcher, string $fetchUntilDateTime): array
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
        return Article::orderBy('published_at', 'desc')->first()->published_at;
    }
}
