<?php

namespace App\Services\NewsHandler\NewsParser\Contracts;

interface NewsParserInterface
{
    public function getParsedData(array $response, string $fetchedAt): array;
}
