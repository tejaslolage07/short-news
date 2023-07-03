<?php

namespace App\Services\NewsHandler\NewsFetcher;

use App\Services\NewsHandler\NewsFetcher\Contracts\NewsFetcher;
use Illuminate\Support\Facades\Http;

// IMP! Bing is rejected due to the reason that it only sends short description of the whole news and not the full article.
class NewsFetcherForBing
{
    private const URL = 'https://api.bing.microsoft.com/v7.0/news/search';

    public function fetch(string $searchQuery = '', int $articleCount = 1000): array
    {
        $headers = $this->getHeaders();
        $params = $this->getParams($searchQuery, $articleCount);
        $response = Http::withHeaders($headers)
            ->get(self::URL, $params)
            ->throw()
        ;

        return $response->json();
    }

    private function getHeaders(): array
    {
        return [
            'Ocp-Apim-Subscription-Key' => config('services.bing.key'),
            'mkt' => 'ja-JP',
        ];
    }

    private function getParams(string $searchQuery, int $count): array
    {
        return [
            'q' => $searchQuery,
            'count' => $count,
            'setLang' => 'jp',
            'freshness' => 'Day',
            'safeSearch' => 'Off',
        ];
    }
}
