<?php

include 'headers.php'; include 'params.php';

require_once __DIR__.'/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/../../");
$dotenv->load();

function callAPI($url, $headers, $params)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $query = http_build_query($params);
    curl_setopt($curl, CURLOPT_URL, "$url?$query");

    $response = curl_exec($curl);

    $info = curl_getinfo($curl);
    $errno = curl_errno($curl);
    curl_close($curl);
    if( $response === false || $errno != 0 ) {  // Checking error
    } else if($info['http_code'] != 200) {}
    return $response;
}

$url = "https://api.bing.microsoft.com/v7.0/news/search";
echo callAPI($url, getBingHeaders() , getBingParams('', 'jp', 1000, 'Day', 'Off'));