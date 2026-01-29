@php
    $locales = [
        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
    ];
    $currentLocale = app()->getLocale();
@endphp

<div class="fi-global-search-field flex items-center gap-1">
    @foreach($locales as $locale => $data)
        <a href="{{ route('locale.change', $locale) }}" 
           title="{{ $data['name'] }}"
           class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200
                  {{ $currentLocale === $locale 
                     ? 'bg-primary-600 text-white ring-2 ring-primary-600/20' 
                     : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5' }}">
            <span class="text-base">{{ $data['flag'] }}</span>
            <span class="hidden lg:inline text-xs">{{ $data['name'] }}</span>
        </a>
    @endforeach
</div>
