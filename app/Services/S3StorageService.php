<?php

namespace App\Services;

use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3StorageService
{
    private const DIR = '/short-news/articles/'; 

    public function writeToS3Bucket(array $dataArray): string|null
    {
        $filename = null;
        try {
            $filename = $this->getFileNameForUpload();
            Storage::disk('s3')->put($filename, json_encode($dataArray));
        } catch (AwsException $e) {
            $filename = null;
            echo 'There was an error uploading the file.'.$e->getMessage();
        } finally {
            return $filename;
        }
    }

    private function getFileNameForUpload(): string
    {
        $filename = self::DIR.Str::random(20).time();
        return $filename;
    }
}
