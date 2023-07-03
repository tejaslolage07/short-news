<?php

namespace App\Services\NewsHandler\NewsFetcher\Contracts;

interface NewsFetcherInterface
{
    public function fetch(string $untilDateTime): array;
}
