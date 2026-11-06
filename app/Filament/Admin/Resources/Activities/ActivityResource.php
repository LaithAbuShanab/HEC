<?php

namespace App\Filament\Admin\Resources\Activities;

use App\Filament\Admin\Navigation\NavigationGroup;
use App\Filament\Admin\Resources\Activities\Pages\ListActivities;
use App\Models\Category;
use App\Models\Contracts\Translatable;
use App\Models\Faq;
use App\Models\Guide;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkItem;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 3;

    public const SUBJECTS = [
        Product::class => 'products',
        Category::class => 'categories',
        Service::class => 'services',
        Guide::class => 'guides',
        Industry::class => 'industries',
        WorkItem::class => 'work_items',
        Faq::class => 'faqs',
        Page::class => 'pages',
        QuoteRequest::class => 'quotes',
        User::class => 'users',
    ];

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return NavigationGroup::System;
    }

    public static function getModelLabel(): string
    {
        return __('admin.activity.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.activity.plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('changes_table')
                ->label(__('admin.activity.fields.changes'))
                ->state(fn (Activity $record) => self::changesTable($record))
                ->html()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['causer', 'subject']))
            ->columns([
                TextColumn::make('created_at')->label(__('admin.activity.fields.created_at'))->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('causer.name')->label(__('admin.activity.fields.causer'))->placeholder(__('admin.activity.fields.system')),
                TextColumn::make('event')
                    ->label(__('admin.activity.fields.event'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? __("admin.activity.events.{$state}") : '—')
                    ->color(fn (?string $state) => match ($state) {
                        'created' => 'success',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('subject_type')
                    ->label(__('admin.activity.fields.subject'))
                    ->formatStateUsing(fn (?string $state) => isset(self::SUBJECTS[$state]) ? __('admin.'.self::SUBJECTS[$state].'.label') : class_basename((string) $state))
                    ->description(fn (Activity $record) => self::subjectTitle($record)),
                TextColumn::make('changed_fields')
                    ->label(__('admin.activity.fields.changes'))
                    ->state(fn (Activity $record) => implode('، ', array_map(fn (int|string $field) => self::fieldLabel($record, (string) $field), array_keys(self::changes($record)['attributes']))))
                    ->limit(60)
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label(__('admin.activity.fields.subject'))
                    ->options(collect(self::SUBJECTS)->map(fn (string $key) => __("admin.{$key}.label"))->all()),
                SelectFilter::make('event')
                    ->label(__('admin.activity.fields.event'))
                    ->options(__('admin.activity.events')),
                SelectFilter::make('causer_id')
                    ->label(__('admin.activity.fields.causer'))
                    ->options(fn () => User::orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function changes(Activity $activity): array
    {
        $changes = $activity->getAttribute('attribute_changes');
        $changes = is_array($changes) ? $changes : (json_decode((string) $changes, true) ?: []);

        return [
            'old' => (array) ($changes['old'] ?? []),
            'attributes' => (array) ($changes['attributes'] ?? []),
        ];
    }

    private static function subjectTitle(Activity $activity): ?string
    {
        $subject = $activity->subject;

        return match (true) {
            $subject instanceof QuoteRequest => $subject->reference,
            $subject instanceof User => $subject->name,
            $subject instanceof Translatable => self::translatableTitle($subject),
            default => $activity->subject_id ? '#'.$activity->subject_id : null,
        };
    }

    public static function fieldLabel(Activity $activity, string $field): string
    {
        $section = self::SUBJECTS[$activity->subject_type] ?? null;

        $candidates = array_filter([
            $section ? "admin.{$section}.fields.{$field}" : null,
            "admin.content.fields.{$field}",
            "admin.products.fields.{$field}",
            "admin.common.{$field}",
            "admin.quotes.fields.{$field}",
        ]);

        foreach ($candidates as $key) {
            if (Lang::has($key)) {
                return __($key);
            }
        }

        return str_replace('_', ' ', $field);
    }

    private static function translatableTitle(Translatable $subject): ?string
    {
        $attribute = collect(['name', 'title', 'question'])->first(fn (string $key) => in_array($key, $subject->getTranslatableAttributes(), true));

        return $attribute === null ? null : (string) $subject->getTranslation($attribute, 'ar');
    }

    private static function changesTable(Activity $activity): HtmlString
    {
        $changes = self::changes($activity);
        $rows = '';

        foreach ($changes['attributes'] as $field => $new) {
            $rows .= sprintf(
                '<tr class="border-t"><td class="p-2 font-medium">%s</td><td class="p-2 text-danger-600">%s</td><td class="p-2 text-success-600">%s</td></tr>',
                e(self::fieldLabel($activity, (string) $field)),
                e(self::display($changes['old'][$field] ?? null)),
                e(self::display($new)),
            );
        }

        return new HtmlString(sprintf(
            '<table class="w-full text-sm"><thead><tr><th class="p-2 text-start">%s</th><th class="p-2 text-start">%s</th><th class="p-2 text-start">%s</th></tr></thead><tbody>%s</tbody></table>',
            e(__('admin.activity.fields.field')),
            e(__('admin.activity.fields.old')),
            e(__('admin.activity.fields.new')),
            $rows,
        ));
    }

    private static function display(mixed $value): string
    {
        return match (true) {
            $value === null => '—',
            is_bool($value) => $value ? '✓' : '✗',
            is_array($value) => (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => (string) $value,
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
