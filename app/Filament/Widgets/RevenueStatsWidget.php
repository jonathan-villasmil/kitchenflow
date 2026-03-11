<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class RevenueStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0; // Show first

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? today()->startOfDay();
        $endDate = $this->filters['endDate'] ?? today()->endOfDay();

        $revenue = Order::whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $estimatedCost = $revenue * 0.30;
        $grossProfit = $revenue - $estimatedCost;

        return [
            Stat::make('Ingresos Brutos', '€' . number_format($revenue, 2))
                ->description('Ventas totales del periodo')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Costo Base Estimado (30%)', '€' . number_format($estimatedCost, 2))
                ->description('Costo medio de materia prima')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Beneficio Bruto', '€' . number_format($grossProfit, 2))
                ->description('Ingresos menos costo estimado')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
