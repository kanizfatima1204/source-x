<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationExperimentAssignment extends Model
{
    protected $fillable = [
        'experiment_id',
        'user_id',
        'variant',
        'algorithm',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function experiment(): BelongsTo
    {
        return $this->belongsTo(
            RecommendationExperiment::class,
            'experiment_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
