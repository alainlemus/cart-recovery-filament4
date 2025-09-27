<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CartStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Analytics';

    protected ?string $description = 'An overview general data.';

    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $user = Auth::user();
        $shopIds = $user->shops->pluck('id');
        // Escucha el filtro global de la página (dashboard)
        $selectedShop = $this->filters['shop_id'] ?? null;

        if ($selectedShop && $selectedShop !== 'all') {
            $shopIds = collect([$selectedShop]);
        }

        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $baseQuery = Cart::whereIn('shop_id', $shopIds);
        /*if ($startDate) {
            $baseQuery = $baseQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $baseQuery = $baseQuery->whereDate('created_at', '<=', $endDate);
        }*/

        return [
            Stat::make('Total Carts', (clone $baseQuery)->count()),
            Stat::make('Abandoned Carts', (clone $baseQuery)->where('status', 'abandoned')->count()),
            Stat::make('Recovered via Email', (clone $baseQuery)->where('status', 'complete')->where('recovered_via', 'email')->count()),
            Stat::make('Recovered via WhatsApp', (clone $baseQuery)->where('status', 'complete')->where('recovered_via', 'whatsapp')->count()),
        ];
    }
}
