<?php

namespace App\Services\NewsHandler;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Models\NewsWebsite;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
use Carbon\Carbon;
use App\Services\S3StorageService;

class NewsHandler
{
    private NewsFetcherForNewsDataIo $newsFetcherForNewsDataIo;
    private NewsParserForNewsDataIo $newsParserForNewsDataIo;
    private S3StorageService $S3StorageService;

    public function __construct(
        NewsFetcherForNewsDataIo $newsFetcherForNewsDataIo,
        NewsParserForNewsDataIo $newsParserForNewsDataIo,
        S3StorageService $S3StorageService,
    ) {
        $this->newsFetcherForNewsDataIo = $newsFetcherForNewsDataIo;
        $this->newsParserForNewsDataIo = $newsParserForNewsDataIo;
        $this->S3StorageService = $S3StorageService;
    }

    public function fetchAndStoreNewsFromNewsDataIo(?string $untilDate = null): void
    {
        if (!$untilDate) {
            $untilDate = $this->dateTimeSixHoursAgo();
        }
        $parsedUntilDateTime = $this->getParsedUntilDateTime($untilDate);
        $response = $this->newsFetcherForNewsDataIo->fetch($parsedUntilDateTime);
        $S3FileNames = $this->storeArticlesToS3Bucket($response);
        $parsedNewsArticles = $this->newsParserForNewsDataIo->getParsedData($response);
        $this->storeParsedNewsArticles($parsedNewsArticles, $S3FileNames, 'newsDataIoApi');
    }

    private function dateTimeSixHoursAgo(): string
    {
        return now()->subHours(6)->tz('Asia/Tokyo');
    }

    private function getParsedUntilDateTime(string $untilDate): string
    {
        return Carbon::parse($untilDate);
    }

    private function storeArticlesToS3Bucket(array $response): array
    {
        $S3FileNames = [];
        foreach ($response['results'] as $article) {
            try{
                $S3FileNames[] = $this->S3StorageService->writeToS3Bucket($article);
            } catch(\Exception $e) {
                report($e);
            }
        }

        return $S3FileNames;
    }

    private function storeParsedNewsArticles(array $parsedNewsArticles, array $S3FileNames, string $sourceName): void
    {
        foreach ($parsedNewsArticles as $index => $parsedNewsArticle) {
            $articleAlreadyExists = Article::where('article_url', $parsedNewsArticle['article_url'])->exists();
            if ($parsedNewsArticle['content'] && !$articleAlreadyExists) {
                $this->storeParsedNewsArticle($parsedNewsArticle, $S3FileNames[$index], $sourceName);
            }
        }
    }

    private function storeParsedNewsArticle(array $parsedNewsArticle, string $S3FileName, string $sourceName): void
    {
        $newsWebsiteId = $this->getNewsWebsite($parsedNewsArticle['news_website']);
        $storedArticle = $this->storeArticle($parsedNewsArticle, $S3FileName, $newsWebsiteId, $sourceName);
        $this->dispatchToSummarizer($storedArticle, $parsedNewsArticle['content']);
    }

    private function getNewsWebsite(?string $newsWebsiteName): ?NewsWebsite
    {
        if (!$newsWebsiteName) {
            return null;
        }

        return NewsWebsite::firstOrCreate(['website' => $newsWebsiteName]);
    }

    private function storeArticle(array $parsedNewsArticle, string $S3FileName, ?NewsWebsite $newsWebsite, string $sourceName): Article
    {
        $article = new Article();
        $article->headline = $parsedNewsArticle['headline'];
        $article->article_url = $parsedNewsArticle['article_url'];
        $article->author = $parsedNewsArticle['author'];
        $article->image_url = $parsedNewsArticle['image_url'];
        $article->published_at = $parsedNewsArticle['published_at'];
        $article->fetched_at = $parsedNewsArticle['fetched_at'];
        $article->news_website_id = $newsWebsite?->id;
        $article->article_s3_filename = $S3FileName;
        $article->short_news = null;
        $article->country = $parsedNewsArticle['country'];
        $article->language = $parsedNewsArticle['language'];
        $article->category = $parsedNewsArticle['category'];
        $article->keywords = $parsedNewsArticle['keywords'];
        $article->source = $sourceName;
        $article->save();

        return $article;
    }

    private function dispatchToSummarizer(Article $storedArticle, string $parsedNewsArticleContent): void
    {
        SummarizeArticle::dispatch($storedArticle, $parsedNewsArticleContent);
    }
}
