<?php

namespace App\Services\NewsHandler\NewsParser;

use App\Services\NewsHandler\NewsParser\Contracts\NewsParser;
use Carbon\Carbon;

class NewsParserForNewsDataIo implements NewsParser
{
    public function getParsedData(array $response): array
    {
        $articles = $response['results'];
        $parsedData = [];
        foreach ($articles as $article) {
            $parsedArticle = $this->parseArticle($article);
            $parsedData[] = $parsedArticle;
        }

        return $parsedData;
    }

    public function getNextPage(string $response): string
    {
        $data = json_decode($response, true);

        return $data['nextPage'];
    }

    private function parseArticle(array $article): array
    {
        $formattedDate = $this->checkIfExistsAndFormatDate($article['pubDate']);
        $currentTime = $this->getCurrentDateTime();
        $keywords = $this->getKeywords($article);
        $categories = $this->getCategories($article);
        $countries = $this->getCountries($article);
        $author = $this->getAuthor($article);

        return [
            'headline' => $article['title'],
            'article_url' => $article['link'],
            'author' => $author,
            'content' => $article['content'],
            'image_url' => $article['image_url'],
            'news_website' => $article['source_id'],
            'published_at' => $formattedDate,
            'fetched_at' => $currentTime,
            'country' => $countries,
            'language' => $article['language'],
            'category' => $categories,
            'keywords' => $keywords,
        ];
    }

    private function getCountries(array $article): ?string
    {
        if (!isset($article['country'])) {
            return null;
        }

        return json_encode($article['country']);
    }
    
    private function getCategories(array $article): ?string
    {
        if (!isset($article['category'])) {
            return null;
        }

        return json_encode($article['category']);
    }
    
    private function getKeywords(array $article): ?string
    {
        if (!isset($article['keywords'])) {
            return null;
        }
    
        return json_encode($article['keywords']);
    }

    private function getCurrentDateTime(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function getAuthor(array $article): ?string
    {
        return $article['creator'][0] ?? null;
    }

    private function checkIfExistsAndFormatDate(?string $date): ?string
    {
        return $date ? (new Carbon($date))->addHours(9)->format('Y-m-d H:i:s') : null;
    }
}
