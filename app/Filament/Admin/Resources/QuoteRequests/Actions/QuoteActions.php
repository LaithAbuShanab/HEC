<?php

namespace App\Filament\Admin\Resources\QuoteRequests\Actions;

use App\Domain\Access\Role;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Domain\Shared\Support\PhoneNumber;
use App\Models\QuoteRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

final class QuoteActions
{
    public static function changeStatus(): Action
    {
        return Action::make('changeStatus')
            ->label(__('admin.quotes.actions.change_status'))
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->authorize('update')
            ->fillForm(fn (QuoteRequest $record) => ['status' => $record->status->value])
            ->schema([
                Select::make('status')
                    ->label(__('admin.quotes.fields.status'))
                    ->options(QuoteStatus::options('ar'))
                    ->required(),
            ])
            ->action(function (QuoteRequest $record, array $data) {
                $record->update(['status' => $data['status']]);

                Notification::make()
                    ->success()
                    ->title(__('admin.quotes.actions.status_changed', ['status' => $record->status->label('ar')]))
                    ->send();
            });
    }

    public static function assign(): Action
    {
        return Action::make('assign')
            ->label(__('admin.quotes.actions.assign'))
            ->icon('heroicon-o-user-plus')
            ->authorize('update')
            ->fillForm(fn (QuoteRequest $record) => ['assigned_to' => $record->assigned_to])
            ->schema([
                Select::make('assigned_to')
                    ->label(__('admin.quotes.fields.assignee'))
                    ->options(fn () => self::assignableUsers())
                    ->searchable(),
            ])
            ->action(fn (QuoteRequest $record, array $data) => $record->update(['assigned_to' => $data['assigned_to']]));
    }

    public static function assignToMe(): Action
    {
        return Action::make('assignToMe')
            ->label(__('admin.quotes.actions.assign_to_me'))
            ->icon('heroicon-o-hand-raised')
            ->authorize('update')
            ->visible(fn (QuoteRequest $record) => $record->assigned_to !== auth()->id())
            ->action(fn (QuoteRequest $record) => $record->update(['assigned_to' => auth()->id()]));
    }

    public static function notes(): Action
    {
        return Action::make('notes')
            ->label(__('admin.quotes.actions.notes'))
            ->icon('heroicon-o-pencil-square')
            ->authorize('update')
            ->fillForm(fn (QuoteRequest $record) => ['internal_notes' => $record->internal_notes])
            ->schema([
                Textarea::make('internal_notes')->label(__('admin.quotes.fields.internal_notes'))->rows(6)->maxLength(5000),
            ])
            ->action(fn (QuoteRequest $record, array $data) => $record->update(['internal_notes' => $data['internal_notes']]));
    }

    public static function markSpam(): Action
    {
        return Action::make('markSpam')
            ->label(__('admin.quotes.actions.mark_spam'))
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->requiresConfirmation()
            ->authorize('update')
            ->visible(fn (QuoteRequest $record) => $record->status !== QuoteStatus::Spam)
            ->action(fn (QuoteRequest $record) => $record->update(['status' => QuoteStatus::Spam]));
    }

    public static function call(): Action
    {
        return Action::make('call')
            ->label(__('admin.quotes.actions.call'))
            ->icon('heroicon-o-phone')
            ->color('gray')
            ->url(fn (QuoteRequest $record) => PhoneNumber::telUrl($record->phone))
            ->visible(fn (QuoteRequest $record) => PhoneNumber::telUrl($record->phone) !== null);
    }

    public static function whatsapp(): Action
    {
        return Action::make('whatsapp')
            ->label(__('admin.quotes.actions.whatsapp'))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('success')
            ->url(fn (QuoteRequest $record) => PhoneNumber::whatsappUrl($record->phone, "HEC {$record->reference}"), shouldOpenInNewTab: true)
            ->visible(fn (QuoteRequest $record) => PhoneNumber::whatsappUrl($record->phone) !== null);
    }

    private static function assignableUsers(): array
    {
        return User::query()
            ->where('is_active', true)
            ->role([Role::Sales->value, Role::Admin->value])
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
