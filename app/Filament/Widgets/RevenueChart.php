<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Ingresos (Últimos 7 Días)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = collect();
        $labels = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $total = \App\Models\Order::whereDate('created_at', $date)
                ->where('status', 'paid')
                ->sum('total');

            $data->push($total);
            $labels->push($date->format('d M'));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos Diarios',
                    'data' => $data->toArray(),
                    'fill' => 'start',
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
