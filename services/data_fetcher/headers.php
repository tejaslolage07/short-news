<?php
require_once __DIR__.'/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/../../"); // Dir where your .env file is located
$dotenv->load();

function getBingHeaders(){
    return array(
        "Ocp-Apim-Subscription-Key : " . env('BING_API_KEY'), 
        "mkt : ja-JP"
    );
}
