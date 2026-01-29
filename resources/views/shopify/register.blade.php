<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.shopify.register.title') }} {{ $product->name }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-4">
        {{-- Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="p-6 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-center">
                <h1 class="text-2xl font-bold">{{ __('messages.shopify.register.title') }}</h1>
                <p class="text-sm mt-2 opacity-90">{{ __('messages.shopify.register.subtitle') }} {{ $product->name }}</p>
                <p class="text-lg font-semibold mt-1">${{ number_format($product->price, 2) }}{{ __('messages.shopify.plans.per_month') }}</p>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mx-6 mt-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mx-6 mt-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mx-6 mt-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Registration Form --}}
            <form method="POST" action="{{ route('shopify.register.store', $product) }}" class="p-6 space-y-4">
                @csrf

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('messages.shopify.register.full_name') }}
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                        placeholder="John Doe"
                    >
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('messages.shopify.register.email_address') }}
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                        placeholder="john@example.com"
                    >
                </div>

                {{-- Shopify Domain --}}
                <div>
                    <label for="shopify_domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('messages.shopify.register.shopify_domain') }}
                    </label>
                    <input
                        type="text"
                        id="shopify_domain"
                        name="shopify_domain"
                        value="{{ old('shopify_domain') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                        placeholder="{{ __('messages.shopify.register.shopify_domain_placeholder') }}"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('messages.shopify.register.shopify_domain_help') }}
                    </p>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('messages.shopify.register.password') }}
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                        placeholder="••••••••"
                    >
                </div>

                {{-- Password Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('messages.shopify.register.confirm_password') }}
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                        placeholder="••••••••"
                    >
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors duration-300"
                >
                    {{ __('messages.shopify.register.create_account') }}
                </button>
            </form>

            {{-- Footer --}}
            <div class="px-6 pb-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.shopify.register.already_account') }}
                    <a href="{{ url('/admin-shop/login') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium">
                        {{ __('messages.shopify.plans.login_here') }}
                    </a>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    <a href="{{ route('shopify.billing.plans') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                        ← {{ __('messages.shopify.register.back_to_plans') }}
                    </a>
                </p>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">{{ __('messages.shopify.register.what_happens_next') }}</h3>
            <ol class="text-sm text-blue-800 dark:text-blue-300 space-y-1 list-decimal list-inside">
                <li>{{ __('messages.shopify.register.steps.create_account') }}</li>
                <li>{{ __('messages.shopify.register.steps.connect_store') }}</li>
                <li>{{ __('messages.shopify.register.steps.approve_charge') }}</li>
                <li>{{ __('messages.shopify.register.steps.start_recovering') }}</li>
            </ol>
        </div>
    </div>
</body>
</html>
