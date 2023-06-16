<?php

use Illuminate\Support\Facades\Http;
require_once __DIR__.'/../../../vendor/autoload.php';

class BingNewsFetcher{
    private $url = 'https://api.bing.microsoft.com/v7.0/news/search';
    private $headers;
    private $params;
    public $response;

    private function setHeaders(){
        $this->headers = [
            'Ocp-Apim-Subscription-Key' => env('BING_API_KEY'),
            "mkt" => "ja-JP"
        ];
    }

    private function setParams($searchQuery, $count = 1000, $language = 'jp', $freshness = 'Day', $safeSearch = "Off"){
        $this->params = array(
            "q" => $searchQuery,
            "setLang" => $language, 
            "count" => $count, 
            "freshness" => $freshness, 
            "safeSearch" => $safeSearch
        );
    }

    private function fetchResults(){
        return Http::withHeaders($this->headers)->get($this->url, $this->params);
    }

    private function handleError($response){
        $errorCode = $response->status();
        $errorMessage = $response->body();
        // Handle the error
        echo "Error code: $errorCode\n";
        echo "Error message: $errorMessage\n";
    }

    public function __construct($searchQuery = '', $count = 1000){
        $this->setHeaders();
        $this->setParams($searchQuery, $count);

        $result = $this->fetchResults();
        if ($result->successful()) {
            $this->response = $result->json();
        } else {
            $this->handleError($result);
        }
    }
}

$fetcher = new BingNewsFetcher();

var_dump($fetcher->response);
