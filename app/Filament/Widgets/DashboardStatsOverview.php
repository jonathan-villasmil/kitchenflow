<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $todayRevenue = \App\Models\Order::whereDate('created_at', today())->where('status', 'paid')->sum('total');
        $activeOrders = \App\Models\Order::whereNotIn('status', ['paid', 'cancelled'])->count();
        $totalCustomers = \App\Models\Customer::count();

        return [
            Stat::make('Ingresos (Hoy)', '€ ' . number_format($todayRevenue, 2))
                ->description('Beneficio bruto diario')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Pedidos en Curso', $activeOrders)
                ->description('Tickets activos en cocina/sala')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Total Clientes', $totalCustomers)
                ->description('Registrados en el restaurante')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
