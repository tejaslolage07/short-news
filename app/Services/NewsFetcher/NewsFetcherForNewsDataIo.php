<?php

namespace App\Services\NewsFetcher;

use Illuminate\Support\Facades\Http;
use Exception;

class NewsFetcherForNewsDataIo
{
    private const url = 'https://newsdata.io/api/1/news';
    public function fetch(string $searchQuery = '', string $category = '', string $page = ''): array
    {
        $headers = $this->getHeaders();
        $params = $this->getParams($searchQuery, $category, $page);
        $response = Http::withHeaders($headers)->get(self::url, $params);
        if (!$response->successful()) {
            throw new Exception('NewsDataIO API returned an error: ' . $response->body());
        }
        return $response->json();
    }

    private function getHeaders(): array
    {
        return [
            'X-ACCESS-KEY' => config('services.newsdataio.key')
        ];
    }

    private function getParams(string $searchQuery, string $category, string $page): array
    {
        $params = array();
        if($searchQuery !== '') {
            $params['q'] = $searchQuery;
        }
        if($category !== '') {
            $params['category'] = $category;
        }
        if($page !== '') {
            $params['page'] = $page;
        }
        $params['language'] = 'jp';
        $params['country'] = 'jp';
        return $params;
    }
}
