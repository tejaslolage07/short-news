<?php

namespace App\Services\NewsHandler\NewsFetcher\Contracts;

interface NewsFetcher
{
    public function fetch(string $untilDateTime): array;
}
