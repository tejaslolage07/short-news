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
        $fetchedAt = now()->format('Y-m-d H:i:s');
        $response = $this->newsFetcherForNewsDataIo->fetch($parsedUntilDateTime);
        $parsedNewsArticles = $this->newsParserForNewsDataIo->getParsedData($response, $fetchedAt);
        $this->storeNewsArticlesAndUploadToS3($response['results'], $parsedNewsArticles, 'newsDataIoApi');
    }

    private function dateTimeSixHoursAgo(): string
    {
        return now()->subHours(6);
    }

    private function storeNewsArticlesAndUploadToS3(
        Collection $responseResults,
        array $parsedNewsArticles,
        string $sourceName
    ): void {
        foreach ($parsedNewsArticles as $index => $parsedNewsArticle) {
            $content = $parsedNewsArticle['content'];
            $url = $parsedNewsArticle['article_url'];

            if (null === $content) {
                continue;
            }
            if (Article::where('article_url', $url)->exists()) {
                continue;
            }

            $s3FileName = $this->uploadNewsArticleToS3($responseResults[$index]);
            $article = $this->storeParsedNewsArticle($parsedNewsArticle, $s3FileName, $sourceName);
            SummarizeArticle::dispatch($article, $content);
        }
    }

    private function uploadNewsArticleToS3(array $newsArticle): string
    {
        try {
            return $this->s3StorageService->writeToS3Bucket($newsArticle);
        } catch (\Exception $e) {
            return null;
            report($e);
        }
    }

    private function storeParsedNewsArticle(
        array $parsedNewsArticle,
        ?string $s3FileName,
        string $sourceName
    ): Article {
        $newsWebsiteId = $this->getNewsWebsite($parsedNewsArticle['news_website']);

        return $this->storeParsedArticle($parsedNewsArticle, $s3FileName, $newsWebsiteId, $sourceName);
    }

    private function getNewsWebsite(?string $newsWebsiteName): ?NewsWebsite
    {
        return !$newsWebsiteName
            ? null
            : NewsWebsite::firstOrCreate(['website' => $newsWebsiteName]);
    }

    private function storeParsedArticle(
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
}
