<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Source extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'name',
        'type',
        'status',
        'quality_score',
        'performance_score',
        'internal_notes',
    ];

    protected $casts = [
        'quality_score' => 'integer',
        'performance_score' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(SourceProduct::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(SourceLocation::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(SourceVerification::class);
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(SourceVerification::class)
            ->latestOfMany();
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(SourceAvailability::class);
    }

    public function performance(): HasOne
    {
        return $this->hasOne(SourcePerformance::class);
    }

    public function matchResults(): HasMany
    {
        return $this->hasMany(MatchResult::class);
    }
}
