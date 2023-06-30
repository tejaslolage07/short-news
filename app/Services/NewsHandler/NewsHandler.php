<?php

namespace App\Services\NewsHandler;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use App\Models\NewsWebsite;
use App\Services\NewsHandler\NewsFetcher\ChunkFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;

class NewsHandler
{
    // public function fetchAndStoreNewsFromNewsDataIo(NewsFetcherForNewsDataIo $newsFetcher, ChunkFetcherForNewsDataIo $chunkFetcher, NewsParserForNewsDataIo $parser): void
    public function fetchAndStoreNewsFromNewsDataIo(NewsFetcherForNewsDataIo $newsFetcher, NewsParserForNewsDataIo $newsParser, ChunkFetcherForNewsDataIo $chunkFetcher): void
    {
        $responses = $chunkFetcher->chunkFetchNewsFromNewsDataIo($newsFetcher);
        foreach ($responses as $response) {
            $parsedNewsArticles = $newsParser->getParsedData($response);
            $this->storeParsedNewsArticles($parsedNewsArticles);
        }
    }

    private function storeParsedNewsArticles(array $parsedNewsArticles)
    {
        foreach ($parsedNewsArticles as $parsedNewsArticle) {
            $this->storeParsedNewsArticle($parsedNewsArticle);
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
        $article->article_s3_filename = '';
        $article->short_news = '';
        $article->news_website_id = $newsWebsiteId;
        $article->published_at = $parsedNewsArticle['published_at'];
        $article->fetched_at = $parsedNewsArticle['fetched_at'];
        $article->save();

        return $article;
    }

    private function dispatchToSummarizer(Article $savedArticle, string $parsedNewsArticleContent): void
    {
        SummarizeArticle::dispatch($savedArticle, $parsedNewsArticleContent);
    }
}
