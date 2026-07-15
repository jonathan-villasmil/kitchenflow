<?php

namespace App\Filament\Pages;

use App\Support\AdminRestaurantContext;
use Carbon\Carbon;
use Filament\Pages\Page;

class AnalyticsReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Informes de Ventas';
    protected static ?string $title = 'Informes Corporativos & Analítica';
    protected static string|\UnitEnum|null $navigationGroup = 'Informes';

    protected string $view = 'filament.pages.analytics-report';

    // ── Filtros de fecha ──────────────────────────────────────────────
    public string $dateRange = 'today';      // today | week | month | year | custom
    public string $startDate = '';
    public string $endDate   = '';

    public function mount(): void
    {
        $this->startDate = today()->toDateString();
        $this->endDate   = today()->toDateString();
    }

    public function updatedDateRange(string $value): void
    {
        match ($value) {
            'today'  => [$this->startDate, $this->endDate] = [today()->toDateString(), today()->toDateString()],
            'week'   => [$this->startDate, $this->endDate] = [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month'  => [$this->startDate, $this->endDate] = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'year'   => [$this->startDate, $this->endDate] = [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            default  => null,
        };

        $this->notifyWidgets();
    }

    public function updatedStartDate(): void
    {
        $this->dateRange = 'custom';
        $this->notifyWidgets();
    }

    public function updatedEndDate(): void
    {
        $this->dateRange = 'custom';
        $this->notifyWidgets();
    }

    protected function notifyWidgets(): void
    {
        $this->dispatch(
            'analytics-filters-updated',
            startDate: $this->startDate ?: today()->toDateString(),
            endDate:   $this->endDate   ?: today()->toDateString(),
        );
    }

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
                    $start = Carbon::parse($this->startDate)->startOfDay();
                    $end   = Carbon::parse($this->endDate)->endOfDay();

                    $revenue = AdminRestaurantContext::scope(\App\Models\Order::query())
                        ->whereBetween('closed_at', [$start, $end])
                        ->where('status', 'paid')->sum('total');

                    $activeOrders = AdminRestaurantContext::scope(\App\Models\Order::query())
                        ->whereNotIn('status', ['paid', 'cancelled'])
                        ->count();
                    $totalCustomers = AdminRestaurantContext::scope(\App\Models\Customer::query())->count();

                    $topDishes = AdminRestaurantContext::scopeThroughOrder(\App\Models\OrderItem::query())
                        ->select('dish_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
                        ->whereBetween('created_at', [$start, $end])
                        ->groupBy('dish_id')->orderByDesc('total_sold')->take(5)->with('dish')->get();

                    $orders = AdminRestaurantContext::scope(\App\Models\Order::with('customer'))
                        ->whereBetween('created_at', [$start, $end])
                        ->orderBy('created_at', 'desc')->get();

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.analytics', [
                        'todayRevenue'   => $revenue,
                        'activeOrders'   => $activeOrders,
                        'totalCustomers' => $totalCustomers,
                        'topDishes'      => $topDishes,
                        'todayOrders'    => $orders,
                    ]);

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'informe-ventas-' . $this->startDate . '-al-' . $this->endDate . '.pdf'
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
