<?php

namespace App\Filament\Resources\OptInUsers\Schemas;

use Filament\Schemas\Schema;

class OptInUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                \Filament\Forms\Components\TextInput::make('uid')
                    ->label('UID')
                    ->required()
                    ->unique(ignoreRecord: true),
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('用户名'),
            ]);
    }
}
