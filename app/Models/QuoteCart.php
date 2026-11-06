<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuoteCart extends Model
{
    protected $fillable = ['token', 'locale', 'last_activity_at'];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return ['last_activity_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (QuoteCart $cart) {
            $cart->token ??= Str::random(64);
            $cart->last_activity_at ??= now();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteCartItem::class)->orderBy('sort');
    }
}
