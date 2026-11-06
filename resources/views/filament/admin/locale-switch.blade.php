@php
    $target = app()->getLocale() === 'ar' ? 'en' : 'ar';
@endphp
<form method="POST" action="{{ route('admin.locale.switch', $target) }}" class="hec-locale-switch">
    @csrf
    <button
        type="submit"
        lang="{{ $target }}"
        title="{{ __('admin.switch_language') }}"
        aria-label="{{ __('admin.switch_language') }}"
        class="fi-btn fi-size-sm fi-color-gray fi-btn-color-gray fi-outlined flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium ring-1 ring-gray-950/10 transition hover:bg-gray-50 dark:ring-white/20 dark:hover:bg-white/5"
    >
        <x-filament::icon icon="heroicon-o-language" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span>{{ __('admin.switch_language') }}</span>
    </button>
</form>
