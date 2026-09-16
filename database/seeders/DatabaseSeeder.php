<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Source;
use App\Models\SourceAvailability;
use App\Models\SourceLocation;
use App\Models\SourcePerformance;
use App\Models\SourceProduct;
use App\Models\SourceVerification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        $admin = User::updateOrCreate(
            [
                'email' => 'admin@source-x.test',
            ],
            [
                'name' => 'Source X Admin',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $buyer = User::updateOrCreate(
            [
                'email' => 'buyer@source-x.test',
            ],
            [
                'name' => 'Demo Buyer',
                'role' => 'buyer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $freshProduce = Category::updateOrCreate(
            ['slug' => 'fresh-produce'],
            [
                'name' => 'Fresh Produce',
                'description' => 'Fresh fruits and agricultural products.',
                'is_active' => true,
            ]
        );

        $spices = Category::updateOrCreate(
            ['slug' => 'spices'],
            [
                'name' => 'Spices',
                'description' => 'Local and imported spices.',
                'is_active' => true,
            ]
        );

        $packaging = Category::updateOrCreate(
            ['slug' => 'packaging'],
            [
                'name' => 'Packaging',
                'description' => 'Commercial packaging materials.',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products = [
            Product::updateOrCreate(
                ['slug' => 'premium-organic-mango'],
                [
                    'category_id' => $freshProduce->id,
                    'name' => 'Premium Organic Mango',
                    'description' => 'Premium quality organic mango.',
                    'unit' => 'kg',
                    'is_active' => true,
                ]
            ),

            Product::updateOrCreate(
                ['slug' => 'fresh-lychee'],
                [
                    'category_id' => $freshProduce->id,
                    'name' => 'Fresh Lychee',
                    'description' => 'Fresh seasonal lychee.',
                    'unit' => 'kg',
                    'is_active' => true,
                ]
            ),

            Product::updateOrCreate(
                ['slug' => 'premium-turmeric'],
                [
                    'category_id' => $spices->id,
                    'name' => 'Premium Turmeric',
                    'description' => 'Premium quality turmeric.',
                    'unit' => 'kg',
                    'is_active' => true,
                ]
            ),

            Product::updateOrCreate(
                ['slug' => 'food-grade-carton-box'],
                [
                    'category_id' => $packaging->id,
                    'name' => 'Food Grade Carton Box',
                    'description' => 'Commercial food-grade carton packaging.',
                    'unit' => 'piece',
                    'is_active' => true,
                ]
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Sources
        |--------------------------------------------------------------------------
        */

        $sources = [
            [
                'reference_code' => 'SX-1024',
                'name' => 'Premium Agricultural Source',
                'type' => 'supplier',
                'quality_score' => 90,
                'performance_score' => 88,
            ],
            [
                'reference_code' => 'SX-1098',
                'name' => 'Verified Fresh Goods Source',
                'type' => 'supplier',
                'quality_score' => 86,
                'performance_score' => 82,
            ],
            [
                'reference_code' => 'SX-1134',
                'name' => 'Regional Produce Source',
                'type' => 'supplier',
                'quality_score' => 78,
                'performance_score' => 76,
            ],
            [
                'reference_code' => 'SX-1188',
                'name' => 'General Commercial Source',
                'type' => 'supplier',
                'quality_score' => 72,
                'performance_score' => 68,
            ],
        ];

        foreach ($sources as $sourceData) {
            $source = Source::updateOrCreate(
                [
                    'reference_code' => $sourceData['reference_code'],
                ],
                [
                    ...$sourceData,
                    'status' => 'active',
                ]
            );

            SourcePerformance::updateOrCreate(
                [
                    'source_id' => $source->id,
                ],
                [
                    'total_orders' => 100,
                    'completed_orders' => 92,
                    'cancelled_orders' => 3,
                    'late_orders' => 5,
                    'rating' => $source->performance_score,
                    'performance_score' => $source->performance_score,
                ]
            );

            SourceVerification::updateOrCreate(
                [
                    'source_id' => $source->id,
                ],
                [
                    'status' => 'verified',
                    'verification_type' => 'manual',
                    'notes' => 'Verified by Source X operations team.',
                    'verified_by' => $admin->id,
                    'verified_at' => now(),
                ]
            );

            SourceLocation::updateOrCreate(
                [
                    'source_id' => $source->id,
                ],
                [
                    'country' => 'Bangladesh',
                    'division' => 'Dhaka',
                    'district' => 'Dhaka',
                    'city' => 'Dhaka',
                    'area' => 'Uttara',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Source Products
        |--------------------------------------------------------------------------
        */

        $mango = Product::where(
            'slug',
            'premium-organic-mango'
        )->firstOrFail();

        $lychee = Product::where(
            'slug',
            'fresh-lychee'
        )->firstOrFail();

        $turmeric = Product::where(
            'slug',
            'premium-turmeric'
        )->firstOrFail();

        $carton = Product::where(
            'slug',
            'food-grade-carton-box'
        )->firstOrFail();

        $source1024 = Source::where(
            'reference_code',
            'SX-1024'
        )->firstOrFail();

        $source1098 = Source::where(
            'reference_code',
            'SX-1098'
        )->firstOrFail();

        $source1134 = Source::where(
            'reference_code',
            'SX-1134'
        )->firstOrFail();

        $source1188 = Source::where(
            'reference_code',
            'SX-1188'
        )->firstOrFail();

        $sourceProducts = [
            [
                'source_id' => $source1024->id,
                'product_id' => $mango->id,
                'price' => 105,
                'minimum_order_quantity' => 20,
                'quality_grade' => 'premium',
                'quality_score' => 95,
            ],
            [
                'source_id' => $source1098->id,
                'product_id' => $mango->id,
                'price' => 112,
                'minimum_order_quantity' => 10,
                'quality_grade' => 'premium',
                'quality_score' => 90,
            ],
            [
                'source_id' => $source1134->id,
                'product_id' => $mango->id,
                'price' => 98,
                'minimum_order_quantity' => 50,
                'quality_grade' => 'standard',
                'quality_score' => 78,
            ],
            [
                'source_id' => $source1188->id,
                'product_id' => $mango->id,
                'price' => 125,
                'minimum_order_quantity' => 10,
                'quality_grade' => 'standard',
                'quality_score' => 72,
            ],
            [
                'source_id' => $source1024->id,
                'product_id' => $lychee->id,
                'price' => 220,
                'minimum_order_quantity' => 10,
                'quality_grade' => 'premium',
                'quality_score' => 90,
            ],
            [
                'source_id' => $source1098->id,
                'product_id' => $turmeric->id,
                'price' => 310,
                'minimum_order_quantity' => 20,
                'quality_grade' => 'premium',
                'quality_score' => 92,
            ],
            [
                'source_id' => $source1188->id,
                'product_id' => $carton->id,
                'price' => 85,
                'minimum_order_quantity' => 100,
                'quality_grade' => 'standard',
                'quality_score' => 75,
            ],
        ];

        foreach ($sourceProducts as $data) {
            SourceProduct::updateOrCreate(
                [
                    'source_id' => $data['source_id'],
                    'product_id' => $data['product_id'],
                ],
                [
                    ...$data,
                    'is_available' => true,
                ]
            );

            SourceAvailability::updateOrCreate(
                [
                    'source_id' => $data['source_id'],
                    'product_id' => $data['product_id'],
                ],
                [
                    'available_quantity' => 500,
                    'unit' => $data['product_id'] === $carton->id
                        ? 'piece'
                        : 'kg',
                    'available_from' => now()->toDateString(),
                    'available_until' => now()
                        ->addDays(14)
                        ->toDateString(),
                    'is_available' => true,
                ]
            );
        }
    }
}