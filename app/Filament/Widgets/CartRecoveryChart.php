<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CartRecoveryChart extends ChartWidget
{
    protected ?string $heading = 'Cart Recovery Chart';

    protected function getData(): array
    {
        $user = Auth::user();
        $shopIds = $user->shops->pluck('id');
        $months = collect(range(0, 11))->map(fn ($i) => Carbon::now()->subMonths($i)->format('Y-m'))->reverse();

        $emailData = [];
        $whatsappData = [];

        foreach ($months as $month) {
            $emailData[] = Cart::whereIn('shop_id', $shopIds)
                ->where('status', 'complete')
                ->where('recovered_via', 'email')
                ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = ?", [$month])
                ->count();

            $whatsappData[] = Cart::whereIn('shop_id', $shopIds)
                ->where('status', 'complete')
                ->where('recovered_via', 'whatsapp')
                ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = ?", [$month])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Email',
                    'data' => $emailData,
                    'backgroundColor' => 'rgba(59,130,246,0.7)',
                ],
                [
                    'label' => 'WhatsApp',
                    'data' => $whatsappData,
                    'backgroundColor' => 'rgba(34,197,94,0.7)',
                ],
            ],
            'labels' => $months->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
