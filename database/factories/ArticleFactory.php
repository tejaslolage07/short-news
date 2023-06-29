<?php

namespace Database\Factories;

use App\Models\NewsWebsite;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'short_news' => '',
            'headline' => fake()->text(),
            'news_website_id' => NewsWebsite::all()->random()->id,
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
    }
}
