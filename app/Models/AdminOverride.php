<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_request_id',
        'match_result_id',
        'admin_id',
        'previous_rank',
        'new_rank',
        'action',
        'reason',
    ];

    protected $casts = [
        'previous_rank' => 'integer',
        'new_rank' => 'integer',
    ];

    public function buyerRequest(): BelongsTo
    {
        return $this->belongsTo(
            BuyerRequest::class
        );
    }

    public function matchResult(): BelongsTo
    {
        return $this->belongsTo(
            MatchResult::class
        );
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'admin_id'
        );
    }
}