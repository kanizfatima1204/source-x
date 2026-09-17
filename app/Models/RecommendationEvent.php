<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recommendation_log_id',
        'product_id',
        'event_type',
        'recommendation_score',
        'algorithm',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'recommendation_score' => 'float',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recommendationLog(): BelongsTo
    {
        return $this->belongsTo(
            RecommendationLog::class
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
