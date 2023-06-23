<?php

namespace App\Services\NewsHandler\NewsParser;

use App\Services\NewsHandler\NewsParser\Contracts\NewsParser;
use Carbon\Carbon;

class NewsParserForBing extends NewsParser
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
        // The Bing API doesn't send author, country, language (already specified in the request)
        $formattedDate = $this->checkIfExistsAndFormatDate($article['datePublished']);
        $imageURL = $this->getImageUrlFromData($article);
        $newsWebsiteName = $this->getNewsWebsiteName($article);
        $currentTime = $this->getCurrentDateTime();
        $keywords = $this->getKeywords($article);
        $category = $this->getCategory($article);

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
        $keywords = [];
        if (!isset($article['about'])) {
            return null;
        }
        foreach ($article['about'] as $keyword) {
            $keywords[] = $keyword['name'];
        }

        return json_encode($keywords);
    }

    private function getCurrentDateTime(): string
    {
        return date('Y-m-d H-i-s');
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

    private function checkIfExistsAndFormatDate(?string $date): ?string
    {
        return $date ? (new Carbon($date))->addHours(9)->format('Y-m-d H:i:s') : null;
    }
}
