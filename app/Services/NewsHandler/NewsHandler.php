<?php

namespace App\Services\NewsHandler;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Models\NewsWebsite;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;
use Carbon\Carbon;

class NewsHandler
{
    private NewsFetcherForNewsDataIo $newsFetcherForNewsDataIo;
    private NewsParserForNewsDataIo $newsParserForNewsDataIo;

    public function __construct(
        NewsFetcherForNewsDataIo $newsFetcherForNewsDataIo,
        NewsParserForNewsDataIo $newsParserForNewsDataIo,
    ) {
        $this->newsFetcherForNewsDataIo = $newsFetcherForNewsDataIo;
        $this->newsParserForNewsDataIo = $newsParserForNewsDataIo;
    }

    public function fetchAndStoreNewsFromNewsDataIo(?string $untilDate = null): void
    {
        if (!$untilDate) {
            $untilDate = $this->getLatestPublishedAt();
        }
        $parsedUntilDateTime = $this->getParsedUntilDateTime($untilDate);
        $response = $this->newsFetcherForNewsDataIo->fetch($parsedUntilDateTime);
        $parsedNewsArticles = $this->newsParserForNewsDataIo->getParsedData($response);
        $this->storeParsedNewsArticles($parsedNewsArticles, 'newsDataIoApi');
    }

    private function getLatestPublishedAt(): string
    {
        return Article::orderBy('published_at', 'desc')->first()->published_at;
    }

    private function getParsedUntilDateTime(string $untilDate): string
    {
        return Carbon::parse($untilDate);
    }

    private function storeParsedNewsArticles(array $parsedNewsArticles, string $sourceName): void
    {
        foreach ($parsedNewsArticles as $parsedNewsArticle) {
            $articleAlreadyExists = Article::where('article_url', $parsedNewsArticle['article_url'])->exists();
            if ($parsedNewsArticle['content'] && !$articleAlreadyExists) {
                $this->storeParsedNewsArticle($parsedNewsArticle, $sourceName);
            }
        }
    }

    private function storeParsedNewsArticle(array $parsedNewsArticle, string $sourceName): void
    {
        $newsWebsiteId = $this->getNewsWebsite($parsedNewsArticle['news_website']);
        $storedArticle = $this->storeArticle($parsedNewsArticle, $newsWebsiteId, $sourceName);
        $this->dispatchToSummarizer($storedArticle, $parsedNewsArticle['content']);
    }

    private function getNewsWebsite(?string $newsWebsiteName): ?NewsWebsite
    {
        if (!$newsWebsiteName) {
            return null;
        }

        return NewsWebsite::firstOrCreate(['website' => $newsWebsiteName]);
    }

    private function storeArticle(array $parsedNewsArticle, ?NewsWebsite $newsWebsite, string $sourceName): Article
    {
        $article = new Article();
        $article->headline = $parsedNewsArticle['headline'];
        $article->article_url = $parsedNewsArticle['article_url'];
        $article->author = $parsedNewsArticle['author'];
        $article->image_url = $parsedNewsArticle['image_url'];
        $article->published_at = $parsedNewsArticle['published_at'];
        $article->fetched_at = $parsedNewsArticle['fetched_at'];
        $article->news_website_id = $newsWebsite?->id;
        $article->article_s3_filename = null;
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
