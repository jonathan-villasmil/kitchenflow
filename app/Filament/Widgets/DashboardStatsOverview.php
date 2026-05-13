<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DashboardStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $start = Carbon::parse($this->filters['startDate'] ?? today())->startOfDay();
        $end   = Carbon::parse($this->filters['endDate']   ?? today())->endOfDay();

        $revenue = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('total');

        $ordersCount = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->count();

        $activeOrders = Order::whereNotIn('status', ['paid', 'cancelled'])->count();

        $avgTicket = $ordersCount > 0 ? $revenue / $ordersCount : 0;

        $isSingleDay = $start->isSameDay($end);
        $periodLabel = $isSingleDay
            ? $start->format('d/m/Y')
            : $start->format('d/m') . ' – ' . $end->format('d/m/Y');

        return [
            Stat::make('Ingresos del periodo', '€ ' . number_format($revenue, 2))
                ->description($periodLabel . ' · ' . $ordersCount . ' pedidos cobrados')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Ticket Medio', '€ ' . number_format($avgTicket, 2))
                ->description('Promedio por pedido cobrado')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('primary'),

            Stat::make('Pedidos Activos', $activeOrders)
                ->description('En cocina o sala ahora mismo')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
