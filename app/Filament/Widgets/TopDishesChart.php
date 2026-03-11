<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TopDishesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Platos Más Vendidos';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? today()->startOfDay();
        $endDate = $this->filters['endDate'] ?? today()->endOfDay();

        $topDishes = OrderItem::select('name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total) as total_revenue'))
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('status', '!=', 'cancelled')
            ->groupBy('name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Unidades Vendidas',
                    'data' => $topDishes->pluck('total_sold')->toArray(),
                    'backgroundColor' => [
                        '#f97316', '#3b82f6', '#ef4444', '#10b981', '#8b5cf6'
                    ],
                ],
            ],
            'labels' => $topDishes->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
