<?php

namespace App\Filament\Resources\Guesses\Pages;

use App\Filament\Resources\Guesses\GuessResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGuess extends EditRecord
{
    protected static string $resource = GuessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
