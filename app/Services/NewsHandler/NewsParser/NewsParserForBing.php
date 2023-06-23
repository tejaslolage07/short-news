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
        // The Bing API doesn't send author, country, language
        $formattedDate = $this->checkIfExistsAndFormatDate($article['datePublished']);
        $imageURL = $this->getImageUrlFromData($article);
        $newsWebsiteName = $this->getNewsWebsiteName($article);
        $currentTime = date('Y-m-d H:i:s');

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
            'category' => $article['category'] ?? null,
            'keywords' => $article['about'][0]['name'] ?? null, // Need refinements (to store array of keywords)
        ];
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
