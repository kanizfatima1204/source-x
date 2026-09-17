<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationTrainingSample extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'recommendation_log_id',
        'algorithm',
        'variant',
        'recommendation_score',
        'impressions',
        'clicks',
        'inquiries',
        'conversions',
        'engagement_label',
        'clicked',
        'converted',
        'buyer_features',
        'product_features',
        'recommendation_features',
        'first_seen_at',
        'last_event_at',
    ];

    protected $casts = [
        'recommendation_score' => 'float',
        'engagement_label' => 'float',
        'clicked' => 'boolean',
        'converted' => 'boolean',
        'buyer_features' => 'array',
        'product_features' => 'array',
        'recommendation_features' => 'array',
        'first_seen_at' => 'datetime',
        'last_event_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recommendationLog(): BelongsTo
    {
        return $this->belongsTo(
            RecommendationLog::class,
            'recommendation_log_id'
        );
    }
}
