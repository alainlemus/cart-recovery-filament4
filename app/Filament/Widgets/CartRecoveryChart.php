<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CartRecoveryChart extends ChartWidget
{
    protected ?string $heading = 'Cart Recovery Chart';

    protected ?string $pollingInterval = '10s';

    protected ?string $maxHeight = '400px';

    protected ?string $maxWidth = null; // Puedes agregar esta propiedad si tu ChartWidget la soporta

    public function getDescription(): ?string
    {
        return 'The carts recovered via Email and WhatsApp over the last 12 months.';
    }

    public function getFilters(): array
    {
        $user = Auth::user();
        $shops = [];
        foreach ($user->shops as $shop) {
            $shops[(string) $shop->id] = (string) $shop->name;
        }
        $options = array_merge([0 => 'Todas las tiendas'], $shops);

        return $options;
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $shopIds = $user->shops->pluck('id');
        $selectedShop = $this->filters['shop_id'] ?? 'all';
        if (is_array($selectedShop)) {
            $selectedShop = $selectedShop[0] ?? 'all';
        }
        $selectedShop = (string) $selectedShop;
        if ($selectedShop !== 'all') {
            $shopIds = collect([$selectedShop]);
        }
        $months = collect(range(0, 11))->map(fn ($i) => Carbon::now()->subMonths($i)->format('Y-m'))->reverse();

        logger()->info('Shop IDs:', $shopIds->toArray());

        $emailData = [];
        $whatsappData = [];

        foreach ($months as $month) {
            $emailCount = Cart::whereIn('shop_id', $shopIds)
                ->where('status', 'complete')
                ->where('recovered_via', 'email')
                ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = ?", [$month])
                ->count();
            $emailData[] = $emailCount;
            logger()->info("Email count for $month: $emailCount");

            $whatsappCount = Cart::whereIn('shop_id', $shopIds)
                ->where('status', 'complete')
                ->where('recovered_via', 'whatsapp')
                ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = ?", [$month])
                ->count();
            $whatsappData[] = $whatsappCount;
            logger()->info("WhatsApp count for $month: $whatsappCount");
        }

        logger()->info('Valor de selectedShop', ['selectedShop' => $selectedShop]);
        $labels = $months->map(fn ($m) => (string) Carbon::createFromFormat('Y-m', $m)->format('M Y'))->values()->all();
        $dataReturn = [
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
            'labels' => $labels,
        ];
        logger()->info('getData return', ['data' => $dataReturn]);

        return $dataReturn;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getMaxWidth(): ?string
    {
        return null; // null para ocupar el 100% del ancho disponible
    }
}
