<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'weight',
        'is_active',
        'configuration',
        'description',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'configuration' => 'array',
    ];
}
