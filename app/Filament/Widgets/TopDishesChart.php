<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Support\AdminRestaurantContext;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class TopDishesChart extends ChartWidget
{
    protected ?string $heading = 'Platos Más Vendidos';
    protected static ?int $sort = 1;

    public string $startDate = '';
    public string $endDate   = '';

    public function mount(): void
    {
        $this->startDate = today()->toDateString();
        $this->endDate   = today()->toDateString();
    }

    #[On('analytics-filters-updated')]
    public function applyFilters(string $startDate, string $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    protected function getData(): array
    {
        $start = Carbon::parse($this->startDate ?: today())->startOfDay();
        $end   = Carbon::parse($this->endDate   ?: today())->endOfDay();

        $topDishes = AdminRestaurantContext::scopeThroughOrder(OrderItem::query())
            ->select('name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total) as total_revenue'))
            ->whereBetween('created_at', [$start, $end])
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
