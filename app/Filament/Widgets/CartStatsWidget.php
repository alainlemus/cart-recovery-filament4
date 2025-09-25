<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CartStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $shopIds = $user->shops->pluck('id');

        return [
            Stat::make('Total Carts', Cart::whereIn('shop_id', $shopIds)->count()),
            Stat::make('Abandoned Carts', Cart::whereIn('shop_id', $shopIds)->where('status', 'abandoned')->count()),
            Stat::make('Recovered via Email', Cart::whereIn('shop_id', $shopIds)->where('status', 'complete')->where('recovered_via', 'email')->count()),
            Stat::make('Recovered via WhatsApp', Cart::whereIn('shop_id', $shopIds)->where('status', 'complete')->where('recovered_via', 'whatsapp')->count()),
        ];
    }
}
