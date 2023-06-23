<?php

namespace App\Services\Interfaces;

interface NewsParser
{
    public function getParsedData(array $response): array;
}
