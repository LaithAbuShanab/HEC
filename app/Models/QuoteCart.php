<?php

namespace App\Models;

use App\Domain\Quotes\QuoteCartManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuoteCart extends Model
{
    use MassPrunable;

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

    public function prunable(): Builder
    {
        return static::where('last_activity_at', '<', now()->subDays(QuoteCartManager::LIFETIME_DAYS));
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteCartItem::class)->orderBy('sort');
    }
}
