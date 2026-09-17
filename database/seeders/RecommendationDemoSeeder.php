<?php

namespace Database\Seeders;

use App\Models\BuyerProductView;
use App\Models\BuyerRequest;
use App\Models\BuyerSearchHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecommendationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if (!$user) {
            return;
        }

        $products = [
            [
                'name' => 'Organic Honey',
                'category' => 'Organic Food',
                'location' => 'Manikganj',
                'price' => 850,
                'budget_level' => 'medium',
                'is_available' => true,
                'is_verified' => true,
                'popularity_score' => 92,
                'description' =>
                    'Pure organic honey from verified local producers.',
            ],
            [
                'name' => 'Organic Rice',
                'category' => 'Organic Food',
                'location' => 'Manikganj',
                'price' => 1200,
                'budget_level' => 'medium',
                'is_available' => true,
                'is_verified' => true,
                'popularity_score' => 87,
                'description' =>
                    'Naturally grown organic rice.',
            ],
            [
                'name' => 'Fresh Organic Vegetables',
                'category' => 'Organic Food',
                'location' => 'Dhaka',
                'price' => 950,
                'budget_level' => 'medium',
                'is_available' => true,
                'is_verified' => true,
                'popularity_score' => 80,
                'description' =>
                    'Fresh seasonal organic vegetables.',
            ],
            [
                'name' => 'Premium Mango',
                'category' => 'Organic Food',
                'location' => 'Rajshahi',
                'price' => 1600,
                'budget_level' => 'high',
                'is_available' => true,
                'is_verified' => true,
                'popularity_score' => 95,
                'description' =>
                    'Premium naturally grown mango.',
            ],
            [
                'name' => 'Organic Lentils',
                'category' => 'Organic Food',
                'location' => 'Manikganj',
                'price' => 700,
                'budget_level' => 'low',
                'is_available' => true,
                'is_verified' => false,
                'popularity_score' => 65,
                'description' =>
                    'Organic lentils from local farmers.',
            ],
            [
                'name' => 'Organic Spice Pack',
                'category' => 'Organic Food',
                'location' => 'Manikganj',
                'price' => 1050,
                'budget_level' => 'medium',
                'is_available' => false,
                'is_verified' => true,
                'popularity_score' => 72,
                'description' =>
                    'Organic spice collection.',
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                [
                    'slug' => Str::slug($data['name']),
                ],
                $data
            );
        }

        BuyerSearchHistory::create([
            'user_id' => $user->id,
            'keyword' => 'organic food',
            'category' => 'Organic Food',
            'location' => 'Manikganj',
            'budget_level' => 'medium',
        ]);

        BuyerSearchHistory::create([
            'user_id' => $user->id,
            'keyword' => 'organic honey',
            'category' => 'Organic Food',
            'location' => 'Manikganj',
            'budget_level' => 'medium',
        ]);

        BuyerSearchHistory::create([
            'user_id' => $user->id,
            'keyword' => 'organic rice',
            'category' => 'Organic Food',
            'location' => 'Manikganj',
            'budget_level' => 'medium',
        ]);

        BuyerRequest::create([
            'user_id' => $user->id,
            'category' => 'Organic Food',
            'location' => 'Manikganj',
            'budget_level' => 'medium',
            'description' =>
                'Looking for organic food suppliers.',
        ]);

        $product = Product::where(
            'slug',
            Str::slug('Organic Honey')
        )->first();

        if ($product) {
            BuyerProductView::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ],
                [
                    'view_count' => 5,
                    'last_viewed_at' => now(),
                ]
            );
        }
    }
}
