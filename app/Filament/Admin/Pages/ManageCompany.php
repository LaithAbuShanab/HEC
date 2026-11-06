<?php

namespace App\Filament\Admin\Pages;

use App\Domain\Shared\Support\PhoneNumber;
use App\Filament\Admin\Navigation\NavigationGroup;
use App\Filament\Admin\Support\Bilingual;
use App\Models\User;
use App\Settings\CompanySettings;
use BackedEnum;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageCompany extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string $settings = CompanySettings::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return NavigationGroup::System;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.company.title');
    }

    public function getTitle(): string
    {
        return __('admin.company.title');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->is_active && $user->isAdmin();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                $this->identityTab(),
                $this->contactTab(),
                $this->locationTab(),
                $this->hoursTab(),
                $this->socialTab(),
            ]),
        ]);
    }

    private function identityTab(): Tab
    {
        return Tab::make(__('admin.company.tabs.identity'))->schema([
            Bilingual::fields('name', __('admin.company.fields.name'), fn (string $path) => TextInput::make($path)->maxLength(120), required: true),
            Bilingual::fields('legal_name', __('admin.company.fields.legal_name'), fn (string $path) => TextInput::make($path)->maxLength(160)),
            $this->confirmation('legal_name_confirmed'),
            Bilingual::fields('tagline', __('admin.company.fields.tagline'), fn (string $path) => TextInput::make($path)->maxLength(120)),
            Toggle::make('tagline_approved')->label(__('admin.company.fields.tagline_approved'))->helperText(__('admin.company.confirmed_help')),
        ]);
    }

    private function contactTab(): Tab
    {
        return Tab::make(__('admin.company.tabs.contact'))->schema([
            Grid::make(2)->schema([
                $this->phoneInput('phone'),
                $this->confirmation('phone_confirmed'),
                $this->phoneInput('whatsapp'),
                $this->confirmation('whatsapp_confirmed'),
                TextInput::make('public_email')->label(__('admin.company.fields.public_email'))->email()->maxLength(160)->extraInputAttributes(['dir' => 'ltr']),
                $this->confirmation('public_email_confirmed'),
            ]),
            TextInput::make('quote_notification_email')
                ->label(__('admin.company.fields.quote_notification_email'))
                ->helperText(__('admin.company.fields.quote_notification_email_help'))
                ->email()
                ->required()
                ->maxLength(160)
                ->extraInputAttributes(['dir' => 'ltr']),
        ]);
    }

    private function locationTab(): Tab
    {
        return Tab::make(__('admin.company.tabs.location'))->schema([
            Bilingual::fields('address', __('admin.company.fields.address'), fn (string $path) => TextInput::make($path)->maxLength(200)),
            TextInput::make('maps_url')
                ->label(__('admin.company.fields.maps_url'))
                ->url()
                ->startsWith(['https://'])
                ->maxLength(500)
                ->extraInputAttributes(['dir' => 'ltr']),
            Grid::make(2)->schema([
                TextInput::make('latitude')->label(__('admin.company.fields.latitude'))->numeric()->minValue(29)->maxValue(33.5),
                TextInput::make('longitude')->label(__('admin.company.fields.longitude'))->numeric()->minValue(34.8)->maxValue(39.5),
            ]),
            Toggle::make('location_confirmed')->label(__('admin.company.fields.location_confirmed'))->helperText(__('admin.company.confirmed_help')),
            Bilingual::fields('service_areas', __('admin.company.fields.service_areas'), fn (string $path) => Textarea::make($path)
                ->rows(2)
                ->maxLength(400)
                ->helperText(__('admin.company.fields.service_areas_help'))),
        ]);
    }

    private function hoursTab(): Tab
    {
        return Tab::make(__('admin.company.tabs.hours'))->schema([
            Repeater::make('working_hours')
                ->label(__('admin.company.fields.working_hours'))
                ->schema([
                    CheckboxList::make('days')
                        ->label(__('admin.company.fields.days'))
                        ->options(__('admin.company.weekdays'))
                        ->columns(4)
                        ->required()
                        ->columnSpanFull(),
                    TimePicker::make('opens')->label(__('admin.company.fields.opens'))->seconds(false)->required(),
                    TimePicker::make('closes')->label(__('admin.company.fields.closes'))->seconds(false)->required()->after('opens'),
                ])
                ->columns(2)
                ->defaultItems(0),
            Bilingual::fields('working_hours_note', __('admin.company.fields.working_hours_note'), fn (string $path) => TextInput::make($path)->maxLength(200)),
            Toggle::make('working_hours_confirmed')->label(__('admin.company.fields.working_hours_confirmed'))->helperText(__('admin.company.confirmed_help')),
        ]);
    }

    private function socialTab(): Tab
    {
        $fields = [];

        foreach (__('admin.company.social') as $network => $label) {
            $fields[] = TextInput::make("social.{$network}")
                ->label($label)
                ->url()
                ->startsWith(['https://'])
                ->maxLength(300)
                ->extraInputAttributes(['dir' => 'ltr']);
        }

        return Tab::make(__('admin.company.tabs.social'))->schema([Grid::make(2)->schema($fields)]);
    }

    private function phoneInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->label(__("admin.company.fields.{$name}"))
            ->tel()
            ->maxLength(20)
            ->extraInputAttributes(['dir' => 'ltr'])
            ->rule(fn () => function (string $attribute, mixed $value, Closure $fail) {
                if (filled($value) && PhoneNumber::international((string) $value) === null) {
                    $fail(__('admin.company.fields.phone_invalid'));
                }
            });
    }

    private function confirmation(string $name): Toggle
    {
        return Toggle::make($name)
            ->label(__('admin.company.confirmed'))
            ->helperText(__('admin.company.confirmed_help'))
            ->inline(false);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['working_hours'] = array_values($data['working_hours'] ?? []);
        $data['social'] = array_filter($data['social'] ?? [], fn ($url) => filled($url));

        return $data;
    }
}
