<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationLog extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'score',
        'score_breakdown',
        'source',
        'algorithm',
        'clicked',
        'clicked_at',
    ];

    protected $casts = [
        'score' => 'float',
        'score_breakdown' => 'array',
        'clicked' => 'boolean',
        'clicked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
