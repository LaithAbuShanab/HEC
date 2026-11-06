<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Domain\Access\Role;
use App\Filament\Admin\Support\Display;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label(__('admin.users.fields.name'))->searchable()->sortable(),
                TextColumn::make('email')->label(__('admin.users.fields.email'))->searchable()->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                TextColumn::make('role')
                    ->label(__('admin.users.fields.role'))
                    ->badge()
                    ->state(fn (User $record) => self::roleOf($record)?->label())
                    ->color(fn (User $record) => match (self::roleOf($record)) {
                        Role::Admin => 'danger',
                        Role::Editor => 'info',
                        Role::Sales => 'success',
                        null => 'gray',
                    }),
                IconColumn::make('is_active')->label(__('admin.users.fields.is_active'))->boolean(),
                IconColumn::make('mfa')
                    ->label(__('admin.users.fields.mfa'))
                    ->state(fn (User $record) => filled($record->getAppAuthenticationSecret()))
                    ->boolean(),
                TextColumn::make('last_login_at')->label(__('admin.users.fields.last_login_at'))->since()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('admin.users.fields.role'))
                    ->options(Role::options())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('roles', fn (Builder $roles) => $roles->where('name', $data['value']))
                        : $query),
                TernaryFilter::make('is_active')->label(__('admin.users.fields.is_active')),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    Action::make('resetMfa')
                        ->label(__('admin.users.actions.reset_mfa'))
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription(__('admin.users.actions.reset_mfa_help'))
                        ->visible(fn (User $record) => filled($record->getAppAuthenticationSecret()))
                        ->action(function (User $record) {
                            $record->saveAppAuthenticationSecret(null);
                            $record->saveAppAuthenticationRecoveryCodes(null);

                            Notification::make()->success()->title(__('admin.users.actions.reset_mfa_done'))->send();
                        }),
                ]),
            ]);
    }

    public static function roleOf(User $user): ?Role
    {
        foreach (Role::cases() as $role) {
            if ($user->hasRole($role->value)) {
                return $role;
            }
        }

        return null;
    }
}
