<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'location',
        'price',
        'budget_level',
        'is_available',
        'is_verified',
        'view_count',
        'request_count',
        'popularity_score',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function views(): HasMany
    {
        return $this->hasMany(BuyerProductView::class);
    }

    public function recommendationLogs(): HasMany
    {
        return $this->hasMany(RecommendationLog::class);
    }
}
