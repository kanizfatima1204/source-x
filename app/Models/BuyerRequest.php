<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuyerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference_code',
        'location',
        'min_budget',
        'max_budget',
        'quality_requirement',
        'required_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'min_budget' => 'decimal:2',
        'max_budget' => 'decimal:2',
        'required_by' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BuyerRequestItem::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchResult::class);
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(AdminOverride::class);
    }
}
