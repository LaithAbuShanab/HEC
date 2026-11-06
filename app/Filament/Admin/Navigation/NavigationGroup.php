<?php

namespace App\Filament\Admin\Navigation;

use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case Sales = 'sales';
    case Catalog = 'catalog';
    case Content = 'content';
    case Seo = 'seo';
    case System = 'system';

    public static function registrations(): array
    {
        $groups = [];

        foreach (self::cases() as $case) {
            $groups[$case->name] = FilamentNavigationGroup::make()->label(fn (): string => $case->getLabel());
        }

        return $groups;
    }

    public function getLabel(): string
    {
        return __("admin.navigation.{$this->value}");
    }
}
