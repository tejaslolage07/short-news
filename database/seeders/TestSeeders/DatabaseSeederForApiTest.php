<?php

namespace Database\Seeders\TestSeeders;

use Illuminate\Database\Seeder;

/**
 * @internal
 *
 * @coversNothing
 */
class DatabaseSeederForApiTest extends Seeder
{
    public function run(): void
    {
        \App\Models\NewsWebsite::factory()->count(5)->create();

        \App\Models\Article::factory()->count(100)->create();

        \App\Models\Article::factory()->count(200)->create([
            'short_news' => 'Not Empty',
        ]);

        \App\Models\Article::factory()->count(100)->create([
            'short_news' => 'Not Empty',
            'news_website_id' => null,
        ]);
    }
}
