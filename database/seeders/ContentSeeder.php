<?php

namespace Database\Seeders;

use App\Models\Guide;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $data = File::json(database_path('data/content.json'), JSON_THROW_ON_ERROR);

        foreach ($data['pages'] as $page) {
            Page::firstOrCreate(['key' => $page['key']], ['title' => $page['title']]);
        }

        foreach (['services' => Service::class, 'guides' => Guide::class, 'industries' => Industry::class] as $key => $model) {
            foreach ($data[$key] as $index => $row) {
                $model::firstOrCreate(['slug' => $row['slug']], ['title' => $row['title']] + ($model === Guide::class ? [] : ['sort' => $index + 1]));
            }
        }
    }
}
