<?php

namespace Database\Seeders;

use App\Models\NewsWebsite;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsWebsiteSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        NewsWebsite::factory(5)->create();
    }
}
