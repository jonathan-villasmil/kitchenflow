<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class WaitTimesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Tiempo Medio en Cocina (Minutos)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? today()->startOfDay();
        $endDate = $this->filters['endDate'] ?? today()->endOfDay();

        // Get average wait time per hour for the selected period
        // Ignore anomalies > 120 minutes
        $waitTimes = OrderItem::select(
                DB::raw('HOUR(sent_at) as hour'),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, sent_at, ready_at)) as avg_wait')
            )
            ->whereBetween('sent_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->whereNotNull('ready_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, sent_at, ready_at) <= 120')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $data = [];
        
        // Fill from 12:00 to 23:00
        for ($i = 12; $i <= 23; $i++) {
            $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $hourData = $waitTimes->firstWhere('hour', $i);
            $data[] = $hourData ? round($hourData->avg_wait, 1) : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tiempo Medio (min)',
                    'data' => $data,
                    'borderColor' => '#ef4444', // red-500
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
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
