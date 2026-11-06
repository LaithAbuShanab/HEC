<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Domain\Access\Role;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->columns(2)->schema([
                TextInput::make('name')->label(__('admin.users.fields.name'))->required()->maxLength(120),
                TextInput::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->email()
                    ->required()
                    ->maxLength(160)
                    ->unique(ignoreRecord: true)
                    ->extraInputAttributes(['dir' => 'ltr']),
                TextInput::make('password')
                    ->label(__('admin.users.fields.password'))
                    ->password()
                    ->revealable()
                    ->rule(Password::min(12)->letters()->mixedCase()->numbers())
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText(fn (string $operation) => $operation === 'edit' ? __('admin.users.fields.password_help') : null)
                    ->autocomplete('new-password'),
                Select::make('role')
                    ->label(__('admin.users.fields.role'))
                    ->options(Role::options('ar'))
                    ->required()
                    ->rule(fn (?User $record) => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                        if ($record !== null && $record->is(auth()->user()) && $record->isAdmin() && $value !== Role::Admin->value) {
                            $fail(__('admin.users.errors.self_demote'));
                        }
                    }),
                Toggle::make('is_active')
                    ->label(__('admin.users.fields.is_active'))
                    ->helperText(__('admin.users.fields.is_active_help'))
                    ->default(true)
                    ->rule(fn (?User $record) => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                        if ($record !== null && $record->is(auth()->user()) && ! $value) {
                            $fail(__('admin.users.errors.self_deactivate'));
                        }
                    }),
            ]),
        ]);
    }
}
