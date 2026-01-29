<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.shopify.plans.title') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('messages.shopify.plans.title') }}
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">
                {{ __('messages.shopify.plans.subtitle') }}
            </p>

            @if($shop)
                <p class="mt-2 text-sm text-gray-500">
                    {{ __('messages.shopify.plans.shop') }}: <strong>{{ $shop->name }}</strong> ({{ $shop->shopify_domain }})
                </p>
            @endif
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg">
                {{ session('warning') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
                {{ session('info') }}
            </div>
        @endif

        {{-- Current Subscription Status --}}
        @if($currentSubscription && $currentSubscription->isActive())
            <div class="mb-8 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border-2 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.shopify.plans.current_plan') }}: {{ $currentSubscription->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('messages.shopify.plans.status') }}: <span class="text-green-600 font-medium">{{ __('messages.shopify.plans.active') }}</span>
                            @if($currentSubscription->isOnTrial())
                                <span class="ml-2 text-yellow-600">({{ __('messages.shopify.plans.trial_ends') }} {{ $currentSubscription->trial_ends_on->format('M d, Y') }})</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.shopify.plans.price') }}: ${{ number_format($currentSubscription->price, 2) }}{{ __('messages.shopify.plans.per_month') }}
                        </p>
                        @if($currentSubscription->billing_on)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('messages.shopify.plans.next_billing') }}: {{ $currentSubscription->billing_on }}
                            </p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <form action="{{ route('shopify.billing.sync') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                                {{ __('messages.shopify.plans.sync_status') }}
                            </button>
                        </form>
                        <form action="{{ route('shopify.billing.cancel') }}" method="POST" onsubmit="return confirm('{{ __('messages.shopify.plans.confirm_cancel') }}')">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-lg transition">
                                {{ __('messages.shopify.plans.cancel_subscription') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Pricing Cards --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            @forelse($products as $product)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300
                    {{ $currentSubscription && $currentSubscription->isActive() && $product->name === $currentSubscription->name ? 'ring-2 ring-green-500' : '' }}">

                    {{-- Card Header --}}
                    <div class="p-6 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                        <h3 class="text-2xl font-bold">{{ $product->name }}</h3>
                        <div class="mt-4">
                            <span class="text-4xl font-extrabold">${{ number_format($product->price, 2) }}</span>
                            <span class="text-lg opacity-80">/month</span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6">
                        @if($product->description)
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                {{ $product->description }}
                            </p>
                        @endif

                        {{-- Features --}}
                        @if($product->features)
                            <ul class="space-y-3 mb-6">
                                @foreach($product->features as $feature)
                                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Action Button --}}
                        @if($currentSubscription && $currentSubscription->isActive() && $product->name === $currentSubscription->name)
                            <button disabled class="w-full py-3 px-6 bg-green-100 text-green-700 font-semibold rounded-lg cursor-not-allowed">
                                {{ __('messages.shopify.plans.current_plan') }}
                            </button>
                        @elseif($currentSubscription && $currentSubscription->isActive())
                            <button disabled class="w-full py-3 px-6 bg-gray-100 text-gray-500 font-semibold rounded-lg cursor-not-allowed">
                                {{ __('messages.shopify.plans.cancel_current_first') }}
                            </button>
                        @elseif(!auth()->check())
                            {{-- User not logged in - redirect to Shopify registration --}}
                            <div class="space-y-2">
                                <a href="{{ route('shopify.register', ['product' => $product->id]) }}"
                                   class="block w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors duration-300 text-center">
                                    {{ __('messages.shopify.plans.get_started') }}
                                </a>
                                <p class="text-center text-sm text-gray-500">
                                    {{ __('messages.shopify.plans.already_account') }}
                                    <a href="{{ url('/admin-shop/login') }}" class="text-indigo-600 hover:underline">{{ __('messages.shopify.plans.login_here') }}</a>
                                </p>
                            </div>
                        @elseif(!$shop)
                            {{-- User logged in but no shop connected --}}
                            <div class="text-center">
                                <p class="text-sm text-yellow-600 mb-2">{{ __('messages.shopify.plans.connect_store_first') }}</p>
                                <a href="{{ route('filament.admin-shop.pages.dashboard') }}"
                                   class="block w-full py-3 px-6 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors duration-300">
                                    {{ __('messages.shopify.plans.go_to_dashboard') }}
                                </a>
                            </div>
                        @else
                            <form action="{{ route('shopify.billing.subscribe', $product) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors duration-300">
                                    {{ __('messages.shopify.plans.subscribe_now') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('messages.shopify.plans.no_plans_available') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Alternative Payment Option --}}
        <div class="mt-12 text-center">
            <p class="text-gray-500 dark:text-gray-400 mb-4">
                {{ __('messages.shopify.plans.prefer_stripe') }}
            </p>
            <a href="{{ route('subscription.create', ['plan' => $products->first()?->id ?? 1]) }}"
               class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                {{ __('messages.shopify.plans.pay_with_stripe') }}
            </a>
        </div>

        {{-- Back to Dashboard / Home --}}
        <div class="mt-8 text-center">
            @auth
                <a href="{{ route('filament.admin-shop.pages.dashboard') }}"
                   class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    ← {{ __('messages.shopify.plans.back_to_dashboard') }}
                </a>
            @else
                <a href="{{ route('home') }}"
                   class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    ← {{ __('messages.shopify.plans.back_to_home') }}
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
