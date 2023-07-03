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
    
    public function fetch(string $initialDateTime): array
    {
        $page = '';
        $creditsUsed = 0;
        $articles = collect();

        while (true) {
            $fetchedNews = $this->chunkFetcherForNewsDataIo->fetchChunk(page: $page);
            $fetchedArticles = collect($fetchedNews['results']);
            $ogCount = $fetchedArticles->count();
            ++$creditsUsed;

            $filteredArticles = $fetchedArticles->reject(function ($fetchedArticle) use ($initialDateTime) {
                return $fetchedArticle['pubDate'] < $initialDateTime;
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
