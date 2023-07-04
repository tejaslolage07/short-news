<?php

namespace Tests\Feature;

use App\Services\S3StorageService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class S3StorageTest extends TestCase
{
    public function testFileIsUploaded(): void
    {
        Storage::fake('s3');

        $s3Service = new S3StorageService();

        $data = [
            'short_news' => null,
            'headline' => fake()->text(),
            'news_website_id' => 1,
            'author' => fake()->text(),
            'article_url' => fake()->url(),
            'image_url' => fake()->url(),
            'article_s3_filename' => fake()->text(),
            'published_at' => fake()->dateTime(),
            'fetched_at' => fake()->dateTime(),
            'source' => fake()->randomElement(['bingApi', 'newsDataIoApi', 'scraper']),
            'country' => fake()->randomElement(['jp', 'us', 'in']),
            'language' => fake()->randomElement(['ja', 'en']),
            'category' => fake()->randomElement(['World', 'Business', 'Technology', 'Entertainment', 'Sports', 'Science', 'Health']),
            'keywords' => fake()->randomElement([json_encode(['keyword1', 'keyword2', 'keyword3']), json_encode(['keyword4', 'keyword5', 'keyword6'])]),
        ];

        $filename = $s3Service->writeToS3Bucket($data);

        $this->assertNotNull($filename);
        Storage::disk('s3')->assertExists($filename);
    }
}
