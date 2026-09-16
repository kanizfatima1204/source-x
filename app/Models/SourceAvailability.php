<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourceAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'product_id',
        'available_quantity',
        'unit',
        'available_from',
        'available_until',
        'is_available',
    ];

    protected $casts = [
        'available_quantity' => 'decimal:2',
        'available_from' => 'date',
        'available_until' => 'date',
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
}
