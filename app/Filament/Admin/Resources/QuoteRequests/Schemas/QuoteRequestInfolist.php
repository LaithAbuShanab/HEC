<?php

namespace App\Filament\Admin\Resources\QuoteRequests\Schemas;

use App\Domain\Quotes\Enums\FulfillmentMethod;
use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Filament\Admin\Support\Display;
use App\Models\QuoteAttachment;
use App\Models\QuoteRequest;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class QuoteRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Section::make(__('admin.quotes.sections.customer'))->columnSpan(2)->columns(3)->schema([
                    TextEntry::make('name')->label(__('admin.quotes.fields.name')),
                    TextEntry::make('phone')->label(__('admin.quotes.fields.phone'))->copyable()->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                    TextEntry::make('city')->label(__('admin.quotes.fields.city')),
                    TextEntry::make('company')->label(__('admin.quotes.fields.company'))->placeholder('—'),
                    TextEntry::make('email')->label(__('admin.quotes.fields.email'))->placeholder('—')->copyable()->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                    TextEntry::make('locale')->label(__('admin.quotes.fields.locale'))
                        ->formatStateUsing(fn (string $state) => __("admin.locales.{$state}")),
                ]),
                Section::make(__('admin.quotes.sections.handling'))->columnSpan(1)->schema([
                    TextEntry::make('reference')->label(__('admin.quotes.fields.reference'))->copyable()->weight('bold'),
                    TextEntry::make('status')->label(__('admin.quotes.fields.status'))->badge()
                        ->formatStateUsing(fn (QuoteStatus $state) => $state->label())
                        ->color(fn (QuoteStatus $state) => self::statusColor($state)),
                    TextEntry::make('assignee.name')->label(__('admin.quotes.fields.assignee'))->placeholder('—'),
                    TextEntry::make('created_at')->label(__('admin.quotes.fields.created_at'))->dateTime('Y-m-d H:i'),
                    TextEntry::make('notification_status')->label(__('admin.quotes.fields.notification_status'))->badge()
                        ->formatStateUsing(fn (NotificationStatus $state) => $state->label())
                        ->color(fn (NotificationStatus $state) => match ($state) {
                            NotificationStatus::Pending => 'warning',
                            NotificationStatus::Sent => 'success',
                            NotificationStatus::Failed => 'danger',
                        }),
                    TextEntry::make('last_notification_error')->label(__('admin.quotes.fields.last_notification_error'))
                        ->visible(fn (QuoteRequest $record) => $record->notification_status === NotificationStatus::Failed)
                        ->color('danger'),
                ]),
            ]),
            Section::make(__('admin.quotes.sections.request'))->columnSpanFull()->columns(3)->schema([
                TextEntry::make('fulfillment')->label(__('admin.quotes.fields.fulfillment'))
                    ->formatStateUsing(fn (FulfillmentMethod $state) => $state->label()),
                TextEntry::make('needed_by')->label(__('admin.quotes.fields.needed_by'))->date('Y-m-d')
                    ->placeholder('—')->helperText(__('admin.quotes.fields.needed_by_help')),
                TextEntry::make('source')->label(__('admin.quotes.fields.source'))
                    ->state(fn (QuoteRequest $record) => collect($record->source ?? [])->filter()->map(fn ($value, $key) => Display::sourceLine((string) $key, (string) $value))->values()->all())
                    ->listWithLineBreaks()
                    ->placeholder('—'),
                TextEntry::make('notes')->label(__('admin.quotes.fields.notes'))->placeholder('—')->columnSpanFull(),
            ]),
            Section::make(__('admin.quotes.sections.items'))->columnSpanFull()->schema([
                RepeatableEntry::make('items')->hiddenLabel()->table([
                    TableColumn::make(__('admin.quotes.fields.product')),
                    TableColumn::make(__('admin.quotes.fields.variant')),
                    TableColumn::make(__('admin.quotes.fields.thickness')),
                    TableColumn::make(__('admin.quotes.fields.dimensions')),
                    TableColumn::make(__('admin.quotes.fields.quantity')),
                    TableColumn::make(__('admin.quotes.fields.unit')),
                    TableColumn::make(__('admin.quotes.fields.item_notes')),
                ])->schema([
                    TextEntry::make('product_name')
                        ->url(fn ($record) => $record->product_url)
                        ->openUrlInNewTab()
                        ->badge(fn ($record) => (bool) $record->is_custom)
                        ->color(fn ($record) => $record->is_custom ? 'warning' : null)
                        ->tooltip(fn ($record) => $record->is_custom ? __('admin.quotes.fields.custom_item') : null),
                    TextEntry::make('variant_label')->placeholder('—')->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                    TextEntry::make('thickness')->placeholder('—')->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                    TextEntry::make('dimensions')->placeholder('—')->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                    TextEntry::make('quantity')->placeholder('—')->formatStateUsing(fn (mixed $state) => Display::quantity($state)),
                    TextEntry::make('unit')->placeholder('—')->formatStateUsing(fn (?string $state) => Display::unit($state)),
                    TextEntry::make('notes')->placeholder('—'),
                ]),
            ]),
            Section::make(__('admin.quotes.sections.attachments'))->columnSpanFull()->schema([
                TextEntry::make('attachment_links')
                    ->hiddenLabel()
                    ->state(fn (QuoteRequest $record) => self::attachmentLinks($record))
                    ->html(),
            ]),
            Section::make(__('admin.quotes.fields.internal_notes'))->columnSpanFull()->schema([
                TextEntry::make('internal_notes')->hiddenLabel()->placeholder('—'),
            ]),
        ]);
    }

    public static function statusColor(QuoteStatus $status): string
    {
        return match ($status) {
            QuoteStatus::New => 'warning',
            QuoteStatus::Reviewing => 'info',
            QuoteStatus::Contacted => 'primary',
            QuoteStatus::Quoted => 'success',
            QuoteStatus::Closed => 'gray',
            QuoteStatus::Spam => 'danger',
        };
    }

    private static function attachmentLinks(QuoteRequest $record): HtmlString
    {
        $attachments = QuoteAttachment::where('quote_request_id', $record->id)->get();

        if ($attachments->isEmpty()) {
            return new HtmlString(e(__('admin.quotes.no_attachments')));
        }

        return new HtmlString($attachments->map(fn (QuoteAttachment $attachment) => sprintf(
            '<a href="%s" class="text-primary-600 underline" rel="nofollow">%s</a> <span class="text-gray-500">(%s)</span>',
            e(route('admin.quote-attachments.download', $attachment)),
            e($attachment->original_name),
            e(Number::fileSize($attachment->size_bytes)),
        ))->implode('<br>'));
    }
}
