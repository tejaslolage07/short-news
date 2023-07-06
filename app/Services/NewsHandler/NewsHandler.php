<?php

namespace App\Services\NewsHandler;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Models\NewsWebsite;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
use App\Services\S3StorageService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NewsHandler
{
    private NewsFetcherForNewsDataIo $newsFetcherForNewsDataIo;
    private NewsParserForNewsDataIo $newsParserForNewsDataIo;
    private S3StorageService $s3StorageService;

    public function __construct(
        NewsFetcherForNewsDataIo $newsFetcherForNewsDataIo,
        NewsParserForNewsDataIo $newsParserForNewsDataIo,
        S3StorageService $s3StorageService,
    ) {
        $this->newsFetcherForNewsDataIo = $newsFetcherForNewsDataIo;
        $this->newsParserForNewsDataIo = $newsParserForNewsDataIo;
        $this->s3StorageService = $s3StorageService;
    }

    public function fetchAndStoreNewsFromNewsDataIo(?string $untilDate = null): void
    {
        $parsedUntilDateTime = $untilDate ?
        Carbon::parse($untilDate) : $this->dateTimeSixHoursAgo();
        $response = $this->newsFetcherForNewsDataIo->fetch($parsedUntilDateTime);
        $s3FileNames = $this->storeArticlesToS3Bucket($response['results']);
        $parsedNewsArticles = $this->newsParserForNewsDataIo->getParsedData($response);
        $this->storeParsedNewsArticles($parsedNewsArticles, $s3FileNames, 'newsDataIoApi');
    }

    private function dateTimeSixHoursAgo(): string
    {
        return now()->subHours(6)->tz('Asia/Tokyo');
    }

    private function storeArticlesToS3Bucket(Collection $originalNewsArticles): array
    {
        $s3FileNames = [];
        foreach ($originalNewsArticles as $article) {
            try {
                $s3FileNames[] = $this->s3StorageService->writeToS3Bucket($article);
            } catch (\Exception $e) {
                $s3FileNames[] = null;
                report($e);
            }
        }

        return $s3FileNames;
    }

    private function storeParsedNewsArticles(
        array $parsedNewsArticles,
        array $s3FileNames,
        string $sourceName
    ): void {
        foreach ($parsedNewsArticles as $index => $parsedNewsArticle) {
            $isArticleUrlNotPresent = Article::where('article_url', $parsedNewsArticle['article_url'])->doesntExist();
            if ($parsedNewsArticle['content'] && $isArticleUrlNotPresent) {
                $this->storeParsedNewsArticle($parsedNewsArticle, $s3FileNames[$index], $sourceName);
            }
        }
    }

    private function storeParsedNewsArticle(
        array $parsedNewsArticle,
        ?string $s3FileName,
        string $sourceName
    ): void {
        $newsWebsiteId = $this->getNewsWebsite($parsedNewsArticle['news_website']);
        $storedArticle = $this->storeArticle($parsedNewsArticle, $s3FileName, $newsWebsiteId, $sourceName);
        $this->dispatchToSummarizer($storedArticle, $parsedNewsArticle['content']);
    }

    private function getNewsWebsite(?string $newsWebsiteName): ?NewsWebsite
    {
        if (!$newsWebsiteName) {
            return null;
        }

        return NewsWebsite::firstOrCreate(['website' => $newsWebsiteName]);
    }

    private function storeArticle(
        array $parsedNewsArticle,
        ?string $s3FileName,
        ?NewsWebsite $newsWebsite,
        string $sourceName
    ): Article {
        $article = new Article();
        $article->headline = $parsedNewsArticle['headline'];
        $article->article_url = $parsedNewsArticle['article_url'];
        $article->author = $parsedNewsArticle['author'];
        $article->image_url = $parsedNewsArticle['image_url'];
        $article->published_at = $parsedNewsArticle['published_at'];
        $article->fetched_at = $parsedNewsArticle['fetched_at'];
        $article->news_website_id = $newsWebsite?->id;
        $article->article_s3_filename = $s3FileName;
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
