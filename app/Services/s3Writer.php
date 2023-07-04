<?php

namespace App\Services;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;

class S3Writer
{
    protected $s3;
    protected $bucket;

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

    public function writeToFile(array $data) : void
    {
        try {
            $dir = '/short-news/articles/';
            $filename = time().'.json';
            $fileContent = json_encode($data);

            $this->s3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $dir.$filename,
                'Body' => $fileContent,
                'ContentType' => 'application/json',
            ]);
        } catch (AwsException $e) {
            echo "There was an error uploading the file.\n";
            echo $e->getMessage();
        }
    }
}
