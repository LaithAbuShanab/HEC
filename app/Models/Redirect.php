<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Redirect extends Model
{
    public const STATUS_CODES = [301, 302, 410];

    public const CACHE_KEY = 'redirects.map';

    protected $fillable = ['from_path', 'to_path', 'status_code', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_hit_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect) {
            $redirect->from_path = self::normalizePath($redirect->from_path);
        });

        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function normalizePath(string $path): string
    {
        $path = '/'.trim((string) parse_url($path, PHP_URL_PATH), '/');

        return mb_strtolower(rawurldecode($path));
    }

    public function isGone(): bool
    {
        return $this->status_code === 410;
    }

    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => self::query()
            ->where('is_active', true)
            ->get(['id', 'from_path', 'to_path', 'status_code'])
            ->mapWithKeys(fn (Redirect $redirect) => [$redirect->from_path => [
                'id' => $redirect->id,
                'to' => $redirect->to_path,
                'status' => $redirect->status_code,
            ]])
            ->all());
    }

    public static function recordHit(int $id): void
    {
        self::query()->whereKey($id)->update([
            'hits' => DB::raw('hits + 1'),
            'last_hit_at' => now(),
        ]);
    }
}
