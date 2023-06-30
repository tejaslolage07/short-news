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
        $articles = [];

        while (true) {
            $fetchedNews = $chunkFetcher->chunkFetch('', '', $page);
            $fetchedArticles = $fetchedNews['results'];
            ++$creditsUsed;

            foreach ($fetchedArticles as $fetchedArticle) {
                $articlePublishedAt = $fetchedArticle['pubDate'];
                if (!$this->isArticlePublishedLaterThanFetchUntilDateTime($fetchUntilDateTime, $articlePublishedAt)) {
                    break 2;
                }
                $articles[] = $fetchedArticle;
            }
            $page = $fetchedNews['nextPage'];
        }
        info(Carbon::now()->tz('Asia/Tokyo')->format('Y-m-d H:i:s')."\tTotal credits used in this session: ".$creditsUsed."\n");

        return ['results' => $articles];
    }

    private function getFetchUntilDateTime(): string
    {
        $latestArticle = Article::orderBy('published_at', 'desc')->first();

        return $latestArticle->published_at ?? $this->getInitialLimitDaysDateTime();
    }

    private function getInitialLimitDaysDateTime(): string
    {
        return Carbon::now()->subDays(self::INITIAL_LIMIT_DAYS)->tz('UTC')->format('Y-m-d H:i:s');
    }

    private function isArticlePublishedLaterThanFetchUntilDateTime(
        string $fetchUntilDateTime,
        string $articlePublishedAt
    ): bool {
        return $articlePublishedAt >= $fetchUntilDateTime;  // >= is used so that if multiple articles have the same published_at value, all of them are fetched.
        // Article url is unique key in database, so no duplicate articles are saved.
    }
}
