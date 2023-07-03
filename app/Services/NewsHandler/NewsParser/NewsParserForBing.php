<?php

namespace App\Services\NewsHandler\NewsParser;

use App\Services\NewsHandler\NewsParser\Contracts\NewsParserInterface;
use Carbon\Carbon;

class NewsParserForBing implements NewsParserInterface
{
    public function getParsedData(array $response): array
    {
        $articles = $response['value'];
        $result = [];
        foreach ($articles as $article) {
            $parsedArticle = $this->parseArticle($article);
            $result[] = $parsedArticle;
        }

        return $result;
    }

    private function parseArticle(array $article): array
    {
        // The Bing API doesn't send author data
        $formattedDate = $article['datePublished'] ? $this->formatDate($article['datePublished']) : null;

        $imageURL = $this->getImageUrlFromData($article);
        $newsWebsiteName = $this->getNewsWebsiteName($article);
        $keywords = $this->getKeywords($article);
        $category = $this->getCategory($article);
        $currentTime = now()->format('Y-m-d H:i:s');

        return [
            'headline' => $article['name'],
            'article_url' => $article['url'],
            'author' => null,
            'content' => $article['description'],
            'image_url' => $imageURL,
            'news_website' => $newsWebsiteName,
            'published_at' => $formattedDate,
            'fetched_at' => $currentTime,
            'country' => null,
            'language' => null,
            'category' => $category,
            'keywords' => $keywords,
        ];
    }

    private function getKeywords(array $article): ?string
    {
        if (!isset($article['about'][0]['name'])) {
            return null;
        }

        return json_encode($article['about'][0]['name']);
    }

    private function getCategory(array $article): ?string
    {
        return $article['category'] ?? null;
    }

    private function getNewsWebsiteName(array $article): ?string
    {
        return $article['provider'][0]['name'] ?? null;
    }

    private function getImageUrlFromData(array $article): ?string
    {
        return $article['image']['thumbnail']['contentUrl'] ?? null;
    }

    private function formatDate(string $date): string
    {
        return Carbon::parse($date)->tz('Asia/Tokyo')->format('Y-m-d H:i:s');
    }
}
