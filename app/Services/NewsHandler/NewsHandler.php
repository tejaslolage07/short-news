<?php

namespace App\Services\NewsHandler;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Models\NewsWebsite;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForBing;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsParser\NewsParserForBing;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NewsHandler
{
    public function fetchAndStoreNewsFromBing(): void
    {
        $newsFetcher = new NewsFetcherForBing();
        $parser = new NewsParserForBing();

        try {
            $response = $newsFetcher->fetch('', 1000);
            $parsedNewsArticles = $parser->getParsedData($response);
            foreach ($parsedNewsArticles as $parsedNewsArticle) {
                $this->storeParsedNewsArticle($parsedNewsArticle);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function fetchAndStoreNewsFromNewsDataIo(): void
    {
        $newsFetcher = new NewsFetcherForNewsDataIo();
        $parser = new NewsParserForNewsDataIo();
        $existingUrl = $this->getLatestUrlFromDB();
        $existingArticleDateTime = $this->getCappedExistingArticleDateTime(1); // Give negative values for no cap (Warning: All credits will be used in one session.)
        $page = '';
        $creditsUsed = 0;

        try {
            do {
                $fetchedNews = $newsFetcher->fetch('', '', $page);
                $parsedNewsArticles = $parser->getParsedData($fetchedNews);
                ++$creditsUsed;

                foreach ($parsedNewsArticles as $parsedNewsArticle) {
                    $articleUrl = $parsedNewsArticle['article_url'];
                    $articlePublishedAt = $parsedNewsArticle['published_at'];
                    if ($this->isNewArticle($existingUrl, $existingArticleDateTime, $articleUrl, $articlePublishedAt)) {
                        if ($parsedNewsArticle['content']) {
                            $this->storeParsedNewsArticle($parsedNewsArticle);
                        }
                    } else {
                        break 2;
                    }
                }
                $page = $fetchedNews['next_page'];
            } while ($page);
        } catch (\Exception $e) {
            echo 'An error occurred: '.$e->getMessage();
        }
        echo Carbon::now()->addHour(9)->format('Y-m-d H:i:s')."\tTotal credits used in this session: ".$creditsUsed."\n";
    }

    private function storeArticle(array $parsedNewsArticle, ?int $newsWebsiteId): Article
    {
        $article = new Article();
        $article->headline = $parsedNewsArticle['headline'];
        $article->article_url = $parsedNewsArticle['article_url'];
        $article->author = $parsedNewsArticle['author'];
        $article->image_url = $parsedNewsArticle['image_url'];
        $article->article_s3_filename = '';
        $article->short_news = '';
        $article->news_website_id = $newsWebsiteId;
        $article->published_at = $parsedNewsArticle['published_at'];
        $article->fetched_at = $parsedNewsArticle['fetched_at'];
        $article->save();

        return $article;
    }

    private function storeParsedNewsArticle(array $parsedNewsArticle): void
    {
        $newsWebsiteId = $this->getNewsWebsiteId($parsedNewsArticle['news_website']);
        $savedArticle = $this->storeArticle($parsedNewsArticle, $newsWebsiteId);
        $this->pushToQueue($savedArticle, $parsedNewsArticle['content']);
    }

    private function getNewsWebsiteId(?string $newsWebsiteName): ?int
    {
        if (!$newsWebsiteName) {
            return null;
        }
        $newsWebsite = NewsWebsite::firstOrCreate(['website' => $newsWebsiteName]);

        return $newsWebsite->id;
    }

    private function pushToQueue(Article $savedArticle, string $parsedNewsArticleContent): void
    {
        SummarizeArticle::dispatch($savedArticle, $parsedNewsArticleContent);
    }

    private function getLatestUrlFromDB(): string
    {
        $latestUrl = DB::table('articles')->orderBy('published_at', 'desc')->value('article_url');

        return $latestUrl ?: '';
    }

    private function getCappedExistingArticleDateTime(int $daysCap): string
    {
        $existingArticleDateTime = DB::table('articles')->orderBy('published_at', 'desc')->value('published_at');
        $cappedAt = Carbon::now()->subHour(24 * $daysCap - 9)->format('Y-m-d H:i:s'); // 24-9 is to adjust for the time difference between UTC and JST. (Carbon gives UTC time, News come in JST)
        if ($existingArticleDateTime && $cappedAt < $existingArticleDateTime) {
            return $existingArticleDateTime;
        }

        return $cappedAt;
    }

    private function isNewArticle(string $existingUrl, string $existingArticleDateTime, string $articleUrl, string $articlePublishedAt): bool
    {
        return $existingUrl !== $articleUrl && $articlePublishedAt > $existingArticleDateTime;
    }
}
