<!-- IMP! Bing is rejected due to the reason that it only sends short description of the whole news and not the full article. -->

<?php

use Illuminate\Support\Facades\Http;

class NewsFetcherForBingApiFetcher{
    private string $url = 'https://api.bing.microsoft.com/v7.0/news/search';
    private $headers;
    private $params;
    public $response;
    
    public function __construct($searchQuery = '', $articleCount = 1000){
        $this->setHeaders();
<<<<<<< HEAD
        $this->setParams($searchQuery, $articleCount);   
    }

    public function fetchResults(){
        $result = Http::withHeaders($this->headers)->get($this->url, $this->params);
=======
        $this->setParams($searchQuery, $articleCount);

        $result = $this->fetchResults();
>>>>>>> a0e502d (Implemented newsdata.io API fetcher and rejected Bing API)
        if ($result->successful()) {
            $this->response = $result->json();
        } else {
            $this->handleError($result);
        }
<<<<<<< HEAD
        return $result;
=======
>>>>>>> a0e502d (Implemented newsdata.io API fetcher and rejected Bing API)
    }

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

<<<<<<< HEAD
=======
    private function fetchResults(){
        return Http::withHeaders($this->headers)->get($this->url, $this->params);
    }

>>>>>>> a0e502d (Implemented newsdata.io API fetcher and rejected Bing API)
    private function handleError($response){
        $errorCode = $response->status();
        $errorMessage = $response->body();
        // Handle the error
        echo "Error code: $errorCode\n";
        echo "Error message: $errorMessage\n";
    }

}

$fetcher = new NewsFetcherForBingApiFetcher('', 5);

var_dump($fetcher->response);
