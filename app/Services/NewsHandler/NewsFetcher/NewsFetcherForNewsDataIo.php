<?php

namespace App\Services\NewsHandler\NewsFetcher;

use App\Models\Article;
use Carbon\Carbon;

class NewsFetcherForNewsDataIo
{
    public function fetch(ChunkFetcherForNewsDataIo $newsFetcher): array
    {
        try {
            $responses = $this->getResponses($newsFetcher);
        } catch (\Exception $e) {
            report('An error occurred: '.$e);
        }

        return $responses;
    }

    private function getResponses(ChunkFetcherForNewsDataIo $chunkFetcher): array
    {
        $existingUrl = $this->getLatestUrlFromDB();
        $dateTimeCap = $this->getDaysCap(1);    // Give negative values for no cap: (Warning: All credits could get used in one session.)
        $page = '';
        $creditsUsed = 0;
        $responses = [];
        do {
            $fetchedNews = $chunkFetcher->chunkFetch('', '', $page);
            ++$creditsUsed;

            for ($i = 0; $i < 10; ++$i) {
                $articleUrl = $fetchedNews['results'][$i]['link'];
                $articlePublishedAt = $fetchedNews['results'][$i]['pubDate'];
                if (!$this->isNewArticle($existingUrl, $dateTimeCap, $articleUrl, $articlePublishedAt)) {
                    $slicedArray['results'] = array_slice($fetchedNews['results'], 0, $i);
                    $responses[] = $slicedArray;

                    break 2;
                }
            }
            $responses[] = $fetchedNews;
            $page = $fetchedNews['nextPage'];
        } while ($page);
        info(Carbon::now()->tz('Asia/Tokyo')->format('Y-m-d H:i:s')."\tTotal credits used in this session: ".$creditsUsed."\n");

        return $responses;
    }

    private function getLatestUrlFromDB(): string
    {
        $latestUrl = Article::orderBy('published_at', 'desc')->value('article_url');

        return $latestUrl ?: '';
    }

    private function getDaysCap(int $days): string
    {
        $existingArticleDateTime = Article::orderBy('published_at', 'desc')->value('published_at');
        $daysCap = Carbon::now()->subDays($days)->tz('UTC')->format('Y-m-d H:i:s');
        if ($existingArticleDateTime && Carbon::parse($daysCap) < Carbon::parse($existingArticleDateTime)) {
            return $existingArticleDateTime;
        }

        return $daysCap;
    }

    private function isNewArticle(string $existingUrl, string $dateTimeCap, string $articleUrl, string $articlePublishedAt): bool
    {
        return $existingUrl !== $articleUrl && $articlePublishedAt > $dateTimeCap;
    }
}
