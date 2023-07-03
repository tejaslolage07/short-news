<?php

namespace App\Services\NewsHandler\NewsFetcher;

use App\Services\NewsHandler\NewsFetcher\Contracts\NewsFetcher;
use Carbon\Carbon;
use App\Models\Article;

class NewsFetcherForNewsDataIo implements NewsFetcher
{
    private ChunkFetcherForNewsDataIo $chunkFetcherForNewsDataIo;

    public function __construct(ChunkFetcherForNewsDataIo $chunkFetcherForNewsDataIo)
    {
        $this->chunkFetcherForNewsDataIo = $chunkFetcherForNewsDataIo;
    }
    
    public function fetch(string $untilDateTime): array
    {
        $page = '';
        $creditsUsed = 0;
        $articles = collect();

        while (true) {
            $fetchedNews = $this->chunkFetcherForNewsDataIo->fetchChunk(page: $page);
            $fetchedArticles = collect($fetchedNews['results']);
            $ogCount = $fetchedArticles->count();
            ++$creditsUsed;

            $filteredArticles = $fetchedArticles->reject(function ($fetchedArticle) use ($untilDateTime) {
                $parsedPublishedAt = Carbon::parse($fetchedArticle['pubDate'], 'UTC');
                $parsedUntilDateTime = Carbon::parse($untilDateTime, 'Asia/Tokyo');
                return $parsedPublishedAt < $parsedUntilDateTime;
            });
            $filteredCount = $filteredArticles->count();
            $articles = $articles->merge($filteredArticles);
            if ($ogCount !== $filteredCount) {
                break;
            }
            $page = $fetchedNews['nextPage'];
        }
        info(now()->format('Y-m-d H:i:s')."\tTotal credits used in this session: ".$creditsUsed."\n");

        return ['results' => $articles];
    }
}
