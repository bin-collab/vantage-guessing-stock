<?php

namespace App\Filament\Resources\ClosingPrices\Schemas;

use Filament\Schemas\Schema;

class ClosingPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('stock_id')
                    ->relationship('stock', 'symbol')
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('date')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required(),
            ]);
    }
}
