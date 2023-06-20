<?php

namespace App\Services;

use App\Models\Article;
use App\Services\NewsFetcher\NewsFetcherForBing;
use App\Services\NewsFetcher\NewsFetcherForNewsDataIo;
use App\Services\NewsFetcher\ParserForBing;
use App\Services\NewsFetcher\ParserForNewsDataIo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewsFetcherService
{
    public function fetchAndStoreNewsFromBing()
    {
        $newsFetcher = new NewsFetcherForBing();
        $response = $newsFetcher->fetch('', 100);
        $parser = new ParserForBing();
        $parsedData = $parser->getParsedData($response->body());
        foreach ($parsedData as $data) {
            $this->storeBingArticle($data);
        }
    }

    public function fetchAndStoreNewsFromNewsDataIo()
    {
        $newsFetcher = new NewsFetcherForNewsDataIo();
        $parser = new ParserForNewsDataIo();
        $existingUrl = $this->getLatestUrlFromDB();
        $existingArticleDateTime = $this->getCappedExistingArticleDateTime();
        $page = '';
        $queries_used = 0;
        try {
            do {
                $fetchedNews = $newsFetcher->fetch('', '', $page);
                $parsedNewsArticles = $parser->getParsedData($fetchedNews);
                $queries_used++;

                foreach ($parsedNewsArticles as $parsedNewsArticle) {
                    $articleUrl = $parsedNewsArticle['article_url'];
                    $articlePublishedAt = $parsedNewsArticle['published_at'];

                    echo $articlePublishedAt;   // FOR DEBUG ONLY ***********************
                    echo "\n";

                    if ($existingUrl !== $articleUrl && $articlePublishedAt > $existingArticleDateTime) {
                        $this->writeToDatabase($parsedNewsArticle);
                    } else {
                        break 2;
                    }
                }
                $page = $parser->getNextPage($fetchedNews);
            } while ($page);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
            echo "Total queries used in this session: " . $queries_used;
    }

    private function getLatestUrlFromDB()
    {
        $latestUrl = DB::table('articles')->orderBy('published_at', 'desc')->value('article_url');
        return $latestUrl ?: '';
    }

    private function writeToDatabase($articleData)
    {
        $article = new Article();
        $article->headline = $articleData['headline'];
        $article->article_url = $articleData['article_url'];
        $article->author = $articleData['author'];
        $article->image_url = $articleData['image_url'];

        $article->article_s3_filename = 'testArticle.com';
        if($articleData['content']) {
            $article->short_news = $articleData['content'];
        } // Not for production. Only for testing.
        else {
            $article->short_news = 'No content available';
        }

        // $article->news_website = $articleData['news_website'];
        $article->published_at = $articleData['published_at'];
        $article->fetched_at = $articleData['fetched_at'];
        $article->save();
    }

    private function getCappedExistingArticleDateTime() // Capped at 24 hours ago
    {
        $existingArticleDateTime = DB::table('articles')->orderBy('published_at', 'desc')->value('published_at');
        $twentyFourHoursAgo = Carbon::now()->subHour(24-9)->format('Y-m-d H:i:s');  // 24-9 is to adjust for the time difference between UTC and JST. (Carbon gives UTC time, News come in JST)
        if($existingArticleDateTime && $twentyFourHoursAgo < $existingArticleDateTime) {
            return $existingArticleDateTime;
        }
        return $twentyFourHoursAgo;
    }

    private function storeBingArticle($data)
    {
        $article = new Article();
        $article->headline = $data['headline'];
        $article->article_url = $data['article_url'];
        $article->author = $data['author'];
        $article->image_url = $data['image_url'];

        $article->article_s3_filename = 'testArticle.com';
        $article->short_news = $data['content']; // Not for production. Only for testing.

        // $article->news_website = $data['news_website'];
        $article->published_at = $data['published_at'];
        $article->fetched_at = $data['fetched_at'];
        $article->save();
    }
}
