<?php

namespace Tests\Feature;

use App\Models\BuyerRequest;
use App\Models\BuyerRequestItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Source;
use App\Models\SourceAvailability;
use App\Models\SourceProduct;
use App\Models\SourceVerification;
use App\Models\User;
use App\Services\SourceMatching\SourceMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceMatchingHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_below_moq_is_not_eligible(): void
    {
        $user = User::create([
            'name' => 'Test Buyer',
            'email' => 'buyer-moq@test.test',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $category = Category::create([
            'name' => 'Fresh Produce',
            'slug' => 'fresh-produce',
            'description' => null,
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Premium Mango',
            'slug' => 'premium-mango-test',
            'description' => null,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $source = Source::create([
            'reference_code' => 'SX-9001',
            'name' => 'MOQ Test Source',
            'type' => 'supplier',
            'status' => 'active',
            'quality_score' => 90,
            'performance_score' => 90,
            'internal_notes' => null,
        ]);

        SourceVerification::create([
            'source_id' => $source->id,
            'status' => 'verified',
            'verification_type' => 'manual',
            'notes' => 'Test verification',
            'verified_by' => null,
            'verified_at' => now(),
        ]);

        SourceProduct::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'price' => 100,
            'minimum_order_quantity' => 100,
            'quality_grade' => 'premium',
            'quality_score' => 90,
            'is_available' => true,
        ]);

        SourceAvailability::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'available_quantity' => 500,
            'unit' => 'kg',
            'available_from' => now()->subDay()->toDateString(),
            'available_until' => now()->addDays(30)->toDateString(),
            'is_available' => true,
        ]);

        $request = BuyerRequest::create([
            'user_id' => $user->id,
            'reference_code' => 'REQ-MOQ01',
            'location' => 'Dhaka',
            'min_budget' => 50,
            'max_budget' => 150,
            'quality_requirement' => 'high',
            'required_by' => now()->addDays(7)->toDateString(),
            'status' => 'submitted',
            'notes' => null,
        ]);

        BuyerRequestItem::create([
            'buyer_request_id' => $request->id,
            'category_id' => $category->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
        ]);

        $results =
            app(
                SourceMatchingService::class
            )->match($request->fresh());

        $this->assertCount(
            0,
            $results
        );
    }

    public function test_verified_source_with_sufficient_quantity_and_moq_matches(): void
    {
        $user = User::create([
            'name' => 'Valid Buyer',
            'email' => 'valid-buyer@test.test',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $category = Category::create([
            'name' => 'Spices',
            'slug' => 'spices-test',
            'description' => null,
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Premium Turmeric',
            'slug' => 'premium-turmeric-test',
            'description' => null,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $source = Source::create([
            'reference_code' => 'SX-9002',
            'name' => 'Valid Matching Source',
            'type' => 'supplier',
            'status' => 'active',
            'quality_score' => 95,
            'performance_score' => 95,
            'internal_notes' => null,
        ]);

        SourceVerification::create([
            'source_id' => $source->id,
            'status' => 'verified',
            'verification_type' => 'manual',
            'notes' => 'Verified source',
            'verified_by' => null,
            'verified_at' => now(),
        ]);

        SourceProduct::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'price' => 100,
            'minimum_order_quantity' => 5,
            'quality_grade' => 'premium',
            'quality_score' => 95,
            'is_available' => true,
        ]);

        SourceAvailability::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'available_quantity' => 500,
            'unit' => 'kg',
            'available_from' => now()->subDay()->toDateString(),
            'available_until' => now()->addDays(30)->toDateString(),
            'is_available' => true,
        ]);

        $request = BuyerRequest::create([
            'user_id' => $user->id,
            'reference_code' => 'REQ-VALID01',
            'location' => 'Dhaka',
            'min_budget' => 50,
            'max_budget' => 150,
            'quality_requirement' => 'high',
            'required_by' => now()->addDays(7)->toDateString(),
            'status' => 'submitted',
            'notes' => null,
        ]);

        BuyerRequestItem::create([
            'buyer_request_id' => $request->id,
            'category_id' => $category->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
        ]);

        $results =
            app(
                SourceMatchingService::class
            )->match($request->fresh());

        $this->assertCount(
            1,
            $results
        );

        $match = $results->first();

        $this->assertSame(
            $source->id,
            $match->source_id
        );

        $this->assertGreaterThan(
            0,
            (float) $match->total_score
        );

        $this->assertNotEmpty(
            $match->reasons
        );
    }

    public function test_unverified_source_is_excluded(): void
    {
        $user = User::create([
            'name' => 'Verification Buyer',
            'email' => 'verification@test.test',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $category = Category::create([
            'name' => 'Packaging',
            'slug' => 'packaging-test',
            'description' => null,
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Food Carton',
            'slug' => 'food-carton-test',
            'description' => null,
            'unit' => 'piece',
            'is_active' => true,
        ]);

        $source = Source::create([
            'reference_code' => 'SX-9003',
            'name' => 'Unverified Source',
            'type' => 'supplier',
            'status' => 'active',
            'quality_score' => 95,
            'performance_score' => 95,
            'internal_notes' => null,
        ]);

        SourceProduct::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'price' => 10,
            'minimum_order_quantity' => 1,
            'quality_grade' => 'premium',
            'quality_score' => 95,
            'is_available' => true,
        ]);

        SourceAvailability::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'available_quantity' => 1000,
            'unit' => 'piece',
            'available_from' => now()->subDay()->toDateString(),
            'available_until' => now()->addDays(30)->toDateString(),
            'is_available' => true,
        ]);

        $request = BuyerRequest::create([
            'user_id' => $user->id,
            'reference_code' => 'REQ-VERIFY01',
            'location' => 'Dhaka',
            'min_budget' => 1,
            'max_budget' => 20,
            'quality_requirement' => 'medium',
            'required_by' => now()->addDays(7)->toDateString(),
            'status' => 'submitted',
            'notes' => null,
        ]);

        BuyerRequestItem::create([
            'buyer_request_id' => $request->id,
            'category_id' => $category->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'piece',
        ]);

        $results =
            app(
                SourceMatchingService::class
            )->match($request->fresh());

        $this->assertCount(
            0,
            $results
        );
    }
}