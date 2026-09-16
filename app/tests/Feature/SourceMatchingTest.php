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

class SourceMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_available_source_is_matched(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $category = Category::create([
            'name' => 'Fresh Produce',
            'slug' => 'fresh-produce-test',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Premium Mango',
            'slug' => 'premium-mango-test',
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $source = Source::create([
            'reference_code' => 'SX-TEST01',
            'name' => 'Test Source',
            'type' => 'supplier',
            'status' => 'active',
            'quality_score' => 90,
            'performance_score' => 90,
        ]);

        SourceProduct::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'price' => 500,
            'minimum_order_quantity' => 5,
            'quality_grade' => 'A',
            'quality_score' => 90,
            'is_available' => true,
        ]);

        SourceAvailability::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'available_quantity' => 100,
            'unit' => 'kg',
            'is_available' => true,
        ]);

        SourceVerification::create([
            'source_id' => $source->id,
            'status' => 'verified',
            'verification_type' => 'manual',
            'verified_by' => null,
            'verified_at' => now(),
        ]);

        $request = BuyerRequest::create([
            'user_id' => $buyer->id,
            'reference_code' => 'REQ-TEST01',
            'location' => 'Dhaka',
            'max_budget' => 600,
            'quality_requirement' => 'A',
            'status' => 'submitted',
        ]);

        BuyerRequestItem::create([
            'buyer_request_id' => $request->id,
            'category_id' => $category->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
        ]);

        $results = app(
            SourceMatchingService::class
        )->match($request->fresh());

        $this->assertCount(1, $results);

        $this->assertEquals(
            $source->id,
            $results->first()->source_id
        );

        $this->assertGreaterThan(
            0,
            (float) $results->first()->total_score
        );
    }

    public function test_unverified_source_is_excluded(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $category = Category::create([
            'name' => 'Spices',
            'slug' => 'spices-test',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Premium Turmeric',
            'slug' => 'premium-turmeric-test',
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $source = Source::create([
            'reference_code' => 'SX-TEST02',
            'name' => 'Unverified Source',
            'type' => 'supplier',
            'status' => 'active',
            'quality_score' => 90,
            'performance_score' => 90,
        ]);

        SourceProduct::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'price' => 300,
            'minimum_order_quantity' => 1,
            'quality_grade' => 'A',
            'quality_score' => 90,
            'is_available' => true,
        ]);

        SourceAvailability::create([
            'source_id' => $source->id,
            'product_id' => $product->id,
            'available_quantity' => 100,
            'unit' => 'kg',
            'is_available' => true,
        ]);

        $request = BuyerRequest::create([
            'user_id' => $buyer->id,
            'reference_code' => 'REQ-TEST02',
            'location' => 'Dhaka',
            'max_budget' => 500,
            'status' => 'submitted',
        ]);

        BuyerRequestItem::create([
            'buyer_request_id' => $request->id,
            'category_id' => $category->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
        ]);

        $results = app(
            SourceMatchingService::class
        )->match($request->fresh());

        $this->assertCount(0, $results);
    }
}
