<?php

namespace App\Services\NewsHandler\NewsParser;

use App\Services\NewsHandler\NewsParser\Contracts\NewsParserInterface;
use Carbon\Carbon;

class NewsParserForNewsDataIo implements NewsParserInterface
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
        $formattedDate = null;
        if ($article['pubDate']) {
            $formattedDate = $this->formatDate($article['pubDate']);
        }
        $currentTime = now()->format('Y-m-d H:i:s');
        $author = $this->getAuthor($article);
        $countries = $this->getCountries($article);
        $language = $this->getLanguageEnumValue($article['language']);
        $categories = $this->getCategories($article);
        $keywords = $this->getKeywords($article);

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
            'language' => $language,
            'category' => $categories,
            'keywords' => $keywords,
        ];
    }

    private function formatDate(string $date): string
    {
        return Carbon::parse($date, 'UTC')->tz('Asia/Tokyo')->format('Y-m-d H:i:s');
    }

    private function getAuthor(array $article): ?string
    {
        return $article['creator'][0] ?? null;
    }

    private function getCountries(array $article): ?array
    {
        if (!isset($article['country'])) {
            return null;
        }

        return $article['country'];
    }

    private function getLanguageEnumValue(?string $language): ?string
    {
        switch ($language) {
            case 'japanese':
                return 'ja';
            case 'english':
                return 'en';
            default:
                return null;
        }
    }

    private function getCategories(array $article): ?array
    {
        if (!isset($article['category'])) {
            return null;
        }

        return $article['category'];
    }

    private function getKeywords(array $article): ?array
    {
        if (!isset($article['keywords'])) {
            return null;
        }

        return $article['keywords'];
    }
}
