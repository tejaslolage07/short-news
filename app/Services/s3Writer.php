<?php

namespace App\Services;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Support\Str;

class S3Writer
{
    protected $s3;
    protected $bucket;
    protected $dir = '/short-news/articles/';

    public function __construct()
    {
        $this->s3 = new S3Client([
            'region' => config('services.aws.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('services.aws.access_key_id'),
                'secret' => config('services.aws.secret_key'),
            ],
        ]);

        $this->bucket = config('services.aws.bucket_name');
    }

    public function writeToS3Bucket(array $dataArray): array
    {
        $filenames = [];
        foreach ($dataArray as $data) {
            try {
                $filename = $this->writeJsonFile($data);
            } catch (AwsException $e) {
                $filename = null;
                echo 'There was an error uploading the file.'.$e->getMessage();
            } finally {
                array_push($filenames, $filename);
            }
        }

        return $filenames;
    }

    private function writeJsonFile(array $data): string
    {
        $filename = Str::random(20).time().'.json';
        $fileContent = json_encode($data);

        $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => $this->dir.$filename,
            'Body' => $fileContent,
            'ContentType' => 'application/json',
        ]);

        return $filename;
    }
}
