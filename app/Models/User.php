<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function buyerRequests(): HasMany
    {
        return $this->hasMany(
            BuyerRequest::class
        );
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(
            SourceVerification::class,
            'verified_by'
        );
    }

    public function adminOverrides(): HasMany
    {
        return $this->hasMany(
            AdminOverride::class,
            'admin_id'
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }
}