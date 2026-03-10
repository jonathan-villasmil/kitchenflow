<?php

namespace App\Filament\Resources\CashRegisters;

use App\Filament\Resources\CashRegisters\Pages\ManageCashRegisters;
use App\Models\CashRegister;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('restaurant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('opened_by')
                    ->required()
                    ->numeric(),
                TextInput::make('closed_by')
                    ->numeric(),
                TextInput::make('opening_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('closing_amount')
                    ->numeric(),
                TextInput::make('expected_amount')
                    ->numeric(),
                DateTimePicker::make('opened_at')
                    ->required(),
                DateTimePicker::make('closed_at'),
                Select::make('status')
                    ->options(['open' => 'Open', 'closed' => 'Closed'])
                    ->default('open')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('restaurant_id')
                    ->numeric(),
                TextEntry::make('opened_by')
                    ->numeric(),
                TextEntry::make('closed_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('opening_amount')
                    ->numeric(),
                TextEntry::make('closing_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('expected_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('opened_at')
                    ->dateTime(),
                TextEntry::make('closed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')->label('Turno #')->sortable(),
                TextColumn::make('openedBy.name')
                    ->label('Abierto por')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('closedBy.name')
                    ->label('Cerrado por')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('opening_amount')
                    ->label('Fondo Inicial')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('expected_amount')
                    ->label('Esp.')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('closing_amount')
                    ->label('Contado')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('Reapertura')
                    ->dateTime('d/m y H:i')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Cierre')
                    ->dateTime('d/m y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'open',
                        'gray' => 'closed',
                    ]),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                \Filament\Actions\Action::make('downloadZReport')
                    ->label('Reporte Z')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn (CashRegister $record): string => route('pos.z-report', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (CashRegister $record): bool => $record->status === 'closed'),
            ])
            ->toolbarActions([
                // No delete bulk
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCashRegisters::route('/'),
        ];
    }
}
