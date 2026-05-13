<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Desde')
                            ->default(today()->startOfDay())
                            ->maxDate(today()),
                        DatePicker::make('endDate')
                            ->label('Hasta')
                            ->default(today()->endOfDay())
                            ->maxDate(today()),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preset_today')
                ->label('Hoy')
                ->color('gray')
                ->size('sm')
                ->action(function () {
                    $today = today()->toDateString();
                    $this->filters = ['startDate' => $today, 'endDate' => $today];
                }),

            Action::make('preset_week')
                ->label('Esta semana')
                ->color('gray')
                ->size('sm')
                ->action(function () {
                    $this->filters = [
                        'startDate' => now()->startOfWeek()->toDateString(),
                        'endDate'   => now()->endOfWeek()->toDateString(),
                    ];
                }),

            Action::make('preset_month')
                ->label('Este mes')
                ->color('gray')
                ->size('sm')
                ->action(function () {
                    $this->filters = [
                        'startDate' => now()->startOfMonth()->toDateString(),
                        'endDate'   => now()->endOfMonth()->toDateString(),
                    ];
                }),

            Action::make('preset_year')
                ->label('Este año')
                ->color('gray')
                ->size('sm')
                ->action(function () {
                    $this->filters = [
                        'startDate' => now()->startOfYear()->toDateString(),
                        'endDate'   => now()->endOfYear()->toDateString(),
                    ];
                }),
        ];
    }
}
