<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TopDishesChart extends ChartWidget
{
    protected ?string $heading = 'Platos Más Vendidos (Top 5)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $topDishes = \App\Models\OrderItem::select('dish_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('dish_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->with('dish')
            ->get();

        $labels = $topDishes->map(fn ($item) => $item->dish?->name ?? 'Desconocido')->toArray();
        $data = $topDishes->map(fn ($item) => $item->total_sold)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Unidades Vendidas',
                    'data' => $data,
                    'backgroundColor' => [
                        '#f59e0b', // Amber
                        '#10b981', // Emerald
                        '#3b82f6', // Blue
                        '#ef4444', // Red
                        '#8b5cf6', // Purple
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
