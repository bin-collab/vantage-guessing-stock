<?php

namespace App\Filament\Resources\Guesses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GuessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('opt_in_user_id')
                    ->relationship('user', 'email')
                    ->required(),
                Select::make('stock_id')
                    ->relationship('stock', 'symbol')
                    ->required(),
                DatePicker::make('guess_date')
                    ->required(),
                TextInput::make('guessed_price')
                    ->numeric()
                    ->required(),
                Toggle::make('is_correct')
                    ->disabled(),
            ]);
    }
}
