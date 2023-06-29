<?php

namespace Database\Seeders\TestSeeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * @internal
 *
 * @coversNothing
 */
class DatabaseSeederForApiTest extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\NewsWebsite::factory(5)->create();
        // for ($i = 0; $i < 2000; ++$i) {
        //     \App\Models\Article::factory(1)->create();
        // }x
        // for ($i = 0; $i < 2000; ++$i) {
        //     \App\Models\Article::factory(1)->create([
        //         'short_news' => 'Not Empty',
        //     ]);
        // }
        // for ($i = 0; $i < 2000; ++$i) {
        //     \App\Models\Article::factory(1)->create();
        // }

        \App\Models\Article::factory(100)->create();

        \App\Models\Article::factory(200)->create([
            'short_news' => 'Not Empty',
        ]);

        \App\Models\Article::factory(100)->create([
            'short_news' => 'Not Empty',
            'news_website_id' => null,
        ]);
    }
}
