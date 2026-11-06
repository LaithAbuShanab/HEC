<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    public const STATUS_CODES = [301, 302, 410];

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
}
