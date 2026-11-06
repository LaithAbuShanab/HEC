<?php

namespace Database\Seeders;

use App\Models\SpecAttribute;
use Illuminate\Database\Seeder;

class SpecAttributeSeeder extends Seeder
{
    public const ATTRIBUTES = [
        ['key' => 'finish', 'name' => ['ar' => 'التشطيب أو الطلاء', 'en' => 'Finish / coating'], 'unit' => null, 'type' => 'text', 'is_filterable' => true],
        ['key' => 'section_shape', 'name' => ['ar' => 'شكل المقطع', 'en' => 'Section shape'], 'unit' => null, 'type' => 'text', 'is_filterable' => true],
        ['key' => 'capacity_ton', 'name' => ['ar' => 'القدرة', 'en' => 'Capacity'], 'unit' => 'ton', 'type' => 'number', 'is_filterable' => true],
        ['key' => 'power_kw', 'name' => ['ar' => 'القدرة الكهربائية', 'en' => 'Power'], 'unit' => 'kW', 'type' => 'number', 'is_filterable' => true],
        ['key' => 'pressure_bar', 'name' => ['ar' => 'الضغط', 'en' => 'Pressure'], 'unit' => 'bar', 'type' => 'number', 'is_filterable' => false],
        ['key' => 'flow', 'name' => ['ar' => 'التدفق', 'en' => 'Flow'], 'unit' => 'L/min', 'type' => 'number', 'is_filterable' => false],
        ['key' => 'gear_ratio', 'name' => ['ar' => 'نسبة التخفيض', 'en' => 'Gear ratio'], 'unit' => null, 'type' => 'text', 'is_filterable' => false],
        ['key' => 'max_rpm', 'name' => ['ar' => 'أقصى سرعة دوران', 'en' => 'Max speed'], 'unit' => 'rpm', 'type' => 'number', 'is_filterable' => false],
    ];

    public function run(): void
    {
        foreach (self::ATTRIBUTES as $sort => $attribute) {
            SpecAttribute::updateOrCreate(['key' => $attribute['key']], $attribute + ['sort' => $sort + 1]);
        }
    }
}
