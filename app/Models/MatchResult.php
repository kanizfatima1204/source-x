<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_request_id',
        'source_id',
        'total_score',
        'rank',
        'confidence',
        'status',
        'is_algorithm_selected',
        'is_admin_selected',
        'summary',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'rank' => 'integer',
        'is_algorithm_selected' => 'boolean',
        'is_admin_selected' => 'boolean',
    ];

    public function buyerRequest(): BelongsTo
    {
        return $this->belongsTo(
            BuyerRequest::class
        );
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(
            Source::class
        );
    }

    public function reasons(): HasMany
    {
        return $this->hasMany(
            MatchReason::class
        );
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(
            AdminOverride::class
        );
    }

    public function selectedByAdmin(): bool
    {
        return $this->is_admin_selected === true;
    }

    public function selectedByAlgorithm(): bool
    {
        return $this->is_algorithm_selected === true;
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRecommended(): bool
    {
        return $this->status === 'recommended';
    }
}