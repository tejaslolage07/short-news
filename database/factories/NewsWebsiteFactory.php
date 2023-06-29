<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NewsWebsiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'website' => fake()->text(),
        ];
    }
}
