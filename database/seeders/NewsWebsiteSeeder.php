<?php

namespace Database\Seeders;

use App\Models\NewsWebsite;

use Illuminate\Database\Seeder;

class NewsWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        NewsWebsite::factory()->count(5)->create();
    }
}
