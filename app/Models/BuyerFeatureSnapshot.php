<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerFeatureSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'search_count',
        'view_count',
        'request_count',
        'impression_count',
        'click_count',
        'inquiry_count',
        'conversion_count',
        'click_rate',
        'conversion_rate',
        'preferred_category',
        'preferred_location',
        'preferred_budget',
        'category_affinity',
        'location_affinity',
        'budget_affinity',
        'engagement_score',
        'last_activity_at',
        'snapshot_at',
    ];

    protected $casts = [
        'click_rate' => 'float',
        'conversion_rate' => 'float',
        'category_affinity' => 'float',
        'location_affinity' => 'float',
        'budget_affinity' => 'float',
        'engagement_score' => 'float',
        'last_activity_at' => 'datetime',
        'snapshot_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
