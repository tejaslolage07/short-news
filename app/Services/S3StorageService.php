<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3StorageService
{
    public const DIR = 'articles/';
    public const LOCAL_DIR = 'articles-local/';
    public const EXT = '.json';
    private $directory;

    public function __construct()
    {
        $this->directory = App::environment('production') ? self::DIR : self::LOCAL_DIR;
    }
    
    public function readFromS3Bucket(string $filename): ?string
    {
        return Storage::disk('s3')->get($this->directory.$filename.self::EXT);
    }

    public function writeToS3Bucket(array $dataArray): string
    {
        $filename = $this->getFileNameForUpload();
        Storage::disk('s3')->put($this->directory.$filename.self::EXT, json_encode($dataArray));

        return $filename;
    }

    private function getFileNameForUpload(): string
    {
        return Str::random(20).time();
    }
}
