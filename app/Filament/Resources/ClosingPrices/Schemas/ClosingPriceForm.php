<?php

namespace App\Filament\Resources\ClosingPrices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClosingPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('stock_id')
                    ->relationship('stock', 'symbol')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->required(),
            ]);
    }
}
