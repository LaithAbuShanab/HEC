<?php

namespace App\Filament\Admin\Resources\Redirects;

use App\Domain\Seo\RedirectRules;
use App\Filament\Admin\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use BackedEnum;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnRight;

    protected static ?string $recordTitleAttribute = 'from_path';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('admin.navigation.seo');
    }

    public static function getModelLabel(): string
    {
        return __('admin.redirects.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.redirects.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')
                ->label(__('admin.redirects.fields.from_path'))
                ->helperText(__('admin.redirects.fields.from_path_help'))
                ->required()
                ->maxLength(255)
                ->extraInputAttributes(['dir' => 'ltr'])
                ->rule(fn (?Redirect $record) => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                    $error = RedirectRules::fromPathError((string) $value, $record?->id);

                    if ($error !== null) {
                        $fail(__("admin.redirects.errors.{$error}"));
                    }
                }),
            Select::make('status_code')
                ->label(__('admin.redirects.fields.status_code'))
                ->options(__('admin.redirects.status_codes'))
                ->default(301)
                ->required()
                ->live(),
            TextInput::make('to_path')
                ->label(__('admin.redirects.fields.to_path'))
                ->helperText(__('admin.redirects.fields.to_path_help'))
                ->required(fn (Get $get) => (int) $get('status_code') !== 410)
                ->hidden(fn (Get $get) => (int) $get('status_code') === 410)
                ->maxLength(500)
                ->extraInputAttributes(['dir' => 'ltr'])
                ->rule(fn (Get $get, ?Redirect $record) => function (string $attribute, mixed $value, Closure $fail) use ($get, $record) {
                    if (blank($value)) {
                        return;
                    }

                    $error = RedirectRules::toPathError((string) $value, (string) $get('from_path'), $record?->id);

                    if ($error !== null) {
                        $fail(__("admin.redirects.errors.{$error}"));
                    }
                }),
            Toggle::make('is_active')->label(__('admin.redirects.fields.is_active'))->default(true),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('from_path')->label(__('admin.redirects.fields.from_path'))->searchable()->extraAttributes(['dir' => 'ltr']),
                TextColumn::make('to_path')->label(__('admin.redirects.fields.to_path'))->searchable()->placeholder('—')->extraAttributes(['dir' => 'ltr']),
                TextColumn::make('status_code')
                    ->label(__('admin.redirects.fields.status_code'))
                    ->badge()
                    ->formatStateUsing(fn (int $state) => __("admin.redirects.status_codes.{$state}"))
                    ->color(fn (int $state) => match ($state) {
                        301 => 'success',
                        302 => 'warning',
                        default => 'danger',
                    }),
                ToggleColumn::make('is_active')->label(__('admin.redirects.fields.is_active')),
                TextColumn::make('hits')->label(__('admin.redirects.fields.hits'))->numeric()->sortable(),
                TextColumn::make('last_hit_at')->label(__('admin.redirects.fields.last_hit_at'))->since()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status_code')->label(__('admin.redirects.fields.status_code'))->options(__('admin.redirects.status_codes')),
            ])
            ->recordActions([
                EditAction::make()->mutateDataUsing(fn (array $data) => self::clearTargetWhenGone($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function clearTargetWhenGone(array $data): array
    {
        if ((int) ($data['status_code'] ?? 301) === 410) {
            $data['to_path'] = null;
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRedirects::route('/'),
        ];
    }
}
