<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'product_id',
        'price',
        'minimum_order_quantity',
        'quality_grade',
        'quality_score',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'minimum_order_quantity' => 'decimal:2',
        'quality_score' => 'integer',
        'is_available' => 'boolean',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(
            SourceAvailability::class,
            'source_id',
            'source_id'
        )->where(
            'product_id',
            $this->product_id
        );
    }
}
