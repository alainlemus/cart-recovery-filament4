<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-header flex flex-col gap-3 px-6 py-4">
        <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                {{ app()->getLocale() === 'es' ? 'Preferencia de Idioma' : 'Language Preference' }}
            </h3>
            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                {{ app()->getLocale() === 'es' ? 'Selecciona tu idioma preferido para la interfaz' : 'Select your preferred interface language' }}
            </p>
        </div>
    </div>

    <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
        <div class="fi-section-content px-6 py-4">
            <form action="{{ route('locale.change', ['locale' => 'LOCALE']) }}" method="GET" id="locale-form">
                <select 
                    name="locale" 
                    onchange="this.form.action = this.form.action.replace('LOCALE', this.value); this.form.submit();"
                    class="fi-select-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    @foreach(config('app.available_locales', ['es', 'en']) as $locale)
                        <option value="{{ $locale }}" {{ app()->getLocale() === $locale ? 'selected' : '' }}>
                            {{ config('app.locale_names')[$locale] ?? strtoupper($locale) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>
