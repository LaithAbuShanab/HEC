<?php

namespace App\Filament\Admin\Navigation;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case Sales = 'sales';
    case Catalog = 'catalog';
    case Content = 'content';
    case Seo = 'seo';
    case System = 'system';

    public function getLabel(): string
    {
        return __("admin.navigation.{$this->value}");
    }
}
