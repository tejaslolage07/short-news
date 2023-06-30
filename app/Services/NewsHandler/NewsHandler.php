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
        $responses = $this->newsFetcherForNewsDataIo->fetch();
        foreach ($responses as $response) {
            $parsedNewsArticles = $this->newsParserForNewsDataIo->getParsedData($response);
            $this->storeParsedNewsArticles($parsedNewsArticles);
        }
    }

    private function storeParsedNewsArticles(array $parsedNewsArticles)
    {
        foreach ($parsedNewsArticles as $parsedNewsArticle) {
            if ($parsedNewsArticle['content']) {
                $this->storeParsedNewsArticle($parsedNewsArticle);
            }
        }
    }

    private function storeParsedNewsArticle(array $parsedNewsArticle): void
    {
        $newsWebsiteId = $this->getNewsWebsiteId($parsedNewsArticle['news_website']);
        $savedArticle = $this->storeArticle($parsedNewsArticle, $newsWebsiteId);
        $this->dispatchToSummarizer($savedArticle, $parsedNewsArticle['content']);
    }

    private function getNewsWebsiteId(?string $newsWebsiteName): ?int
    {
        if (!$newsWebsiteName) {
            return null;
        }
        $newsWebsite = NewsWebsite::firstOrCreate(['website' => $newsWebsiteName]);

        return $newsWebsite->id;
    }

    private function storeArticle(array $parsedNewsArticle, ?int $newsWebsiteId): Article
    {
        $article = new Article();
        $article->headline = $parsedNewsArticle['headline'];
        $article->article_url = $parsedNewsArticle['article_url'];
        $article->author = $parsedNewsArticle['author'];
        $article->image_url = $parsedNewsArticle['image_url'];
        $article->published_at = $parsedNewsArticle['published_at'];
        $article->fetched_at = $parsedNewsArticle['fetched_at'];
        $article->news_website_id = $newsWebsiteId;
        $article->article_s3_filename = null;
        $article->short_news = null;
        $article->save();

        return $article;
    }

    private function dispatchToSummarizer(Article $savedArticle, string $parsedNewsArticleContent): void
    {
        SummarizeArticle::dispatch($savedArticle, $parsedNewsArticleContent);
    }
}
