<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CartStatsWidgetMoney extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $shopIds = $user->shops->pluck('id');

        $recovered = Cart::whereIn('shop_id', $shopIds)
            ->where('status', 'complete')
            ->sum('total_price');

        $toRecover = Cart::whereIn('shop_id', $shopIds)
            ->where('status', 'abandoned')
            ->sum('total_price');

        return [
            Stat::make('Recovered Amount', '$' . number_format($recovered, 2)),
            Stat::make('Amount to Recover', '$' . number_format($toRecover, 2)),
        ];
    }
}
