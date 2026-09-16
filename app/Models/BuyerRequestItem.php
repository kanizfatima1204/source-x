<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_request_id',
        'category_id',
        'product_id',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function buyerRequest(): BelongsTo
    {
        return $this->belongsTo(
            BuyerRequest::class
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            Category::class
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class
        );
    }
}