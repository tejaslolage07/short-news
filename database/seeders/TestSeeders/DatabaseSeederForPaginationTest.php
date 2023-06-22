<?php

namespace Database\Seeders\TestSeeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * @internal
 *
 * @coversNothing
 */
class DatabaseSeederForPaginationTest extends Seeder
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

        \App\Models\Article::factory(2)->create();

        \App\Models\Article::factory(1)->create([
            'short_news' => 'Not Empty',
            'published_at' => '2020-06-20 00:00:00',
        ]);
        \App\Models\Article::factory(1)->create([
            'short_news' => 'Not Empty',
            'published_at' => '2021-06-20 00:00:00',
        ]);
        \App\Models\Article::factory(2)->create([
            'published_at' => '2022-06-20 00:00:00',
            'short_news' => 'Not Empty',
        ]);

        \App\Models\Article::factory(2)->create([
            'published_at' => '2023-06-20 00:00:00',
            'short_news' => 'Not Empty',
        ]);

        \App\Models\Article::factory(2)->create();
    }
}
