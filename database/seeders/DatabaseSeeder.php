<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SaleUnitSeeder::class,
            SpecAttributeSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
