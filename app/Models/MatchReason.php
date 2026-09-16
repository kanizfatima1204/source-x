<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_result_id',
        'factor',
        'score',
        'weight',
        'weighted_score',
        'status',
        'message',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'weight' => 'decimal:2',
        'weighted_score' => 'decimal:2',
    ];

    public function matchResult(): BelongsTo
    {
        return $this->belongsTo(
            MatchResult::class
        );
    }

    public function isStrong(): bool
    {
        return (float) $this->score >= 85;
    }

    public function isPartial(): bool
    {
        return
            (float) $this->score >= 50
            && (float) $this->score < 85;
    }

    public function isWeak(): bool
    {
        return (float) $this->score < 50;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'strong' => 'Strong',
            'partial' => 'Partial',
            'weak' => 'Weak',
            default => ucfirst(
                (string) $this->status
            ),
        };
    }

    public function factorLabel(): string
    {
        return str(
            (string) $this->factor
        )
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}