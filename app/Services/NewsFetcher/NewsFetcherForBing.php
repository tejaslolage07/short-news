<?php

namespace App\Services\NewsFetcher;

use Illuminate\Support\Facades\Http;
use Exception;

// IMP! Bing is rejected due to the reason that it only sends short description of the whole news and not the full article.

// const url = 'https://api.bing.microsoft.com/v7.0/news/search';
class NewsFetcherForBing
{
    const url = 'https://api.bing.microsoft.com/v7.0/news/search';
    public function fetch(string $searchQuery = '', int $articleCount = 1000)
    {
        $headers = $this->getHeaders();
        $params = $this->getParams($searchQuery, $articleCount);
        $response = Http::withHeaders($headers)->get(self::url, $params);
        if (!$response->successful()) {
            throw new Exception('Bing API returned an error: ' . $response->body());
        }
        return $response;
    }

    private function getHeaders(): array
    {
        return [
            'Ocp-Apim-Subscription-Key' => config('services.bing.key'),
            "mkt" => "ja-JP"
        ];
    }

    private function getParams(string $searchQuery, int $count): array
    {
        return array(
            "q" => $searchQuery,
            "count" => $count,
            "setLang" => 'jp',
            "freshness" => 'Day',
            "safeSearch" => 'Off'
        );
    }
}
