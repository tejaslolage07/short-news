<?php

include 'NewsFetcherForBing.php';

class ParserForBing{
    private $response = array();
    private $parsedData = array();  // This is the data that will be returned to the controller. // $response['results']
    
    public function __construct($response){
        $this->response = $response;
    }
    
    public function getParsedData(){
        $articles = $this->getJsonData();
        $this->parsedData = $this->parseBingData($articles);
        return $this->parsedData;
    }

    private function parseBingData($articles){
        $result = [];
        foreach($articles as $article){
            $formattedDate = $this->formatDate($article['datePublished']);
            if(isset($article['image']['thumbnail']['contentUrl'])){ // This is to prevent errors when the image is not available.
                $imageURL = $article['image']['thumbnail']['contentUrl'];
            }else{
                $imageURL = null;
            }
            $result[] = [
                'headline' => $article['name'],
                'url' => $article['url'],
                'author' => null,   // The Bing API doesn't send author data.
                'description' => $article['description'],
                'imageURL' => $imageURL,
                'sourceWebsite' => $article['provider'][0]['name'],
                'publishedAt' => $formattedDate,
                'fetchedAt' => date('Y-m-d H:i:s')
            ];
        }
        return $result;
    }
    
    private function formatDate($date){
        $formattedDate = new DateTime($date);
        return $formattedDate->format('Y-m-d H:i:s');
    }

    public function getJsonData(){
        $data = json_decode($this->response, true);
        return $data['value'];
    }
}

// Below lines are for testing purposes only.

$fetcher = new NewsFetcherForBingApiFetcher();
$response = $fetcher->fetchResults();
$parser = new ParserForBing($response);
$parsedData = $parser->getParsedData();
var_dump($parsedData);

/// IMP : MAKE PRIVATE AND REVIEW THE CODE.