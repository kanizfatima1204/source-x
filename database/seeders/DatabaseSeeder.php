<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RecommendationRuleSeeder::class,
            HybridRecommendationRuleSeeder::class,
            RecommendationDemoSeeder::class,
            RecommendationControlCenterSeeder::class,
        ]);
    }
}
