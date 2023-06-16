<?php

function getBingParams($search_term, $language, $count, $freshness, $safeSearch){
    return array(
        "q" => $search_term, 
        "setLang" => $language, 
        "count" => $count, 
        "freshness" => $freshness, 
        "safeSearch" => $safeSearch
    );
}