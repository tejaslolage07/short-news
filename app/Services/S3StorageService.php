<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3StorageService
{
    private const DIR = '/short-news/articles/';

    public function writeToS3Bucket(array $dataArray): string
    {
        $filename = $this->getFileNameForUpload();
        Storage::disk('s3')->put(self::DIR.$filename, json_encode($dataArray));

        return $filename;
    }

    private function getFileNameForUpload(): string
    {
        return Str::random(20).time();
    }
}
