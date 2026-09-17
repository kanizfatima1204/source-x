<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerSearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'keyword',
        'category',
        'location',
        'budget_level',
        'search_count',
    ];

    protected $casts = [
        'search_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
