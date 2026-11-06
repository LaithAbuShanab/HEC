<?php

namespace App\Filament\Admin\Resources;

use App\Models\Contracts\Translatable;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

abstract class TranslatableResource extends Resource
{
    protected static string $langKey;

    protected static string $navigationGroupKey = 'content';

    protected static string $titleAttribute = 'title';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('admin.navigation.'.static::$navigationGroupKey);
    }

    public static function getModelLabel(): string
    {
        return __('admin.'.static::$langKey.'.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.'.static::$langKey.'.plural');
    }

    public static function getRecordTitle(?Model $record): string
    {
        return $record instanceof Translatable ? (string) $record->getTranslation(static::$titleAttribute, 'ar') : '';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
