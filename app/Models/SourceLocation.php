<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourceLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'country',
        'division',
        'district',
        'city',
        'area',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}