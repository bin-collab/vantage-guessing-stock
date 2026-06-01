<?php

namespace App\Filament\Resources\Guesses\Schemas;

use Filament\Schemas\Schema;

class GuessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('opt_in_user_id')
                    ->relationship('user', 'email')
                    ->required(),
                \Filament\Forms\Components\Select::make('stock_id')
                    ->relationship('stock', 'symbol')
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('guess_date')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('guessed_price')
                    ->numeric()
                    ->required(),
                \Filament\Forms\Components\Toggle::make('is_correct')
                    ->disabled(),
            ]);
    }
}
