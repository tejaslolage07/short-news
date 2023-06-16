<?php

use Illuminate\Support\Facades\Http;

class NewsFetcherForNewsDataIoApiFetcher{
    private string $url = 'https://newsdata.io/api/1/news';
    private $headers = array();
    private $params = array();
    public $response = array();
    
    public function __construct(string $searchQuery = '', string $category = ''){
        $this->setHeaders();
        $this->setParams($searchQuery, $category);
    }
    
    public function fetchResults(){
        $result = Http::withHeaders($this->headers)->get($this->url, $this->params);
        if ($result->successful()) {
            $this->response = $result->json();
        } else {
            $this->handleError($result);
        }
        return $result;
    }

    private function setHeaders(){
        $this->headers = [
            'X-ACCESS-KEY' => env('NEWS_DATA_IO_KEY')
        ];
    }

    private function setParams(string $searchQuery, string $category){
        $this->params = array(
            "language" => 'jp',
            "country" => "jp",
            // 'page' => '',
        );
        if($category !== ''){
            $this->params['category'] = $category;
        }
        if($searchQuery !== ''){
            $this->params['q'] = $searchQuery;
        }
    }


    private function handleError($response){
        $errorCode = $response->status();
        $errorMessage = $response->body();
        // Handle the error
        echo "Error code: $errorCode\n";
        echo "Error message: $errorMessage\n";
    }

}

$fetcher = new NewsFetcherForNewsDataIoApiFetcher();

var_dump($fetcher->fetchResults());
