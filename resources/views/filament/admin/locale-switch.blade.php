@php
    $target = app()->getLocale() === 'ar' ? 'en' : 'ar';
@endphp
<form method="POST" action="{{ route('admin.locale.switch', $target) }}" style="display: inline-flex">
    @csrf
    <x-filament::button
        type="submit"
        color="gray"
        outlined
        size="sm"
        icon="heroicon-o-language"
        :tooltip="__('admin.switch_language')"
        :lang="$target"
    >
        {{ __('admin.switch_language') }}
    </x-filament::button>
</form>
