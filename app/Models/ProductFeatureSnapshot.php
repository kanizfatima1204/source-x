<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFeatureSnapshot extends Model
{
    protected $fillable = [
        'product_id',
        'category',
        'location',
        'budget_level',
        'price_score',
        'popularity_score',
        'availability_score',
        'verification_score',
        'view_count',
        'request_count',
        'recommendation_impressions',
        'recommendation_clicks',
        'recommendation_inquiries',
        'recommendation_conversions',
        'ctr',
        'conversion_rate',
        'quality_score',
        'snapshot_at',
    ];

    protected $casts = [
        'price_score' => 'float',
        'popularity_score' => 'float',
        'availability_score' => 'float',
        'verification_score' => 'float',
        'ctr' => 'float',
        'conversion_rate' => 'float',
        'quality_score' => 'float',
        'snapshot_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
