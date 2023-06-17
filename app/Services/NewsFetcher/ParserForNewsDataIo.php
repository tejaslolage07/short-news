<?php

include 'NewsFetcherForNewsDataIo.php';

class ParserForNewsDataIo{
    private $response = array();
    private $parsedData = array();  // This is the data that will be returned to the controller.
    
    public function __construct($response){
        $this->response = $response;
    }
    
    public function getParsedData(){
        $articles = $this->getJsonData();
        $this->parsedData = $this->parseNewsDataIoData($articles);
        return $this->parsedData;
    }

    private function parseNewsDataIoData($articles){
        $parsedData = [];
        foreach($articles as $article){
            $formattedDate = $this->formatDate($article['pubDate']);
            $parsedData[] = [
                'headline' => $article['title'],
                'url' => $article['link'],
                'author' => null,                   // The NewsDataIo API doesn't send author data, only the source website.
                'content' => $article['content'],
                'imageURL' => $article['image_url'],
                'sourceWebsite' => $article['creator'],
                'publishedAt' => $formattedDate,
                'fetchedAt' => date('Y-m-d H:i:s')  // This is not the exact time the article was fetched, but rather the time when it was parsed. (Close enough to be acceptable)
            ];
        }
        return $parsedData;
    }

    private function getJsonData(){
        $data = json_decode($this->response, true);
        return $data['results'];
    }

    private function formatDate($date){
        $formattedDate = new DateTime($date);
        return $formattedDate->format('Y-m-d H:i:s');
    }
}

// Below lines are for testing purposes only.

$fetcher = new NewsFetcherForNewsDataIoApiFetcher();
$response = $fetcher->fetchResults();
$parser = new ParserForNewsDataIo($response);
$parsedData = $parser->getParsedData();
var_dump($parsedData);


// TODO: Add pagination facility to the NewsDataIo API fetcher.