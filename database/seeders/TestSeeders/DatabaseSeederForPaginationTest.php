<?php

namespace Database\Seeders\TestSeeders;

use Illuminate\Database\Seeder;

/**
 * @internal
 *
 * @coversNothing
 */
class DatabaseSeederForPaginationTest extends Seeder
{
    public function run(): void
    {
        \App\Models\NewsWebsite::factory()->count(5)->create();

        \App\Models\Article::factory()->count(2)->create();

        \App\Models\Article::factory()->count(1)->create([
            'short_news' => 'Not Empty',
            'published_at' => '2020-06-20 00:00:00',
        ]);
        \App\Models\Article::factory()->count(1)->create([
            'short_news' => 'Not Empty',
            'published_at' => '2021-06-20 00:00:00',
        ]);
        \App\Models\Article::factory()->count(2)->create([
            'published_at' => '2022-06-20 00:00:00',
            'short_news' => 'Not Empty',
        ]);

        \App\Models\Article::factory()->count(2)->create([
            'published_at' => '2023-06-20 00:00:00',
            'short_news' => 'Not Empty',
        ]);

        \App\Models\Article::factory()->count(2)->create();
    }
}
