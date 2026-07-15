<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Support\AdminRestaurantContext;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

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

    public function getHeading(): ?string
    {
        $start = $this->startDate ?: today()->toDateString();
        $end   = $this->endDate   ?: today()->toDateString();
        return $start === $end
            ? 'Ingresos del ' . Carbon::parse($start)->format('d/m/Y')
            : 'Ingresos: ' . Carbon::parse($start)->format('d/m') . ' — ' . Carbon::parse($end)->format('d/m/Y');
    }

    protected function getData(): array
    {
        $start = Carbon::parse($this->startDate ?: today())->startOfDay();
        $end   = Carbon::parse($this->endDate   ?: today())->endOfDay();

        // Generar todos los días del rango
        $days   = collect();
        $labels = collect();
        $data   = collect();

        $diff = (int) $start->diffInDays($end);
        // Si el rango es muy grande, agrupar por semana o mes
        $groupBy = $diff > 60 ? 'month' : ($diff > 14 ? 'week' : 'day');

        if ($groupBy === 'day') {
            for ($i = 0; $i <= $diff; $i++) {
                $date  = $start->copy()->addDays($i);
                $total = AdminRestaurantContext::scope(Order::query())
                    ->whereDate('closed_at', $date)
                    ->where('status', 'paid')
                    ->sum('total');
                $data->push(round($total, 2));
                $labels->push($date->format('d M'));
            }
        } elseif ($groupBy === 'week') {
            $cursor = $start->copy()->startOfWeek();
            while ($cursor->lte($end)) {
                $weekEnd = $cursor->copy()->endOfWeek();
                $total = AdminRestaurantContext::scope(Order::query())
                    ->whereBetween('closed_at', [$cursor, $weekEnd->min($end)])
                    ->where('status', 'paid')->sum('total');
                $data->push(round($total, 2));
                $labels->push('Sem ' . $cursor->format('d/m'));
                $cursor->addWeek();
            }
        } else {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $monthEnd = $cursor->copy()->endOfMonth();
                $total = AdminRestaurantContext::scope(Order::query())
                    ->whereBetween('closed_at', [$cursor, $monthEnd->min($end)])
                    ->where('status', 'paid')->sum('total');
                $data->push(round($total, 2));
                $labels->push($cursor->format('M Y'));
                $cursor->addMonth();
            }
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Ingresos (€)',
                    'data'            => $data->toArray(),
                    'fill'            => 'start',
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'tension'         => 0.3,
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
