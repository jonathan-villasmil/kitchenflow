<?php

namespace App\Filament\Widgets;

use App\Support\AdminRestaurantContext;
use Carbon\Carbon;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class TodayOrdersList extends TableWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Pedidos del periodo';

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

    public function table(Table $table): Table
    {
        $start = Carbon::parse($this->startDate ?: today())->startOfDay();
        $end   = Carbon::parse($this->endDate   ?: today())->endOfDay();

        return $table
            ->query(fn (): Builder => AdminRestaurantContext::scope(\App\Models\Order::query())
                ->whereBetween('created_at', [$start, $end])
                ->latest()
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('Nº Pedido')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 5, '0', STR_PAD_LEFT))
                    ->weight('bold'),

                \Filament\Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->default('—'),

                \Filament\Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'dine_in'  => 'Mesa',
                        'takeaway' => 'Llevar',
                        'delivery' => 'Delivery',
                        default    => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'dine_in'  => 'info',
                        'takeaway' => 'warning',
                        'delivery' => 'success',
                        default    => 'gray',
                    }),

                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'gray',
                        'confirmed' => 'info',
                        'ready'     => 'warning',
                        'paid'      => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),

                \Filament\Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Hora')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
