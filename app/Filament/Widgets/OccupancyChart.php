<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OccupancyChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Curva de Ocupación (Pedidos x Hora)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? today()->startOfDay();
        $endDate = $this->filters['endDate'] ?? today()->endOfDay();

        $orders = Order::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('status', '!=', 'cancelled')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $data = [];
        
        // Fill from 12:00 to 23:00
        for ($i = 12; $i <= 23; $i++) {
            $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $hourData = $orders->firstWhere('hour', $i);
            $data[] = $hourData ? $hourData->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pedidos',
                    'data' => $data,
                    'borderColor' => '#3b82f6', // blue-500
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => 'start',
                    'tension' => 0.4, // smooth curve
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
