<?php

namespace Tests\Feature;

use App\Models\BuyerRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerRequestAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_cannot_view_another_buyers_request(): void
    {
        $owner = User::factory()->create([
            'role' => 'buyer',
        ]);

        $otherBuyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $request = BuyerRequest::create([
            'user_id' => $owner->id,
            'reference_code' => 'REQ-AUTH01',
            'location' => 'Dhaka',
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($otherBuyer)
            ->get(
                route(
                    'buyer.requests.show',
                    $request
                )
            );

        $response->assertForbidden();
    }
}
