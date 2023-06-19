<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'short_news' => fake()->text(),
            'headline' => fake()->text(),
            'author' => fake()->text(),
            'article_url' => fake()->url(),
            'image_url' => fake()->url(),
            'article_s3_filename' => fake()->text(),
            'published_at' => fake()->dateTime(),
            'fetched_at' => fake()->dateTime(),
            'source' => fake()->randomElement(['api', 'scraper']),
        ];
    }
}
