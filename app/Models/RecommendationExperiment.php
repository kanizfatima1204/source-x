<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecommendationExperiment extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'control_algorithm',
        'variant_algorithm',
        'traffic_percentage',
        'minimum_sample_size',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'traffic_percentage' => 'integer',
        'minimum_sample_size' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(
            RecommendationExperimentAssignment::class,
            'experiment_id'
        );
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }
}
