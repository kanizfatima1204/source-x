<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourcePerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'total_orders',
        'completed_orders',
        'cancelled_orders',
        'late_orders',
        'rating',
        'performance_score',
    ];

    protected $casts = [
        'total_orders' => 'integer',
        'completed_orders' => 'integer',
        'cancelled_orders' => 'integer',
        'late_orders' => 'integer',
        'rating' => 'integer',
        'performance_score' => 'integer',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}