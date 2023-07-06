<?php

namespace App\Services\NewsHandler\NewsFetcher;

use Illuminate\Support\Facades\Http;

class ChunkFetcherForNewsDataIo
{
    private const URL = 'https://newsdata.io/api/1/news';

    public function fetchChunk(string $searchQuery = '', string $category = '', string $page = ''): array
    {
        $headers = $this->getHeaders();
        $params = $this->getParams($searchQuery, $category, $page);
        $response = Http::withHeaders($headers)
            ->get(self::URL, $params)
            ->throw()
        ;

        return $response->json();
    }

    private function getHeaders(): array
    {
        return [
            'X-ACCESS-KEY' => config('services.newsdataio.key'),
        ];
    }

    private function getParams(string $searchQuery, string $category, string $page): array
    {
        $params = [];
        if ('' !== $searchQuery) {
            $params['q'] = $searchQuery;
        }
        if ('' !== $category) {
            $params['category'] = $category;
        }
        if ('' !== $page) {
            $params['page'] = $page;
        }
        $params['language'] = 'jp';
        $params['country'] = 'jp';

        return $params;
    }
}
