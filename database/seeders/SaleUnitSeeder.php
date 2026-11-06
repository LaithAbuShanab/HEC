<?php

namespace Database\Seeders;

use App\Models\SaleUnit;
use Illuminate\Database\Seeder;

class SaleUnitSeeder extends Seeder
{
    public const UNITS = [
        'piece' => ['ar' => 'قطعة', 'en' => 'Piece'],
        'meter' => ['ar' => 'متر', 'en' => 'Meter'],
        'sheet' => ['ar' => 'لوح', 'en' => 'Sheet'],
        'kg' => ['ar' => 'كغ', 'en' => 'kg'],
        'ton' => ['ar' => 'طن', 'en' => 'Ton'],
        'coil' => ['ar' => 'لفة', 'en' => 'Coil'],
        'bundle' => ['ar' => 'ربطة', 'en' => 'Bundle'],
        'carton' => ['ar' => 'كرتونة', 'en' => 'Carton'],
    ];

    public function run(): void
    {
        $sort = 0;
        foreach (self::UNITS as $key => $name) {
            SaleUnit::updateOrCreate(['key' => $key], ['name' => $name, 'sort' => ++$sort]);
        }
    }
}
