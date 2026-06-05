<?php

namespace App\Filament\Resources\ClosingPrices;

use App\Filament\Resources\ClosingPrices\Pages\CreateClosingPrice;
use App\Filament\Resources\ClosingPrices\Pages\EditClosingPrice;
use App\Filament\Resources\ClosingPrices\Pages\ListClosingPrices;
use App\Filament\Resources\ClosingPrices\Schemas\ClosingPriceForm;
use App\Filament\Resources\ClosingPrices\Tables\ClosingPricesTable;
use App\Models\ClosingPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClosingPriceResource extends Resource
{
    protected static ?string $model = ClosingPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '录入收盘价';

    public static function form(Schema $schema): Schema
    {
        return ClosingPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClosingPricesTable::configure($table);
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
            'index' => ListClosingPrices::route('/'),
            'create' => CreateClosingPrice::route('/create'),
            'edit' => EditClosingPrice::route('/{record}/edit'),
        ];
    }
}
