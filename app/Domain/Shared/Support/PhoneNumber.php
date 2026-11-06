<?php

namespace App\Domain\Shared\Support;

use App\Domain\Catalog\Support\SearchNormalizer;

final class PhoneNumber
{
    public const COUNTRY_CODE = '962';

    public static function international(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', SearchNormalizer::normalize($phone)) ?? '';

        $digits = match (true) {
            str_starts_with($digits, '00') => substr($digits, 2),
            str_starts_with($digits, self::COUNTRY_CODE) => $digits,
            str_starts_with($digits, '0') && strlen($digits) === 10 => self::COUNTRY_CODE.substr($digits, 1),
            strlen($digits) === 9 && str_starts_with($digits, '7') => self::COUNTRY_CODE.$digits,
            default => $digits,
        };

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    public static function whatsappUrl(string $phone, ?string $message = null): ?string
    {
        $number = self::international($phone);

        if ($number === null) {
            return null;
        }

        return "https://wa.me/{$number}".($message ? '?text='.rawurlencode($message) : '');
    }

    public static function telUrl(string $phone): ?string
    {
        $number = self::international($phone);

        return $number === null ? null : "tel:+{$number}";
    }
}
