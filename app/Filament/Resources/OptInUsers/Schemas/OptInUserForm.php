<?php

namespace App\Filament\Resources\OptInUsers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OptInUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('uid')
                    ->label('UID')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('用户名'),
            ]);
    }
}
