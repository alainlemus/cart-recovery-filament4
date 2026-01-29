<div class="language-selector">
    @php
        $locales = [
            'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
            'en' => ['name' => 'English', 'flag' => '🇺🇸'],
        ];
        $currentLocale = app()->getLocale();
    @endphp
    
    <div class="flex gap-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-1">
        @foreach($locales as $locale => $data)
            <a href="{{ route('locale.change', $locale) }}" 
               class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-all duration-200
                      {{ $currentLocale === $locale 
                         ? 'bg-indigo-600 text-white shadow-sm' 
                         : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <span class="text-lg">{{ $data['flag'] }}</span>
                <span class="hidden sm:inline">{{ $data['name'] }}</span>
            </a>
        @endforeach
    </div>
</div>

