<?php

namespace App\Services\NewsHandler;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Models\NewsWebsite;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;

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

    public function fetchAndStoreNewsFromNewsDataIo(): void
    {
        $response = $this->newsFetcherForNewsDataIo->fetch();
        $parsedNewsArticles = $this->newsParserForNewsDataIo->getParsedData($response);
        $this->storeParsedNewsArticles($parsedNewsArticles);
    }

    private function storeParsedNewsArticles(array $parsedNewsArticles): void
    {
        foreach ($parsedNewsArticles as $parsedNewsArticle) {
            if ($parsedNewsArticle['content']) {
                $this->storeParsedNewsArticle($parsedNewsArticle);
            }
        }
    }

    private function storeParsedNewsArticle(array $parsedNewsArticle): void
    {
        $newsWebsiteId = $this->getNewsWebsite($parsedNewsArticle['news_website']);
        $storedArticle = $this->storeArticle($parsedNewsArticle, $newsWebsiteId);
        $this->dispatchToSummarizer($storedArticle, $parsedNewsArticle['content']);
    }

    private function getNewsWebsite(?string $newsWebsiteName): ?NewsWebsite
    {
        if (!$newsWebsiteName) {
            return null;
        }
        return NewsWebsite::firstOrCreate(['website' => $newsWebsiteName]);
    }

    private function storeArticle(array $parsedNewsArticle, ?NewsWebsite $newsWebsite): Article
    {
        $article = new Article();
        $article->headline = $parsedNewsArticle['headline'];
        $article->article_url = $parsedNewsArticle['article_url'];
        $article->author = $parsedNewsArticle['author'];
        $article->image_url = $parsedNewsArticle['image_url'];
        $article->published_at = $parsedNewsArticle['published_at'];
        $article->fetched_at = $parsedNewsArticle['fetched_at'];
        $article->news_website_id = $newsWebsite->id ?? null;
        $article->article_s3_filename = null;
        $article->short_news = null;
        $article->save();

        return $article;
    }

    private function dispatchToSummarizer(Article $storedArticle, string $parsedNewsArticleContent): void
    {
        SummarizeArticle::dispatch($storedArticle, $parsedNewsArticleContent);
    }
}
