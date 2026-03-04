<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AnalyticsReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Informes de Ventas';
    protected static ?string $title = 'Informes Corporativos & Analítica';
    protected static string|\UnitEnum|null $navigationGroup = 'Informes';

    protected string $view = 'filament.pages.analytics-report';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStatsOverview::class,
            \App\Filament\Widgets\RevenueChart::class,
            \App\Filament\Widgets\TopDishesChart::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_pdf')
                ->label('Descargar Informe (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $todayRevenue = \App\Models\Order::whereDate('created_at', today())->where('status', 'paid')->sum('total');
                    $activeOrders = \App\Models\Order::whereNotIn('status', ['paid', 'cancelled'])->count();
                    $totalCustomers = \App\Models\Customer::count();

                    $topDishes = \App\Models\OrderItem::select('dish_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
                        ->groupBy('dish_id')
                        ->orderByDesc('total_sold')
                        ->take(5)
                        ->with('dish')
                        ->get();

                    $todayOrders = \App\Models\Order::with('customer')
                        ->whereDate('created_at', today())
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.analytics', [
                        'todayRevenue' => $todayRevenue,
                        'activeOrders' => $activeOrders,
                        'totalCustomers' => $totalCustomers,
                        'topDishes' => $topDishes,
                        'todayOrders' => $todayOrders,
                    ]);

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'informe-ventas-' . now()->format('Y-m-d') . '.pdf'
                    );
                }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\TodayOrdersList::class,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'manager']);
    }
}
