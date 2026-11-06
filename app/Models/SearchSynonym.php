<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SearchSynonym extends Model
{
    protected $fillable = ['terms'];

    protected function casts(): array
    {
        return ['terms' => 'array'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('search_synonym_groups'));
        static::deleted(fn () => Cache::forget('search_synonym_groups'));
    }

    public static function groups(): array
    {
        return Cache::rememberForever('search_synonym_groups', fn () => self::query()->pluck('terms')->all());
    }
}
