<?php

namespace App\Filament\Resources\HappyHours;

use App\Filament\Resources\HappyHours\Pages\CreateHappyHour;
use App\Filament\Resources\HappyHours\Pages\EditHappyHour;
use App\Filament\Resources\HappyHours\Pages\ListHappyHours;
use App\Filament\Resources\HappyHours\Schemas\HappyHourForm;
use App\Filament\Resources\HappyHours\Tables\HappyHoursTable;
use App\Models\HappyHour;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HappyHourResource extends Resource
{
    protected static ?string $model = HappyHour::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return HappyHourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HappyHoursTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHappyHours::route('/'),
            'create' => CreateHappyHour::route('/create'),
            'edit' => EditHappyHour::route('/{record}/edit'),
        ];
    }
}
