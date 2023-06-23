<?php

namespace App\Services\NewsHandler\NewsParser\Contracts;

interface NewsParser
{
    public function getParsedData(array $response): array;
}
